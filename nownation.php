<?php
// The main application page starts by including the header.
// The header itself includes the session manager, so user state is available.
require_once 'components/header.php';
?>

<div class="flex h-screen">
    <!-- Sidebar Hover Trigger -->
    <div id="sidebar-hover-trigger" class="fixed top-0 left-0 h-full z-10 hidden md:block" style="width: 100px;"></div>

    <?php
    // The sidemenu is included to provide navigation.
    require_once 'components/sidemenu.php';
    ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 pt-20 overflow-y-auto relative">
        <!-- Hamburger Menu for mobile -->
        <button id="hamburger-btn" class="md:hidden absolute top-6 left-6 p-2 rounded-lg bg-white/30 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 z-30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Presentation Wrapper -->
        <!-- This is where JavaScript will inject the active presentation content. -->
        <div id="presentation-wrapper" class="w-full h-full flex items-center justify-center"></div>

    </main>
</div>

<!-- Quiz Modal -->
<div id="quiz-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-lg w-full">
        <h2 id="quiz-question" class="text-2xl font-bold mb-6 text-center" style="color: var(--primary-color-light);"></h2>
        <div id="quiz-options" class="space-y-4"></div>
        <div id="quiz-feedback" class="mt-6 text-center font-semibold"></div>
    </div>
</div>

<!-- Include JavaScript files -->
<!-- main.js handles global logic like theme and modals -->
<script src="assets/js/main.js"></script>
<!-- nownation.js handles the specific logic for this page -->
<script src="assets/js/nownation.js"></script>

</body>
</html>
