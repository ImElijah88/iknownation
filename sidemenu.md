Plan: Sidemenu Component
Objective: Create a reusable PHP file for the application's main navigation sidemenu.

File to Create: sidemenu.php

Key Functionality (HTML):

✅ Main Structure: Create the <nav id="sidebar"> element.

✅ Header: Include the "Now Nation" logo and title.

✅ Dynamic Menu: Create an empty container (<div id="sidebar-menu"></div>). The main application's JavaScript will populate this area with presentation links.

✅ User Controls: Create an empty container (<div id="user-add-controls"></div>). JavaScript will add the + button here for logged-in users.

✅ "Add Presentation" Modal:

✅ Create a hidden modal (#add-presentation-modal).

✅ Inside, include an empty container (#presentation-selection-list). JavaScript will populate this with a list of available presentations when the user clicks the + button.

Export Controls: Create a container at the bottom of the sidemenu (#export-controls) that is hidden by default. JavaScript will populate this with export buttons (HTML, PDF, etc.) when a presentation is active.

Note: The dynamic behavior of the sidemenu (populating links, managing add/remove buttons, and modal interactions) is handled by `assets/js/common.js`.

### Future Improvements:

*   **Export Controls Population:** Implement JavaScript to populate the `#export-controls` container with buttons once `api/export.php` is ready.
*   **Search/Filter in Sidemenu:** Add a search bar or filter options directly within the sidemenu for large numbers of presentations.
*   **Drag-and-Drop Reordering:** Allow users to reorder presentations in their sidemenu.
