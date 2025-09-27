### Documentation: `creating neo.md`

**Objective:**
This document provides exact, step-by-step instructions to set up and run the Python MCP (Model Context Protocol) Bridge Server. This server acts as an intermediary, allowing our PHP application (Now Nation) to communicate with the MCP File System server and other tools.

**Goal:** Get the Python MCP Bridge Server Running.

---

#### **Step 1: Check if Python is Installed**

We need Python for the bridge server.

1.  **Open your Command Prompt:**
    *   Click the Windows Start button.
    *   Type `cmd` in the search bar.
    *   Click on "Command Prompt" to open it.

2.  **Type the following command and press Enter:**
    ```bash
    python --version
    ```

3.  **What to look for:**
    *   If you see a version number like `Python 3.9.7` or `Python 3.10.5`, then Python is installed. **Great!** You can move to **Step 2**.
    *   If you get an error message like `'python' is not recognized as an internal or external command, operable program or batch file.`, then Python is **NOT** installed.

4.  **If Python is NOT installed, follow these sub-steps:**
    *   **a.** Open your web browser and go to this exact address: `https://www.python.org/downloads/windows/`
    *   **b.** Under "Python Releases for Windows", find the latest "Python 3.x.x" stable release. Click on the "Windows installer (64-bit)" link to download it.
    *   **c.** Once downloaded, double-click the installer file (e.g., `python-3.x.x-amd64.exe`).
    *   **d.** In the installer window, **VERY IMPORTANT:** At the bottom, **CHECK THE BOX** that says **"Add Python to PATH"**.
    *   **e.** Then, click "Install Now" and follow the prompts to complete the installation.
    *   **f.** After installation, **CLOSE** your current Command Prompt window and **OPEN A NEW ONE**.
    *   **g.** In the new Command Prompt, type `python --version` again and press Enter. It should now show the Python version.

---

#### **Step 2: Check if Node.js and npm are Installed**

We need Node.js and npm for a part of the MCP server.

1.  **In your Command Prompt** (the one where you checked Python, or a new one if you just installed Python), type the following command and press Enter:
    ```bash
    node --version
    ```
2.  **Then, type this command and press Enter:**
    ```bash
    npm --version
    ```

3.  **What to look for:**
    *   If you see version numbers for both (e.g., `v18.17.1` for Node and `9.6.7` for npm), then they are installed. **Great!** You can move to **Step 3**.
    *   If you get an error for either, then Node.js is **NOT** installed.

4.  **If Node.js is NOT installed, follow these sub-steps:**
    *   **a.** Open your web browser and go to this exact address: `https://nodejs.org/en/download`
    *   **b.** Click on the "Windows Installer" link (the LTS version is usually recommended).
    *   **c.** Once downloaded, double-click the installer file (e.g., `node-v18.x.x-x64.msi`).
    *   **d.** Follow the installation prompts. Node.js usually includes npm automatically.
    *   **e.** After installation, **CLOSE** your current Command Prompt window and **OPEN A NEW ONE**.
    *   **f.** In the new Command Prompt, type `node --version` and `npm --version` again. They should now show version numbers.

---

#### **Step 3: Create a Folder for the Bridge Server**

This will be the dedicated place for our Python bridge server files.

1.  **Open File Explorer** (the yellow folder icon on your taskbar or desktop).
2.  **Navigate to your project directory:**
    *   Go to `C:`
    *   Then `xampp`
    *   Then `htdocs`
    *   Then `videoideas`
    *   Then `Nownationproject`
    *   So you should be in: `C:\xampp\htdocs\videoideas\Nownationproject\`

3.  **Create a NEW folder inside `Nownationproject`:**
    *   Right-click in an empty space within the `Nownationproject` folder.
    *   Select `New` -> `Folder`.
    *   Type the exact name: `mcp_bridge`
    *   Press Enter.

---

#### **Step 4: Create the `.env` File**

This file will store your API key securely.

1.  **Open Notepad** (search for "Notepad" in the Windows search bar).
2.  **Copy and paste the following exact line into Notepad:**
    ```
    OPENAI_API_KEY="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"
    ```
3.  Go to `File` -> `Save As...`
4.  **Navigate to the `mcp_bridge` folder:** `C:\xampp\htdocs\videoideas\Nownationproject\mcp_bridge\`
5.  In the "File name" box, type: `.env` (make sure to include the dot at the beginning).
6.  In the "Save as type" dropdown, select "All Files (*.*)".
7.  Click "Save".

---

#### **Step 5: Create the `mcp_bridge.py` File**

This is the Python code for our bridge server.

1.  **Open Notepad** again.
2.  **Copy and paste the entire code block below into Notepad:**
    ```python
    import asyncio
    from flask import Flask, request, jsonify
    from typing import Optional
    from contextlib import AsyncExitStack

    from mcp import ClientSession, StdioServerParameters
    from mcp.client.stdio import stdio_client
    from openai import OpenAI
    from dotenv import load_dotenv
    import json
    import os

    load_dotenv()

    app = Flask(__name__)

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
            
            server_params = StdioServerParameters(**server_config)
            stdio_transport = await self.exit_stack.enter_async_context(stdio_client(server_params))
            self.stdio, self.write = stdio_transport
            self.session = await self.exit_stack.enter_async_context(ClientSession(self.stdio, self.write))
            await self.session.initialize()
            print("MCP Client connected and initialized.")

        async def call_tool(self, tool_name: str, tool_args: dict):
            if not self.session:
                raise Exception("MCP Client not connected. Call /mcp/connect first.")
            result = await self.session.call_tool(tool_name, tool_args)
            return result.content

        async def cleanup(self):
            if self.session:
                await self.exit_stack.aclose()
                self.session = None
                print("MCP Client cleaned up.")

    mcp_client = MCPClient()

    @app.route('/mcp/connect', methods=['POST'])
    async def connect_mcp():
        try:
            server_config = request.json.get('server_config')
            if not server_config:
                return jsonify({"error": "server_config missing"}), 400
            
            await asyncio.create_task(mcp_client.connect_to_server(server_config))
            return jsonify({"status": "connected"})
        except Exception as e:
            return jsonify({"error": str(e)}), 500

    @app.route('/mcp/call_tool', methods=['POST'])
    async def call_mcp_tool():
        try:
            tool_name = request.json.get('tool_name')
            tool_args = request.json.get('tool_args', {})
            if not tool_name:
                return jsonify({"error": "tool_name missing"}), 400
            
            result = await asyncio.create_task(mcp_client.call_tool(tool_name, tool_args))
            return jsonify({"status": "success", "result": result})
        except Exception as e:
            return jsonify({"error": str(e)}), 500

    @app.route('/mcp/cleanup', methods=['POST'])
    async def cleanup_mcp():
        try:
            await asyncio.create_task(mcp_client.cleanup())
            return jsonify({"status": "cleaned up"})
        except Exception as e:
            return jsonify({"error": str(e)}), 500

    if __name__ == '__main__':
        print("To run this server, open Command Prompt in this folder and type:")
        print("uvicorn mcp_bridge:app --host 0.0.0.0 --port 5001 --reload")
        print("Keep this window open while you are using the Now Nation app.")
    ```
3.  Go to `File` -> `Save As...`
4.  **Navigate to the `mcp_bridge` folder:** `C:\xampp\htdocs\videoideas\Nownationproject\mcp_bridge\`
5.  In the "File name" box, type: `mcp_bridge.py`
6.  In the "Save as type" dropdown, select "All Files (*.*)".
7.  Click "Save".

---

#### **Step 6: Install Python Libraries**

1.  **Open your Command Prompt** (if you closed it, open it again).
2.  **Navigate to the `mcp_bridge` folder.** Type this command and press Enter:
    ```bash
    cd C:\xampp\htdocs\videoideas\Nownationproject\mcp_bridge
    ```
3.  Now, install the necessary libraries. Type this command and press Enter:
    ```bash
    pip install flask uvicorn python-dotenv mcp openai
    ```
    *   Wait for it to finish. You might see a lot of text scrolling.

---

#### **Step 7: Run the MCP Bridge Server**

1.  In the same Command Prompt window (still in the `mcp_bridge` folder), type this command and press Enter:
    ```bash
    uvicorn mcp_bridge:app --host 0.0.0.0 --port 5001 --reload
    ```
2.  **What to expect:**
    *   You should see messages like `INFO:     Uvicorn running on http://0.0.0.0:5001 (Press CTRL+C to quit)`.
    *   **Keep this Command Prompt window OPEN** while you are using your Now Nation application. If you close it, the PHP app won't be able to talk to the MCP server.

---

Once you have successfully completed all these steps and the `uvicorn` server is running, please let me know. Then, I will update Neo (our PHP agent) to use this new bridge server for file operations.
