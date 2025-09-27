### Documentation: `logout.php`

**Objective:**
This file handles the user logout process for the Now Nation application.

**File Path:**
`logout.php`

**Key Functionality:**

*   **Session Termination:**
    *   Includes `components/session_manager.php` to access the `logoutUser()` function.
    *   Calls `logoutUser()` to destroy the current user's session, effectively logging them out of the application.

*   **Redirection:**
    *   After successfully logging out the user, the script redirects the browser back to the main application page (`nownation.php`).
    *   This ensures a smooth transition for the user after their session has ended.

**Dependencies:**

*   `components/session_manager.php`

### Future Improvements:

*   **Secure Logout:** Ensure all session-related cookies are properly invalidated on logout.
*   **Post-Logout Message:** Display a "You have been logged out" message on the redirected page.