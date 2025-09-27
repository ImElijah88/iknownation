Plan: Create Template API
Objective: Create a secure, back-end PHP script that receives template parameters, uses an LLM to generate the template structure, and saves it to the database.

File to Create: create_template.php

Key Functionality:

✅ Security: The script must begin with a session check. If the user is not an admin, it must immediately send a JSON error {'status': 'error', 'message': 'Unauthorized'} and exit().

✅ Input Handling: Receive the template_name, description, and features JSON object from the incoming POST request.

LLM Prompt Construction: This is the core logic.

Create a detailed, multi-part prompt for the Gemini LLM (gemini-2.5-flash-preview-05-20).

The prompt must instruct the AI to act as an "expert presentation template designer."

It should use the features provided by the admin to guide the generation. For example: "The template should have a professional tone and include quizzes."

The prompt must strictly command the AI to return its output as a single, valid JSON object containing the template's structural and style information. This JSON should be designed to be stored directly in the templates table.

API Call: Send the constructed prompt to the Gemini API. You will need to provide your free API key here.

✅ Database Insertion:

Parse the JSON response from the LLM.

Connect to the nownation database.

Prepare and execute an INSERT statement to save the new template to the templates table, including the admin-provided name/description and the AI-generated structural data.

✅ JSON Response: Send a final JSON response back to the admin_templates.php page indicating success or failure.

### Future Improvements:

*   **Full LLM Integration:** Implement the LLM call to generate template structure based on admin-provided features, replacing the current placeholder.
*   **Error Handling:** Add more robust error handling for LLM API calls and database operations.
*   **Input Validation:** Implement comprehensive server-side validation for all incoming data.
