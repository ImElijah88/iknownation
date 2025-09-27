Plan: Session Manager
Objective: To manage user sessions and authentication status across the application.

File to Create: session_manager.php

Key Functionality:

✅ Starts a PHP session (`session_start()`).

✅ `isLoggedIn()`: Checks if a user is currently logged in.

✅ `isAdmin()`: Checks if the logged-in user has an 'admin' role.

✅ `getCurrentUser()`: Retrieves the data of the currently logged-in user from the session.

✅ `loginUser(array $user)`: Logs a user in by storing their data in the session.

✅ `logoutUser()`: Logs the current user out by destroying the session.

### Future Improvements:

✅ **Session Security:** Implemented more advanced session security measures (e.g., session fixation prevention, session hijacking prevention, stricter cookie settings).
✅ **Role-Based Access Control (RBAC):** Expanded `isAdmin()` to a more generic `hasRole()` system for finer-grained access control.
✅ **Rate Limiting:** Implemented rate limiting for login attempts to prevent brute-force attacks.