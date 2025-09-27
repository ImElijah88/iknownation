Plan: Header Component
Objective: Create a reusable PHP file for the application's header, including all modals for user interaction.

File to Create: header.php

Key Functionality (HTML):

✅ Top-Right Controls: Create the main container (#user-controls) for the top-right of the page.

✅ Include the theme toggle button (light/dark mode).

✅ Include an empty container (#auth-container) where JavaScript will dynamically insert either the "Login" button or the "My Profile"/"Logout" buttons.

✅ Login/Register Modal (#login-modal):

✅ Create a single modal containing three distinct views: Login (#login-view), Register (#register-view), and Forgot Password (#forgot-password-view).

✅ Only one view should be visible at a time.

✅ Include links within the forms to switch between these views (e.g., "Don't have an account?", "Forgot password?").

✅ My Profile Modal (#profile-modal):

✅ Create a separate modal for the user's profile.

✅ It should be hidden by default.

✅ Include placeholders with specific IDs for the user's total XP (#profile-total-xp) and their earned badges (#profile-badges), which will be populated by JavaScript.

### Future Improvements:

*   **Profile Modal Content:** Populate the `#profile-modal` with actual user data (XP, badges) from the database.
*   **Forgot Password Backend:** Implement the back-end logic for password recovery.
*   **Accessibility:** Enhance accessibility features (ARIA attributes, keyboard navigation).
