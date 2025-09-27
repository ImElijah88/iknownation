Plan: My Profile Page
Objective: Create the user's personal hub for managing and generating presentations.

File to Create: my_profile.php

Key Functionality (PHP):

✅ Modular Includes: Use <?php include 'sidemenu.php'; ?> and <?php include 'header.php'; ?> to maintain a consistent look and feel.

Key Functionality (HTML):

✅ AI Generation Form:

✅ Create a form (#generate-form) with a file input (<input type="file">) that accepts .txt, .pdf, and .doc files.

✅ Include a dropdown to select a "Template".

✅ Include a "Generate Presentation" submit button.

✅ Add a status area (#generation-status) to display feedback (e.g., "Uploading...", "Processing...").

✅ User Content Vault:

✅ Create a container (#my-presentations-list) to display the user's generated and saved presentations.

✅ This will be populated dynamically by JavaScript. Each item should have "View", "Save", and "Delete" buttons.

Key Functionality (JavaScript):

✅ Fetch User Presentations: On page load, make a fetch call to get_presentations.php?user_id=... to get the list of presentations created by the current user and populate the "Content Vault".

✅ Form Submission:

✅ Add a submit event listener to the #generate-form.

✅ When submitted, use FormData to package the uploaded file and send it via fetch to generate_presentation.php.

✅ Display a loading message in the #generation-status area.

✅ When the response comes back from the AI, display the result in the "Content Vault" as a new, unsaved presentation.

✅ Note: Dynamic form fields are now generated based on selected template features.

✅ Vault Actions:

✅ View: Clicking "View" should somehow signal the main nownation.php page to add this presentation to the sidemenu (e.g., using localStorage or a session variable).

✅ Save: Clicking "Save" will make an API call to a new endpoint (we can add this to generate_presentation.php) to save the temporary presentation to the database.

✅ Delete: Clicking "Delete" will make an API call to delete the presentation from the database and remove it from the list.

✅ Export: Each presentation now has an "Export" dropdown, allowing the user to download the content as either an HTML or a TXT file.

### Future Improvements:

*   **File Type Support:** Implement content extraction for `.pdf`, `.doc`, `.docx` files in `api/generate_presentation.php`.
*   **Generation Progress Feedback:** Provide more detailed real-time feedback during LLM generation (e.g., progress bar, step-by-step updates).
*   **Template Management UI:** Allow users to manage their own templates (if applicable, based on future features).
*   **User Profile Data:** Display and allow editing of user profile information beyond just email.
