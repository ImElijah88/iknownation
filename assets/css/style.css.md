### Documentation: `assets/css/style.css`

**Objective:**
This is the global stylesheet for the Now Nation application, defining its overall visual design, theming, and core component styling.

**File Path:**
`assets/css/style.css`

**Key Functionality:**

*   **CSS Variables (Theming):**
    *   Defines `--primary-color`, `--primary-color-light`, and `--primary-color-hover` for consistent branding and easy theme switching.
    *   These variables can be dynamically overridden by JavaScript for presentation-specific color schemes.

*   **Body & Background:**
    *   Applies a custom font (`Inter`).
    *   Implements an animated gradient background (`gradient-flow` animation) that transitions smoothly between light and dark modes.

*   **Core Component Styling:**
    *   Provides base display and visibility rules for `presentation-container` and `slide` elements, controlling their active states.

*   **Sidemenu Styling:**
    *   Defines the visual behavior of the sidemenu items.
    *   Implements the expanding text effect for presentation titles on hover or when the sidebar is open (`#sidebar.open .sidebar-item .sidebar-text-container`).
    *   Controls the visibility of the `sidebar-actions` (download/remove buttons), making them `display: none` by default and `display: flex` only when the parent `sidebar-item` is `active`.

*   **Notification Styling:**
    *   Provides styles for the global notification pop-ups (`#notification`), including success and error states.

**Dependencies:**

*   Used by all HTML pages (e.g., `nownation.php`, `my_profile.php`, `admin_templates.php`) via a `<link>` tag in `components/header.php`.
*   Relies heavily on Tailwind CSS utility classes for layout and many visual properties.

### Future Improvements:

*   **CSS Variables:** Expand the use of CSS variables for more flexible theming.
*   **Responsiveness:** Further optimize for various screen sizes and devices.
*   **Performance:** Minify and concatenate CSS for production.