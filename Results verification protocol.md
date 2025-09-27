AUTONOMOUS RESULTS VERIFICATION PROTOCOL

Activation: This protocol is automatically activated during the "Verification & Documentation Phase" of the AUTONOMOUS_WORKFLOW.MD after a task has been executed.

Core Directive: Your primary function is to ensure that the work you have completed produces the intended result and meets the project's quality standards. You must act as a Quality Assurance (QA) engineer, validating your own code against the initial requirements.

THE VERIFICATION CHECKLIST
You must answer the following questions for every task you complete.

1. Define the "Definition of Done"

Question: What were the specific success criteria for this task?

Action: State the clear, observable outcomes that prove the task is complete.

Example: "Definition of Done: The getUserPresentations feature is complete when a logged-in user can see a list of their presentation titles rendered on the dashboard/dash.html page, fetched dynamically from the database via the /api/get_presentations.php endpoint."

2. Outline the Verification Path (Testing Plan)

Question: What are the exact steps a human would take to test and verify that this feature works as intended?

Action: Provide a numbered list of simple, repeatable steps.

Example: "Verification Path:

Log in as a registered user.

Navigate to dashboard/dash.html.

Open the browser's developer tools and switch to the 'Network' tab.

Observe a fetch request being made to /api/get_presentations.php.

Confirm the request receives a 200 OK status and a JSON response containing presentation data.

Visually confirm that the presentation titles from the JSON response are now displayed as HTML elements on the page."

3. Assess for Potential Side Effects (Regression Check)

Question: Could the changes I made have unintentionally broken any other part of the application?

Action: Briefly analyze the potential impact on related files or features.

Example: "Regression Check: The changes were primarily in McpBridgeService.php and nownation.js. These changes are self-contained and are unlikely to affect user authentication or the guest-facing presentation, but the login process should be monitored."

Protocol Completion:
Once you have completed this checklist, you may proceed with the documentation steps (updating .md files) as outlined in the main man_at_work.MD.

This protocol ensures that every completed task is not just coded, but is also verified and validated.
