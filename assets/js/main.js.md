### Documentation: `assets/js/main.js`

**Objective:**
This file contains global JavaScript logic for common UI interactions and functionalities that are shared across multiple pages of the Now Nation application.

**File Path:**
`assets/js/main.js`

**Key Functionality:**

*   **Theme Management:**
    *   Initializes the application's theme (light/dark mode) based on `localStorage` or system preference.
    *   Handles the click event for the theme toggle button, updating the theme and saving the preference.

*   **Notification System:**
    *   Provides a global `window.showNotification(message, type)` function.
    *   Displays temporary success or error messages to the user in a consistent style.

*   **Modal Management:**
    *   Provides global `window.openModal(modalId)` and `window.closeModal(modalId)` functions.
    *   Manages the visibility of the main login/register modal (`#login-modal`) and the profile modal (`#profile-modal`).
    *   Handles closing modals when clicking outside their content area.
    *   Manages the display of different views within the login modal (Login, Register, Forgot Password) and transitions between them.
    *   Includes logic for toggling password visibility in input fields.

*   **Authentication Form Handling:**
    *   Attaches event listeners to the login (`#login-form`), registration (`#register-form`), and forgot password (`#forgot-password-form`) forms.
    *   **Login:** Sends user credentials to `api/auth.php` via `POST` request. On success, reloads the page to update the user's state.
    *   **Registration:** Sends user credentials to `api/auth.php` via `POST` request. On success, resets the form and switches to the login view.
    *   **Forgot Password:** Currently a front-end placeholder; it displays a notification that the feature is not yet implemented.

**Dependencies:**

*   `components/header.php`: Relies on the HTML structure and IDs defined within the header for its DOM elements.
*   `api/auth.php`: Communicates with this API for user authentication.

**Usage:**

*   This script is included in `components/header.php`, making its functionalities available on all pages that include the header.

### Future Improvements:

*   **"Remember Me" Functionality:** Implement actual "Remember Me" logic (e.g., using secure cookies or `localStorage` for token).
*   **Forgot Password UI/UX:** Improve the UI/UX for the "Forgot Password" flow.
*   **Accessibility:** Enhance accessibility for modals and form interactions.
*   **Error Logging:** Implement more robust client-side error logging.