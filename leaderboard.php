<?php
require_once 'components/header.php';
?>

<div class="flex h-screen">
    <?php
    require_once 'components/sidemenu.php';
    ?>

    <!-- Main Content Area -->
    <main class="flex-1 p-6 pt-20 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Leaderboard</h1>

            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg p-8">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-4">Rank</th>
                            <th class="p-4">User</th>
                            <th class="p-4">Total XP</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboard-body">
                        <!-- Leaderboard data will be loaded here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Include JavaScript files -->
<script src="assets/js/main.js"></script>
<script src="assets/js/leaderboard.js"></script>

</body>
</html>
