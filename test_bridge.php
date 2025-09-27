<?php
// Enable error reporting to see any issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Require the necessary files
require 'vendor/autoload.php';
require 'McpBridgeService.php';

// This is the configuration for the MCP File System server.
// It tells the bridge how to start the file system tool.
// IMPORTANT: We've set the path to your project directory.
$serverConfig = [
    "command" => "npx",
    "args" => [
        "-y",
        "@modelcontextprotocol/server-filesystem",
        "C:/xampp/htdocs/videoideas/Nownationproject/" // Note the forward slashes
    ],
    "env" => null
];

echo "<h1>Updating Neo: Testing the McpBridgeService</h1>";

try {
    // 1. Create an instance of our new service
    $bridge = new McpBridgeService();

    // 2. Connect to the MCP File System server via the bridge
    echo "<h3>Step 1: Connecting to MCP File System Server...</h3>";
    $connectResult = $bridge->connect($serverConfig);
    echo "<pre>" . json_encode($connectResult, JSON_PRETTY_PRINT) . "</pre>";

    // 3. If connection is successful, call a tool to list files
    // --- FINAL FIX: Use the correct tool name 'list_directory' ---
    echo "<h3>Step 2: Calling 'list_directory' tool...</h3>";
    $listResult = $bridge->callTool('list_directory', ['path' => '.']); // '.' means the current directory
    
    echo "<h3>Result (Files in your project directory):</h3>";
    echo "<pre>" . json_encode($listResult, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h2>SUCCESS! Neo is now fully connected and can use file system tools.</h2>";

} catch (Exception $e) {
    echo "<h2>An error occurred:</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
}
