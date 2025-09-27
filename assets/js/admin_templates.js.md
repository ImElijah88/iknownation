# JavaScript: `admin_templates.js`

## Description

This script provides all the client-side functionality for the **Admin Templates** page (`admin_templates.php`). It handles fetching and displaying existing templates, creating new templates via a form, and deleting templates.

## Key Functionality

1.  **`loadTemplates()`**
    *   **Trigger:** Called on page load (`DOMContentLoaded`).
    *   **Action:** Makes a `fetch` request to `api/get_all_templates.php`.
    *   **On Success:** Calls `renderTemplates()` to display the list of templates.
    *   **On Failure:** Displays an error message in the template list container.

2.  **`renderTemplates(templates)`**
    *   **Trigger:** Called by `loadTemplates()`.
    *   **Action:** Clears the existing list and dynamically creates and appends an HTML element for each template, displaying its name, description, and features. It also adds a "Delete" button to each.

3.  **`handleFormSubmit(event)`**
    *   **Trigger:** When the `#template-creator-form` is submitted.
    *   **Action:**
        *   Prevents the default form submission.
        *   Constructs a `features` object from the checked feature checkboxes.
        *   Sends all form data (name, description, features) to `api/create_template.php` via a `POST` request.
        *   Displays status messages (Saving, Success, Error) in the `#generation-status` div.
        *   On success, it resets the form and calls `loadTemplates()` to refresh the list.

4.  **`handleDeleteTemplate(templateId, templateEl)`**
    *   **Trigger:** Called when a user confirms the deletion after clicking a `.delete-btn`.
    *   **Action:**
        *   Sends the `templateId` to `api/delete_template.php` via a `POST` request.
        *   On success, it removes the corresponding HTML element (`templateEl`) from the DOM and shows a success message.
        *   On failure, it displays an error message.

## Event Listeners

-   A `submit` listener on the main form triggers `handleFormSubmit`.
-   A delegated `click` listener on the template list container (`#existing-templates-list`) checks for clicks on `.delete-btn` elements to initiate the deletion process.
