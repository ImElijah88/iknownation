# API Endpoint: `delete_template.php`

## Description

This script handles the deletion of a specific presentation template. It is an admin-only endpoint.

## Parameters

This script expects a JSON object in the request body with the following parameter:

-   `id` (integer, required): The ID of the template to be deleted.

## Workflow

1.  **Security Check:** The script first verifies that the current user has admin privileges using the `isAdmin()` function from `session_manager.php`. If not, it returns a 403 Forbidden error.
2.  **Input Validation:** It checks if the `id` is present in the JSON payload.
3.  **Database Deletion:** It prepares and executes a `DELETE` statement on the `templates` table for the given `id`.
4.  **Response:**
    *   If the deletion is successful and one or more rows were affected, it returns a `status: success` message.
    *   If no rows were affected (e.g., the template ID didn't exist), it returns a 404 Not Found error.
    *   If the database query fails, it returns a 500 Internal Server Error.

## Status

-   [✅] Implemented endpoint for deleting templates.

## Wish List / Future Improvements

-   [ ] Add a check to see if any presentations are currently using the template before allowing deletion (soft delete might be better).
