<?php
// The header component requires the session manager to determine user status.
require_once 'components/session_manager.php';
?>
<!DOCTYPE html>
<!-- The 'dark' class is managed by main.js based on localStorage -->
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Now Nation - Interactive Presentations</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css" />

    <script>
        // This global variable is the bridge between PHP's session and our JavaScript.
        // It allows the frontend to know who the user is without making an extra API call.
        const APP_USER = <?php echo json_encode(getCurrentUser()); ?>;
    </script>
</head>
<body class="text-gray-800 dark:text-gray-200">

    <!-- Top Right Controls -->
    <div class="absolute top-6 right-6 flex items-center space-x-4 z-10" id="user-controls">
        <!-- Theme Toggle Button -->
        <button id="theme-toggle" class="p-2 rounded-lg bg-white/30 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-600/50 backdrop-blur-sm transition-colors">
            <svg id="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg id="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
        
        <!-- Auth container to be filled by JavaScript based on APP_USER -->
        <div id="auth-container"></div>
    </div>

    <!-- MODALS -->

    <!-- Login/Register Modal -->
    <div id="login-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-sm w-full relative">
            <button id="close-login-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <!-- Login View -->
            <div id="login-view">
                <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
                <form id="login-form">
                    <div class="mb-4">
                        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg pr-10" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 dark:text-gray-400 toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-open"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-closed hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="remember-me" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Remember me</span>
                        </label>
                        <a href="#" id="forgot-password-link" class="text-sm font-medium" style="color: var(--primary-color-light);">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Login</button>
                    <p class="mt-4 text-center text-sm">Don't have an account? <a href="#" id="show-register-link" class="font-medium" style="color: var(--primary-color-light);">Register</a></p>
                </form>
            </div>

            <!-- Register View -->
            <div id="register-view" class="hidden">
                <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>
                <form id="register-form">
                    <div class="mb-4">
                        <label for="register-email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" id="register-email" name="email" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label for="register-password" class="block mb-2 text-sm font-medium">Password</label>
                        <input type="password" id="register-password" name="password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="8+ characters" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Register</button>
                    <p class="mt-4 text-center text-sm">Already have an account? <a href="#" id="show-login-link" class="font-medium" style="color: var(--primary-color-light);">Login</a></p>
                </form>
            </div>

            <!-- Forgot Password View -->
            <div id="forgot-password-view" class="hidden">
                <h2 class="text-2xl font-bold mb-4 text-center">Recover Password</h2>
                <p class="text-center text-sm mb-6 text-gray-600 dark:text-gray-300">Enter your email to get a recovery link.</p>
                 <form id="forgot-password-form">
                    <div class="mb-6">
                        <label for="recover-email" class="block mb-2 text-sm font-medium">Email</label>
                        <input type="email" id="recover-email" name="email" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="you@example.com" required>
                    </div>
                    <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">Send Recovery Link</button>
                    <p class="mt-4 text-center text-sm">
                        <a href="#" id="back-to-login-link" class="font-medium" style="color: var(--primary-color-light);"> &larr; Back to Login </a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <!-- Content will be added in a future step -->
    </div>

    <!-- Notification Element -->
    <div id="notification"></div>
