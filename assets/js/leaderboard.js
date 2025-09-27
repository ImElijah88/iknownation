document.addEventListener('DOMContentLoaded', () => {
    const leaderboardBody = document.getElementById('leaderboard-body');

    async function fetchLeaderboard() {
        try {
            const response = await fetch('api/get_leaderboard.php');
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            const leaderboardData = await response.json();
            renderLeaderboard(leaderboardData);
        } catch (error) {
            console.error("Failed to fetch leaderboard:", error);
            leaderboardBody.innerHTML = '<tr><td colspan="3" class="text-center text-red-500">Error loading leaderboard.</td></tr>';
        }
    }

    function renderLeaderboard(leaderboardData) {
        leaderboardBody.innerHTML = '';
        if (leaderboardData.length === 0) {
            leaderboardBody.innerHTML = '<tr><td colspan="3" class="text-center text-gray-500">No data available.</td></tr>';
            return;
        }

        leaderboardData.forEach((user, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-4">${index + 1}</td>
                <td class="p-4">${user.email}</td>
                <td class="p-4">${user.total_xp}</td>
            `;
            leaderboardBody.appendChild(row);
        });
    }

    fetchLeaderboard();
});
