### Documentation: `api/get_template_details.php`

**Objective:**
This API endpoint retrieves detailed information for a specific presentation template from the database.

**File Path:**
`api/get_template_details.php`

**Key Functionality:**

*   **Input Handling:**
    *   Accepts a `template_id` as a GET parameter.
    *   Validates that a `template_id` is provided.

*   **Database Query:**
    *   Connects to the `nownation` database.
    *   Queries the `templates` table to fetch the `id`, `template_name`, `description`, and `features` for the specified `template_id`.

*   **Data Processing:**
    *   Decodes the `features` JSON string into a PHP associative array.

*   **JSON Response:**
    *   Returns a JSON object containing a `status` (success/error) and the `template` details.
    *   If the template is not found or the `template_id` is missing, it returns an appropriate error message.

**Dependencies:**

*   `config/db_config.php`: For database connection.

**Front-end Interaction:**

*   Used by `assets/js/my_profile.js` to fetch the specific features of a selected template, which are then used to dynamically render input fields in the presentation generation form.

### Future Improvements:

*   **Error Handling:** Implement more specific error messages for invalid template IDs.
*   **Caching:** Implement server-side caching.