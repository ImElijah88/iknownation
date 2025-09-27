<?php
// The admin page requires the user to be an admin.
require_once 'components/session_manager.php';

// If the user is not an admin, redirect them to the main page.
if (!isAdmin()) {
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
            <h1 class="text-3xl font-bold mb-6">Admin: Template Creator</h1>

            <!-- Template Creation Form -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4" style="color: var(--primary-color-light);">Create New Template</h2>
                <form id="template-creator-form">
                    <div class="mb-4">
                        <label for="template_name" class="block mb-2 text-sm font-medium">Template Name</label>
                        <input type="text" id="template_name" name="template_name" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block mb-2 text-sm font-medium">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium">Features (for AI)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center"><input type="checkbox" name="features" value="has_quizzes" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500"> <span class="ml-2">Include Quizzes</span></label>
                            <label class="flex items-center"><input type="checkbox" name="features" value="professional_tone" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500"> <span class="ml-2">Professional Tone</span></label>
                            <label class="flex items-center"><input type="checkbox" name="features" value="use_emojis" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500"> <span class="ml-2">Use Emojis</span></label>
                            <label class="flex items-center"><input type="checkbox" name="features" value="image_placeholders" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500"> <span class="ml-2">Image Placeholders</span></label>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Generate & Save Template</button>
                </form>
                <div id="generation-status" class="mt-4 text-center"></div>
            </div>

            <!-- Existing Templates List -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold mb-4">Existing Templates</h2>
                <div id="existing-templates-list" class="space-y-4">
                    <!-- Templates will be loaded here by JavaScript -->
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Include JavaScript files -->
<script src="assets/js/main.js"></script>
<script src="assets/js/admin_templates.js"></script>

</body>
</html>
