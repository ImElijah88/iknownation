Plan: Database Setup
Objective: Create the necessary MySQL database and tables for the Now Nation application.

File to Create: database_setup.sql

Instructions:
Create a single SQL script that performs the following actions in order:

✅ Creates a database named nownation if it doesn't already exist.

✅ Switches to using the nownation database.

✅ Creates the users table with the following columns:

✅ id (INT, Primary Key, AUTO_INCREMENT)

✅ email (VARCHAR, Unique)

✅ password (VARCHAR)

✅ role (ENUM('client','admin'), default 'client')

✅ progress_data (TEXT, JSON format)

✅ created_at (TIMESTAMP, default current time)

✅ Creates the presentations table with the following columns:

✅ id (INT, Primary Key, AUTO_INCREMENT)

✅ user_id (INT, can be NULL, links to users.id)

✅ template_id (INT, links to templates.id)

✅ presentation_key (VARCHAR, Unique)

✅ title (VARCHAR)

✅ slides (LONGTEXT, must be valid JSON)

✅ quizzes (LONGTEXT, must be valid JSON)

✅ speech (TEXT)

✅ colors (VARCHAR)

✅ Note: A `status` column (ENUM('temporary', 'saved')) was added to the `presentations` table during development.

✅ Creates the templates table with the following columns to define the structure and style of presentations:

✅ id (INT, Primary Key, AUTO_INCREMENT)

✅ template_name (VARCHAR) - The public name of the template (e.g., "Default Gamified").

✅ description (TEXT) - A short description of the template's style and features.

✅ features (JSON) - A list of features this template supports, which will help the LLM generate appropriate content. For example: {"has_quizzes": true, "max_slides": 10}.

✅ Inserts a default template into the templates table. This will be the only option available to users initially.

✅ template_name: "Default Gamified Template"

✅ description: "An engaging and interactive template with quizzes to enhance learning."

✅ features: {"has_quizzes": true, "max_slides": 10, "style": "fun_and_interactive"}

✅ Inserts at least one "default" presentation into the presentations table where the user_id is NULL and the template_id is 1. This will be the presentation that guest users see.

### Future Improvements:

*   **Migration Scripts:** For production environments, consider using a proper database migration tool (e.g., Phinx, Doctrine Migrations) instead of a single setup script.
*   **Indexing:** Review and add more indexes for performance optimization on frequently queried columns.
*   **Constraints:** Add more foreign key constraints or other database constraints for stricter data integrity.