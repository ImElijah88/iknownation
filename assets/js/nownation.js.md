### Documentation: `assets/js/nownation.js`

**Objective:**
This file contains the page-specific JavaScript logic for the main presentation viewer (`nownation.php`). It manages the display and interaction of presentations.

**File Path:**
`assets/js/nownation.js`

**Key Functionality:**

*   **Presentation Initialization:**
    *   On page load, it determines which presentation to display.
    *   Prioritizes a `presentation` key found in the URL query parameters.
    *   If no URL parameter, it defaults to the first presentation in the user's sidemenu (managed by `common.js`).

*   **Presentation Rendering (`renderPresentation`):**
    *   Takes presentation data (title, slides HTML, quizzes, colors) and dynamically injects it into the `presentation-wrapper` on the page.
    *   Constructs the main presentation container, including the header, slide area, and navigation buttons.

*   **Interactive Presentation Logic (`runPresentationLogic`):**
    *   Manages the state of the currently viewed presentation (current slide, XP score).
    *   Handles slide transitions (Next/Previous buttons, Start button).
    *   Controls the visibility of the presentation header and navigation based on the current slide.
    *   **Quiz Integration:** Displays interactive quiz questions after specific slides, handles user answers, provides feedback, and updates XP scores.

*   **Theme Color Application:**
    *   Applies presentation-specific primary, light, and hover colors by updating CSS variables (`--primary-color`, etc.) on the document's root element.

*   **Sidebar Hover Logic:**
    *   Manages the global sidebar hover effect, expanding/collapsing the entire sidemenu based on mouse entry/exit from a defined trigger area.

**Dependencies:**

*   `assets/js/common.js`: Relies heavily on global variables (`allPresentations`, `sidemenuKeys`, `APP_USER`) and shared functions (`updateUIForUser`, `renderSidemenu`, `downloadSpeech`, `removePresentationFromSidemenu`) provided by `common.js`.
*   `api/get_presentations.php`: Indirectly relies on this API (via `common.js`) for fetching presentation data.
*   `components/sidemenu.php`: Interacts with the HTML structure of the sidemenu.

**Usage:**

*   This script is included specifically on `nownation.php` to provide its core interactive presentation viewing experience.

### Future Improvements:

*   **Presentation Progress Saving:** Implement saving user's progress (current slide, XP) to the database.
*   **Gamification UI:** Display XP and badge progress more prominently.
*   **Export Buttons:** Integrate the export buttons once the backend is ready.
*   **Keyboard Navigation:** Add keyboard navigation for slides.