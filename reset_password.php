<?php require_once 'components/header.php'; ?>

<main class="w-full h-screen flex justify-center items-center">
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Reset Your Password</h2>
        
        <div id="reset-form-container">
            <form id="reset-password-form">
                <input type="hidden" id="reset-token" name="token" value="">
                
                <div class="mb-4">
                    <label for="new-password" class="block mb-2 text-sm font-medium">New Password</label>
                    <input type="password" id="new-password" name="new_password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" required>
                </div>

                <div class="mb-6">
                    <label for="confirm-password" class="block mb-2 text-sm font-medium">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" required>
                </div>

                <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Reset Password</button>
            </form>
        </div>

        <div id="reset-message" class="hidden text-center">
            <p></p>
            <a href="index.php" class="font-medium mt-4 inline-block" style="color: var(--primary-color-light);">Return to Home</a>
        </div>

    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const tokenField = document.getElementById('reset-token');
    const formContainer = document.getElementById('reset-form-container');
    const messageContainer = document.getElementById('reset-message');

    if (token) {
        tokenField.value = token;
    } else {
        formContainer.innerHTML = '<p class="text-center text-red-500">No reset token provided. Please check your link and try again.</p>';
    }

    const resetForm = document.getElementById('reset-password-form');
    resetForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (newPassword !== confirmPassword) {
            alert("Passwords do not match.");
            return;
        }

        const formData = new FormData(resetForm);
        const data = Object.fromEntries(formData.entries());
        data.action = 'reset_password';

        try {
            const response = await fetch('api/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            formContainer.classList.add('hidden');
            messageContainer.classList.remove('hidden');
            const p = messageContainer.querySelector('p');

            if (result.status === 'success') {
                p.textContent = result.message;
                p.className = 'text-green-500';
            } else {
                p.textContent = result.message;
                p.className = 'text-red-500';
            }

        } catch (error) {
            console.error("Error resetting password:", error);
            formContainer.classList.add('hidden');
            messageContainer.classList.remove('hidden');
            messageContainer.querySelector('p').textContent = 'An unexpected error occurred. Please try again.';
            messageContainer.querySelector('p').className = 'text-red-500';
        }
    });
});
</script>

</body>
</html>