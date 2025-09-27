### Documentation: `api/delete_presentation.php`

**Objective:**
This API endpoint allows a logged-in user to delete one of their presentations from the database.

**File Path:**
`api/delete_presentation.php`

**Key Functionality:**

*   **Access Control:**
    *   Ensures that only logged-in users can delete presentations.
    *   Relies on `components/session_manager.php` for user login status verification.

*   **Input Handling:**
    *   Accepts a `POST` request with a JSON payload containing the `id` of the presentation to be deleted.

*   **Permission Check:**
    *   Before deletion, it verifies that the presentation identified by the `id` actually belongs to the currently logged-in user.
    *   This prevents users from deleting presentations owned by others.

*   **Database Deletion:**
    *   If the user has permission, it removes the corresponding record from the `presentations` table.

*   **JSON Response:**
    *   Returns a JSON response indicating success or failure of the deletion operation.

**Dependencies:**

*   `config/db_config.php`: For database connection.
*   `components/session_manager.php`: For user authentication and session management.

**Front-end Interaction:**

*   Used by `assets/js/my_profile.js` when a user clicks the "Delete" button in their "Content Vault".
*   Integrated with a custom confirmation modal for user verification before deletion.

### Future Improvements:

*   **Soft Delete:** Implement soft deletion (marking as deleted instead of permanent removal) for data recovery.
*   **Logging:** Log deletion events for auditing.