Product Requirements Document: Now Nation

1. Overall Goal
   To create a dynamic, user-centric web application where users can interact with gamified presentations. Guests can view a default presentation, while registered users can customize their workspace, generate new presentations from documents using a local LLM, and manage their unique content. The entire application will be built to run on a local server using PHP and a MySQL database.

2. User Experience Flow
   2.1. Guest User:

✅ Landing: Arrives on nownation.php.

✅ Default Content: Sees one pre-loaded, default presentation in the sidemenu.

✅ Interaction: Can fully interact with the default presentation (view slides, take quizzes).

✅ Available Actions: Can toggle the theme (light/dark) and open the login/register modal.

   2.2. Registered User:

✅ Login: Logs into their account.

✅ Personalized Workspace: The sidemenu is now empty, but a + button is visible.

✅ Adding Content: Clicks the + button, which opens a modal listing all available presentations (both default and their own saved ones). They can select multiple presentations to add to their sidemenu.

✅ Content Management: Can remove presentations from their sidemenu at any time using an x button.

✅ Profile & Creation: Clicks the "My Profile" link in the header to navigate to my_profile.php.

✅ Generation: On the profile page, uploads a document (.txt, .pdf, .doc) to generate a new, unique presentation using the integrated LLM.

✅ Saving & Exporting: Can view, save (to their account), delete, and export their created presentations in various formats (HTML, PDF, TXT).

3. Key Features (Detailed Breakdown)
✅ Modular Front-End: The application will be broken into reusable components (header.php, sidemenu.php) to ensure clean code and easy maintenance.

✅ Guest Experience: One "default" presentation (identified by user_id IS NULL in the database) will be loaded for all non-logged-in users.

✅ User Authentication: A robust login/registration system will manage user accounts.

✅ Customizable Sidemenu (Logged-in Users): The sidemenu will feature a + button to open a modal and select presentations to add to the current view.

✅ My Profile Page (my_profile.php): A "Content Vault" displaying all user-created presentations with options to View (add to sidemenu), Save (to DB), and Delete. It will also host the AI generation form.

✅ AI Presentation Generation (api/generate_presentation.php): A back-end script that accepts a document, sends its content to an LLM, and receives structured presentation data back.

Export Functionality (api/export.php): A back-end script to generate downloadable files (HTML, PDF, etc.) of presentations.

4. Technical Architecture
   The project will follow the file structure and asset management plan outlined in the assets_management.md document. All user-specific access and UI changes will be managed by the session_manager.php component and the main.js script.