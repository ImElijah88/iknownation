<?php
// classes/PresentationAgent.php

require_once __DIR__ . '/../config/db_config.php';

class Neo
{
    private $conn;
    private $llmApiKey;
    private $llmEndpoint;
    private $llmModel;
    private $mpvServerUrl; // Placeholder for MPV server URL

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->mpvServerUrl = 'http://localhost:5001'; // mcp_bridge server URL
    }

    /**
     * Generates presentation content using an LLM.
     *
     * @param string $fileContent The content extracted from the user's uploaded file.
     * @param array $templateFeatures The features of the selected template.
     * @param array $dynamicInputs Dynamic inputs provided by the user.
     * @return array The generated presentation content (slides, quizzes, speech, colors, title).
     * @throws Exception If LLM interaction fails or returns invalid content.
     */
    public function generatePresentationContent(string $fileContent, array $templateFeatures, array $dynamicInputs): array
    {
        $llmPrompt = $this->buildLlmPrompt($fileContent, $templateFeatures, $dynamicInputs);
        $llmResponse = $this->callLlmApi($llmPrompt);
        $generatedContent = $this->extractAndDecodeJson($llmResponse);

        return $generatedContent;
    }

    /**
     * Builds the detailed prompt for the LLM.
     */
    private function buildLlmPrompt(string $fileContent, array $templateFeatures, array $dynamicInputs): string
    {
        $llmPrompt = "You are an expert presentation designer. Generate a comprehensive and engaging presentation based on the provided TEXT and parameters.\n";
        $llmPrompt .= "Your presentation should be detailed, insightful, and suitable for a professional audience.\n";
        $llmPrompt .= "Extract key points from the TEXT to form the core content of each slide.\n";
        $llmPrompt .= "The speech should elaborate on each slide's content, providing additional context and examples.\n\n";
        $llmPrompt .= "TEXT: " . $fileContent . "\n\n";
        $llmPrompt .= "TEMPLATE FEATURES: " . json_encode($templateFeatures) . "\n\n";
        $llmPrompt .= "USER INPUTS: " . json_encode($dynamicInputs) . "\n\n";
        $llmPrompt .= "Your output MUST be a single JSON object with the following structure. DO NOT include any other text, markdown, or conversational filler outside this JSON object. ONLY return the JSON object.\n";
        $llmPrompt .= "{\n";
        $llmPrompt .= "  \"title\": \"[Concise and engaging Presentation Title]\",\n";
        $llmPrompt .= "  \"slides\": [\n";
        $llmPrompt .= "    {\n";
        $llmPrompt .= "      \"html\": \"<div class=\\\"slide w-full text-center\\\"><h2 class=\\\"text-4xl font-bold mb-4\\\">[Slide 1 Title]</h2><p class=\\\"text-xl text-gray-600 dark:text-gray-300 mb-8\\\">[Slide 1 Content, summarizing the presentation's core]</p><button class=\\\"start-btn text-white font-bold py-3 px-6 rounded-lg text-xl transition-all transform hover:scale-105\\\" style=\\\"background-color: var(--primary-color);\\\">Start Presentation</button></div>\\\"\n";
        $llmPrompt .= "    },\n";
        $llmPrompt .= "    {\n";
        $llmPrompt .= "      \"html\": \"<div class=\\\"slide w-full\\\"><h2 class=\\\"text-3xl font-bold mb-4\\\" style=\\\"color: var(--primary-color-light);\\\">[Slide 2 Title]</h2><p class=\\\"text-lg mb-4\\\">[Slide 2 Content, detailed explanation of a key point]</p></div>\\\"\n";
        $llmPrompt .= "    },\n";
        $llmPrompt .= "    // Generate at least 5-7 more slides, each wrapped in <div class=\\\"slide w-full\\\">...</div>.\n";
        $llmPrompt .= "    // Each slide should have a clear title and content. Use Tailwind CSS classes for styling within the HTML.\n";
        $llmPrompt .= "    // Ensure the final slide is a conclusion or summary.\n";
        $llmPrompt .= "  ],\n";
        $llmPrompt .= "  \"quizzes\": [\n";
        $llmPrompt .= "    {\n";
        $llmPrompt .= "      \"slideAfter\": [slide index after which quiz appears],\n";
        $llmPrompt .= "      \"question\": \"[Quiz Question]\",\n";
        $llmPrompt .= "      \"options\": [\"[Option 1]\", \"[Option 2]\", \"[Option 3]\", \"[Option 4]\"],\n";
        $llmPrompt .= "      \"correctAnswer\": \"[Correct Option]\"\n";
        $llmPrompt .= "    },\n";
        $llmPrompt .= "    // ... more quizzes ...\n";
        $llmPrompt .= "  ],\n";
        $llmPrompt .= "  \"speech\": \"Slide 1: [Detailed speech for Slide 1]\nSlide 2: [Detailed speech for Slide 2]\n...[Full speech transcript for the entire presentation, clearly segmented by 'Slide X:' and providing comprehensive explanations for each slide's content.]\",\n";
        $llmPrompt .= "  \"colors\": {\"primary\": \"[#hex]\", \"light\": \"[#hex]\", \"hover\": \"[#hex]\"}\n";
        $llmPrompt .= "}\n";
        $llmPrompt .= "Ensure all HTML is properly escaped for JSON. Do not include any other text or markdown outside the JSON object. The first slide MUST contain the 'start-btn' class. All slides MUST have the 'slide' class. The 'title' field should be concise. The 'slides' array should contain multiple distinct slides. The 'speech' should be a continuous text, but clearly marked for each slide. The 'colors' should be a valid hex code for a primary, light, and hover color. ONLY return the JSON object.";

        return $llmPrompt;
    }

    /**
     * Makes the API call to the LLM provider.
     */
    private function callLlmApi(string $prompt): string
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($this->mpvServerUrl . '/llm/completion', [
            'json' => [
                'prompt' => $prompt
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('MCP Bridge API call failed: HTTP Error ' . $response->getStatusCode() . ': ' . $response->getBody());
        }

        return $response->getBody()->getContents();
    }

    /**
     * Extracts and decodes the JSON content from the LLM's raw response.
     */
    private function extractAndDecodeJson(string $llmResponse): array
    {
        $llmOutput = json_decode($llmResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to decode MCP Bridge response: ' . json_last_error_msg() . '. Response: ' . $llmResponse);
        }

        $generatedText = '';
        if (isset($llmOutput['result'])) {
            $generatedText = $llmOutput['result'];
        } else {
            throw new Exception('MCP Bridge response did not contain expected content (missing result). Response: ' . $llmResponse);
        }

        // Attempt to extract JSON from the LLM's response, in case it includes conversational filler
        $jsonStart = strpos($generatedText, '{');
        $jsonEnd = strrpos($generatedText, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            throw new Exception('Could not find a valid JSON object in LLM response. Response: ' . $generatedText);
        }

        $jsonString = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart + 1);
        $generatedContent = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to decode extracted JSON content: ' . json_last_error_msg() . '. Content: ' . $jsonString);
        }

        return $generatedContent;
    }

    /**
     * Placeholder for extracting content from various file types using MPV server.
     * This method would interact with your MPV server to get file content.
     * For now, it only handles .txt files directly.
     */
    public function extractFileContent(string $filePath, string $fileExtension): string
    {
        switch ($fileExtension) {
            case 'txt':
                return file_get_contents($filePath);
            case 'pdf':
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    return $pdf->getText();
                } catch (Exception $e) {
                    throw new Exception("Failed to extract PDF content: " . $e->getMessage());
                }
            case 'doc':
            case 'docx':
                try {
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
                    $text = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($element->getElements() as $textElement) {
                                    if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                        $text .= $textElement->getText() . ' ';
                                    }
                                }
                            }
                        }
                    }
                    return $text;
                } catch (Exception $e) {
                    throw new Exception("Failed to extract Word document content: " . $e->getMessage());
                }
            default:
                return "Unsupported file type: ." . $fileExtension;
        }
    }
}