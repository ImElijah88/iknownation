<!-- 
 * Sidemenu Component
 * 
 * This file defines the structure of the main navigation sidebar.
 * It is designed to be highly dynamic, with its content managed by JavaScript.
-->
<nav id="sidebar" class="p-4 flex flex-col items-start space-y-2 z-20 fixed top-0 left-0 h-full w-auto">
    <!-- App Header -->
    <a href="/nownation.php" class="w-full flex items-center p-3 mb-4">
        <span class="p-2 rounded-lg" style="background-color: var(--primary-color)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
        </span>
        <div class="sidebar-text-container ml-3">
            <h1 class="text-xl font-bold">Now Nation</h1>
        </div>
    </a>

    <!-- Dynamic Menu Items -->
    <!-- This div will be populated by nownation.js with presentation links -->
    <div id="sidebar-menu" class="w-full space-y-2"></div>

    <!-- User-specific Controls -->
    <!-- The '+' button for logged-in users will be injected here by JavaScript -->
    <div id="user-add-controls" class="w-full pt-4 mt-4 border-t border-black/10 dark:border-white/10"></div>

    <!-- Export Controls -->
    <!-- This container will be populated with export buttons by JavaScript when a presentation is active -->
    <div id="export-controls" class="w-full absolute bottom-4 px-4"></div>
</nav>

<!-- "Add Presentation" Modal -->
<div id="add-presentation-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-2xl w-full relative">
        <button id="close-add-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <h2 class="text-2xl font-bold mb-6 text-center">Add Presentations to Your Workspace</h2>
        <!-- This list will be populated by JavaScript with available presentations -->
        <div id="presentation-selection-list" class="space-y-3 max-h-96 overflow-y-auto"></div>
    </div>
</div>
