<?php
// Use Guzzle HTTP client for making requests
use GuzzleHttp\Client;

class McpBridgeService
{
    private Client $client;
    private string $bridgeUrl = 'http://127.0.0.1:5001';

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30.0,
        ]);
    }

    public function connect(array $serverConfig): array
    {
        // This is the correct logic. It sends the server configuration as a JSON object.
        return $this->sendRequest('/mcp/connect', [
            'server_config' => $serverConfig
        ]);
    }

    public function callTool(string $toolName, array $toolArgs): array
    {
        return $this->sendRequest('/mcp/call_tool', [
            'tool_name' => $toolName,
            'tool_args' => $toolArgs,
        ]);
    }

    private function sendRequest(string $endpoint, array $payload): array
    {
        $response = $this->client->post($this->bridgeUrl . $endpoint, [
            'json' => $payload
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
}