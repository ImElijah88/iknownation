from flask import Flask, request, jsonify
from openai import OpenAI
import os
import json

app = Flask(__name__)

client = OpenAI(
    base_url="https://openrouter.ai/api/v1",
    api_key=os.getenv("OPENROUTER_API_KEY"),
)

@app.route('/')
def hello_world():
    return 'Hello from the backend!'

@app.route('/generate_presentation', methods=['POST'])
def generate_presentation():
    data = request.get_json()
    text = data.get('text')

    if not text:
        return jsonify({'error': 'No text provided'}), 400

    try:
        prompt = f"""
        You are an expert presentation designer. Generate a comprehensive and engaging presentation based on the provided TEXT.
        Your presentation should be detailed, insightful, and suitable for a professional audience.
        Extract key points from the TEXT to form the core content of each slide.
        The speech should elaborate on each slide's content, providing additional context and examples.

        TEXT: {text}

        Your output MUST be a single JSON object with the following structure. DO NOT include any other text, markdown, or conversational filler outside this JSON object. ONLY return the JSON object.
        {{
          "title": "[Concise and engaging Presentation Title]",
          "slides": [
            {{
              "title": "[Slide 1 Title]",
              "content": "[Slide 1 Content, summarizing the presentation's core]"
            }},
            {{
              "title": "[Slide 2 Title]",
              "content": "[Slide 2 Content, detailed explanation of a key point]"
            }},
            // Generate at least 5-7 more slides, each with a clear title and content.
            // Ensure the final slide is a conclusion or summary.
          ]
        }}
        Ensure all HTML is properly escaped for JSON. Do not include any other text or markdown outside the JSON object.
        """

        completion = client.chat.completions.create(
            model="mistralai/mixtral-8x7b-instruct",
            messages=[
                {
                    "role": "user",
                    "content": prompt,
                },
            ],
        )

        generated_content = completion.choices[0].message.content
        presentation_data = json.loads(generated_content)
        return jsonify(presentation_data)

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
