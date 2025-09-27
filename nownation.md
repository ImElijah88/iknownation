Plan: Main Application (nownation.php)
Objective: Create the main application shell that ties all components and logic together.

File to Create: nownation.php

Key Functionality (PHP):

Authentication Endpoint: The file must start with the PHP block that handles POST requests for login and registration. This logic should connect to the database, process the request, echo a JSON response, and then exit().

✅ Modular Includes: In the HTML body, use <?php include 'sidemenu.php'; ?> and <?php include 'header.php'; ?> to build the page from the reusable components.

Key Functionality (JavaScript):

✅ Global State: Initialize global variables for presentations, currentUser, etc. (Logic moved to `assets/js/common.js`)

✅ Initialization (fetchPresentationsAndInit): On page load, fetch all presentations from get_presentations.php and store them in the global presentations variable. (Logic moved to `assets/js/common.js`)

✅ Guest vs. User Logic:

✅ If not logged in: Automatically add the single default presentation to the sidemenu.

✅ If logged in: Leave the sidemenu empty and show the + Add Presentation button.

✅ Sidemenu Management:

✅ Implement the logic for the + button to open the "Add Presentation" modal and populate it with presentations that aren't already in the sidemenu.

✅ When a user selects a presentation from the modal, dynamically create the link and add it to the sidemenu.

✅ Implement the logic for the x (remove) button on each sidemenu item.

✅ Presentation Display: Handle the logic for switching between active presentations when a user clicks a link in the sidemenu. This includes loading the slides, quizzes, and activating the correct container.

Export Logic: When a presentation is active, populate the #export-controls container with buttons that link to the export.php script (e.g., <a href="export.php?id=...&format=pdf">...</a>).

### Future Improvements:

*   **Implement Export Logic:** Integrate the export functionality (buttons in `#export-controls`) once `api/export.php` is ready.
*   **Presentation Progress Saving:** Implement saving user's progress (current slide, XP) to the database.
*   **Gamification Features:** Expand on XP and badges, potentially adding leaderboards or more complex gamified elements.
*   **Search/Filter Presentations:** Add search and filter capabilities for presentations in the sidemenu.
