### Documentation: `api/auth.php`

**Objective:**
This API endpoint handles user authentication, including registration and login, for the Now Nation application.

**File Path:**
`api/auth.php`

**Key Functionality:**

*   **Request Handling:**
    *   Accepts `POST` requests with JSON payloads.
    *   Expects an `action` field (`'register'` or `'login'`) and user credentials (email, password).

*   **User Registration (`action: 'register'`):**
    *   Receives email and password.
    *   Checks if the email already exists in the `users` table.
    *   Hashes the password using `password_hash()` for secure storage.
    *   Inserts the new user's email and hashed password into the `users` table.
    *   Returns a JSON response indicating success or failure.

*   **User Login (`action: 'login'`):
    *   Receives email and password.
    *   Retrieves user data from the `users` table based on the provided email.
    *   Verifies the provided password against the stored hashed password using `password_verify()`.
    *   If login is successful, it calls `loginUser()` from `session_manager.php` to establish the user's session.
    *   Returns a JSON response with success status, a message, and the logged-in user's data (excluding password).

*   **Security:**
    *   Uses prepared statements (`mysqli::prepare()`) to prevent SQL injection.
    *   Hashes passwords before storing them in the database.

*   **Dependencies:**
    *   `config/db_config.php`: For database connection.
    *   `components/session_manager.php`: For managing user sessions (`loginUser()`).

*   **Front-end Interaction:**
    *   Used by `assets/js/main.js` to handle login and registration form submissions.

### Future Improvements:

*   **Password Recovery:** Implement the back-end logic for "Forgot Password" (email sending, token verification, password reset).
*   **Email Verification:** Add email verification for new registrations.
*   **Rate Limiting:** Implement rate limiting for login and registration attempts.
*   **OAuth/SSO:** Consider integrating third-party authentication (Google, Facebook).