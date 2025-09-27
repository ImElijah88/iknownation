<?php
// The profile page requires a user to be logged in.
require_once 'components/session_manager.php';

// If the user is not logged in, redirect them to the main page.
if (!isLoggedIn()) {
    header("Location: nownation.php");
    exit;
}

// Include the standard header.
require_once 'components/header.php';
?>

<div class="flex h-screen">
    <?php
    // Include the standard sidemenu.
    require_once 'components/sidemenu.php';
    ?>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 pt-20 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">My Profile & Content</h1>

            <!-- AI Presentation Generation -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4" style="color: var(--primary-color-light);">Generate New Presentation</h2>
                <form id="generate-form">
                    <div class="mb-4">
                        <label for="upload-file" class="block mb-2 text-sm font-medium">Upload Document (.txt, .pdf, .doc)</label>
                        <input type="file" id="upload-file" name="document" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="template-select" class="block mb-2 text-sm font-medium">Select Template</label>
                        <select id="template-select" name="template" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg">
                            <!-- Options will be loaded by JavaScript -->
                        </select>
                    </div>
                    <div id="dynamic-template-fields" class="mb-4 p-4 border border-gray-300/50 dark:border-gray-600/50 rounded-lg hidden">
                        <h3 class="text-lg font-bold mb-2" style="color: var(--primary-color-light);">Template Options</h3>
                        <!-- Dynamic fields will be rendered here -->
                    </div>
                    <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Generate</button>
                </form>
                <div id="generation-status" class="mt-4 text-center"></div>
            </div>

            <!-- User Content Vault -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-4">My Content Vault</h2>
                <div id="my-presentations-list" class="space-y-4">
                    <!-- User's presentations will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Include JavaScript files -->
<script src="assets/js/main.js"></script>
<script src="assets/js/my_profile.js"></script>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-sm w-full text-center">
        <h2 id="confirm-title" class="text-xl font-bold mb-4">Are you sure?</h2>
        <p id="confirm-message" class="mb-6">This action cannot be undone.</p>
        <div class="flex justify-center space-x-4">
            <button id="confirm-btn-no" class="px-6 py-2 font-semibold bg-black/10 dark:bg-white/10 hover:bg-black/20 dark:hover:bg-white/20 rounded-lg">No</button>
            <button id="confirm-btn-yes" class="px-6 py-2 font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg">Yes</button>
        </div>
    </div>
</div>

</body>
</html>
