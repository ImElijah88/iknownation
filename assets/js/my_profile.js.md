### Documentation: `assets/js/my_profile.js`

**Objective:**
This file contains the page-specific JavaScript logic for the user's profile page (`my_profile.php`). It manages the display of user-generated content and the presentation generation process.

**File Path:**
`assets/js/my_profile.js`

**Key Functionality:**

*   **User Content Vault Management:**
    *   Fetches and displays all presentations belonging to the logged-in user from the database.
    *   Dynamically renders each presentation in the "Content Vault" (`#my-presentations-list`).
    *   For each presentation, it creates "View", "Save" (for temporary presentations), and "Delete" buttons.

*   **Template Selection & Dynamic Form Fields:**
    *   Fetches all available templates from `api/get_all_templates.php` and populates the template selection dropdown.
    *   When a template is selected, it reads its `features` and dynamically renders additional input fields (e.g., for quizzes, tone, key points) in the "Generate Presentation" form.

*   **Presentation Generation:**
    *   Handles the submission of the "Generate Presentation" form.
    *   Processes file uploads (currently `.txt` files for content extraction).
    *   Collects all form data, including dynamic inputs, and sends it to `api/generate_presentation.php`.
    *   Displays status messages during the generation process and updates the "Content Vault" upon completion.

*   **Presentation Saving (`handleSavePresentation`):**
    *   Triggers an API call to `api/save_presentation.php` to change a temporary presentation's status to 'saved'.
    *   Updates the UI to reflect the saved status (hides the "Save" button).

*   **Presentation Deletion (`handleDeletePresentation`):**
    *   Initiates a custom confirmation modal to verify deletion intent.
    *   If confirmed, triggers an API call to `api/delete_presentation.php` to remove the presentation from the database.
    *   Removes the presentation's element from the DOM upon successful deletion.

*   **Presentation Viewing (`handleViewPresentation`):**
    *   Adds the selected presentation's key to the `sidemenuKeys` in `localStorage`.
    *   Redirects the user to `nownation.php` to view the presentation, ensuring it appears in the sidemenu.

**Dependencies:**

*   `assets/js/common.js`: For shared functions like `showNotification` and global state (`APP_USER`).
*   `api/get_presentations.php`: For fetching user's presentations.
*   `api/get_all_templates.php`: For populating the template dropdown.
*   `api/generate_presentation.php`: For submitting presentation generation requests.
*   `api/save_presentation.php`: For updating presentation status.
*   `api/delete_presentation.php`: For deleting presentations.

**Usage:**

*   This script is included specifically on `my_profile.php` to provide its interactive content management and generation features.

### Future Improvements:

*   **File Type Support UI:** Provide clearer feedback on supported file types for generation.
*   **Generation Progress UI:** Implement a more detailed progress indicator during LLM generation.
*   **Template Management UI:** Add UI for users to manage their own templates (if applicable).
*   **Profile Editing:** Allow users to edit their profile information.