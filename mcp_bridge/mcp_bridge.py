import asyncio
from flask import Flask, request, jsonify
from typing import Optional
from contextlib import AsyncExitStack
from asgiref.wsgi import WsgiToAsgi

from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from openai import OpenAI
from dotenv import load_dotenv
import json
import os

load_dotenv()

# Create the Flask instance first, with a different name
flask_app = Flask(__name__)

class MCPClient:
    def __init__(self):
        self.session: Optional[ClientSession] = None
        self.exit_stack = AsyncExitStack()
        self.openai = OpenAI(
            base_url="https://openrouter.ai/api/v1"
        )

    async def connect_to_server(self, server_config):
        if self.session:
            await self.cleanup()
        
        print("[DEBUG] Preparing to start MCP server subprocess...")
        server_params = StdioServerParameters(**server_config)
        
        stdio_transport = await self.exit_stack.enter_async_context(stdio_client(server_params))
        
        print("[DEBUG] Subprocess started successfully. Initializing session...")
        self.stdio, self.write = stdio_transport
        self.session = await self.exit_stack.enter_async_context(ClientSession(self.stdio, self.write))
        await self.session.initialize()

        # --- Automatically list available tools upon connection ---
        print("[INFO] Discovering available tools from the MCP server...")
        list_tools_response = await self.session.list_tools()
        tool_names = [tool.name for tool in list_tools_response.tools]
        print(f"[INFO] MCP Server connected. Available tools are: {tool_names}")
        # --- END ---

        print("MCP Client connected and initialized.")

    async def call_tool(self, tool_name: str, tool_args: dict):
        if not self.session:
            raise Exception("MCP Client not connected. Call /mcp/connect first.")
        
        print(f"[DEBUG] Calling tool: {tool_name} with args: {tool_args}")
        tool_result = await self.session.call_tool(tool_name, tool_args)
        print("[DEBUG] Tool call successful.")
        
        content = tool_result.content

        # Handle the case where the content is a list of objects.
        if isinstance(content, list):
            print("[DEBUG] Content is a list. Processing each item for serialization.")
            processed_list = []
            for item in content:
                if hasattr(item, 'text'):
                    try:
                        # Attempt to parse the text as JSON, as it often is
                        processed_list.append(json.loads(item.text))
                    except (json.JSONDecodeError, TypeError):
                        # If not JSON, just append the raw text
                        processed_list.append(item.text)
                else:
                    # If the item is already a simple type, append it directly
                    processed_list.append(item)
            return processed_list

        # Fallback for single objects
        if hasattr(content, 'text'):
            print("[DEBUG] Content is a single TextContent-like object.")
            try:
                return json.loads(content.text)
            except json.JSONDecodeError:
                return content.text
        elif isinstance(content, (str, dict, list, int, float, bool, type(None))):
            print("[DEBUG] Content is already JSON serializable.")
            return content
        else:
            print("[DEBUG] Content is not a recognized type, converting to string as fallback.")
            return str(content)

    async def cleanup(self):
        if self.session:
            await self.exit_stack.aclose()
            self.session = None
            print("MCP Client cleaned up.")

mcp_client = MCPClient()

# Define all routes on the flask_app instance
@flask_app.route('/mcp/connect', methods=['POST'])
async def connect_mcp():
    try:
        print("[DEBUG] Received request on /mcp/connect")
        server_config = request.json.get('server_config')
        if not server_config:
            return jsonify({"error": "server_config missing"}), 400
        
        await mcp_client.connect_to_server(server_config)
        
        print("[DEBUG] /mcp/connect request handled successfully.")
        return jsonify({"status": "connected"})
    except Exception as e:
        print(f"[ERROR] An error occurred in connect_mcp: {e}")
        return jsonify({"error": str(e)}), 500

@flask_app.route('/mcp/call_tool', methods=['POST'])
async def call_mcp_tool():
    try:
        print("[DEBUG] Received request on /mcp/call_tool")
        tool_name = request.json.get('tool_name')
        tool_args = request.json.get('tool_args', {})
        if not tool_name:
            return jsonify({"error": "tool_name missing"}), 400
        
        result = await mcp_client.call_tool(tool_name, tool_args)

        print("[DEBUG] /mcp/call_tool request handled successfully.")
        return jsonify({"status": "success", "result": result})
    except Exception as e:
        print(f"[ERROR] An error occurred in call_mcp_tool: {e}")
        return jsonify({"error": str(e)}), 500

@flask_app.route('/mcp/cleanup', methods=['POST'])
async def cleanup_mcp():
    try:
        await mcp_client.cleanup()
        return jsonify({"status": "cleaned up"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@flask_app.route('/llm/completion', methods=['POST'])
async def llm_completion():
    try:
        print("[DEBUG] Received request on /llm/completion")
        prompt = request.json.get('prompt')
        if not prompt:
            return jsonify({"error": "prompt missing"}), 400

        completion = mcp_client.openai.chat.completions.create(
            model="mistralai/mixtral-8x7b-instruct",
            messages=[
                {
                    "role": "user",
                    "content": prompt,
                },
            ],
        )

        print("[DEBUG] /llm/completion request handled successfully.")
        return jsonify({"status": "success", "result": completion.choices[0].message.content})
    except Exception as e:
        print(f"[ERROR] An error occurred in llm_completion: {e}")
        return jsonify({"error": str(e)}), 500

# Now, create the 'app' variable that Uvicorn uses by wrapping the Flask app
app = WsgiToAsgi(flask_app)

if __name__ == '__main__':
    print("To run this server, open Command Prompt in this folder and type:")
    print("uvicorn mcp_bridge:app --host 0.0.0.0 --port 5001 --reload")
    print("Keep this window open while you are using the Now Nation app.")

