Plan: Admin Template Creator Page
Objective: Create a secure, admin-only page for defining and generating new presentation templates.

File to Create: admin_templates.php

Key Functionality (PHP):

✅ Access Control: The script must start with PHP logic to check for a user session. If the user is not logged in OR their role is not 'admin', they must be redirected to the main nownation.php page immediately.

✅ Modular Includes: Use <?php include 'sidemenu.php'; ?> and <?php include 'header.php'; ?> for a consistent UI.

Key Functionality (HTML):

✅ Main Form (#template-creator-form):

✅ Template Name: A text input for template_name.

✅ Description: A textarea for the description.

✅ Features: A section with checkboxes or toggle switches that will be used to construct the features JSON object. Examples:

[x] Include Quizzes

[ ] Professional Tone

[x] Use Emojis

[ ] Image Placeholders

✅ Submit Button: A button with the text "Generate & Save Template".

✅ Status Area (#generation-status): A div to display feedback from the AI generation process (e.g., "Sending to AI...", "Template created successfully!").

Existing Templates List: A section that fetches and displays all templates currently in the templates table, so the admin can see what already exists.

Key Functionality (JavaScript):

Form Submission: Add a submit event listener to the form.

Data Packaging: On submit, prevent the default form action. Gather the template_name, description, and construct a JSON object from the selected features.

API Call: Send this data via a fetch POST request to the create_template.php endpoint.

Handle Response: Display the success or error message from the API in the #generation-status area. On success, dynamically add the new template to the "Existing Templates List" on the page.

### Future Improvements:

*   **Implement JavaScript for Form Submission:** Develop `assets/js/admin_templates.js` to handle form submission to `api/create_template.php`.
*   **Implement Listing Existing Templates:** Fetch and display existing templates from the database in the `#existing-templates-list` section.
*   **Edit/Delete Templates:** Add functionality to edit or delete existing templates.
*   **Dynamic Feature Input:** Improve the "Features" section to allow more flexible input (e.g., text fields for `max_slides`, dropdowns for `style` options) rather than just checkboxes.
*   **LLM Integration for Template Creation:** Integrate `api/create_template.php` with an LLM to generate more complex template structures based on admin input.
