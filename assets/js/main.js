/*
 * Now Nation - Global JavaScript
 * --------------------------------
 * This script handles functionality common to all pages, including
 * theme management, modal interactions, and user notifications.
 */

document.addEventListener("DOMContentLoaded", () => {
    // --- GLOBAL ELEMENTS ---
    const themeToggleBtn = document.getElementById("theme-toggle");
    const sunIcon = document.getElementById("theme-icon-sun");
    const moonIcon = document.getElementById("theme-icon-moon");
    const notificationEl = document.getElementById('notification');

    // --- MODAL ELEMENTS ---
    const loginModal = document.getElementById("login-modal");
    const profileModal = document.getElementById("profile-modal");
    const closeLoginModalBtn = document.getElementById("close-login-modal");
    const closeProfileModalBtn = document.getElementById("close-profile-modal");

    const loginView = document.getElementById('login-view');
    const registerView = document.getElementById('register-view');
    const forgotPasswordView = document.getElementById('forgot-password-view');

    const showRegisterLink = document.getElementById('show-register-link');
    const showLoginLink = document.getElementById('show-login-link');
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const backToLoginLink = document.getElementById('back-to-login-link');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');

    // --- 1. THEME MANAGEMENT ---
    
    /**
     * Updates the sun/moon icons based on the current theme.
     */
    function updateThemeIcons() {
        if (document.documentElement.classList.contains("dark")) {
            moonIcon.classList.add("hidden");
            sunIcon.classList.remove("hidden");
        } else {
            moonIcon.classList.remove("hidden");
            sunIcon.classList.add("hidden");
        }
    }

    // Set theme on initial load
    if (localStorage.getItem("theme") === "light" || (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: light)").matches)) {
        document.documentElement.classList.remove("dark");
    } else {
        document.documentElement.classList.add("dark");
    }
    // Call once on load
    updateThemeIcons();

    // Add click listener for the theme toggle button
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", () => {
            document.documentElement.classList.toggle("dark");
            localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
            updateThemeIcons();
        });
    }

    // --- 2. NOTIFICATION SYSTEM ---

    /**
     * Displays a notification message to the user.
     * @param {string} message The message to display.
     * @param {string} type The type of notification ('success' or 'error').
     */
    window.showNotification = function(message, type = 'success') {
        if (!notificationEl) return;
        notificationEl.textContent = message;
        notificationEl.className = ''; // Clear previous classes
        notificationEl.classList.add(type, 'show');
        setTimeout(() => {
            notificationEl.classList.remove('show');
        }, 3000);
    }

    // --- 3. MODAL MANAGEMENT ---

    // Function to open a modal
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.classList.remove('hidden');
            if (modalId === 'profile-modal') {
                fetchAndPopulateProfileModal();
            }
        }
    }

    async function fetchAndPopulateProfileModal() {
        const profileTotalXpEl = document.getElementById('profile-total-xp');
        const profileBadgesEl = document.getElementById('profile-badges');

        if (!profileTotalXpEl || !profileBadgesEl) return;

        profileTotalXpEl.textContent = 'Loading...';
        profileBadgesEl.innerHTML = '';

        try {
            const response = await fetch('api/get_user_data.php');
            if (!response.ok) throw new Error(`API Error: ${response.status}`);
            const data = await response.json();

            if (data.status === 'success') {
                profileTotalXpEl.textContent = data.user.total_xp;
                if (data.user.badges && data.user.badges.length > 0) {
                    profileBadgesEl.innerHTML = data.user.badges.map(badge => `<span class="badge">${badge}</span>`).join('');
                } else {
                    profileBadgesEl.textContent = 'No badges yet.';
                }
            } else {
                profileTotalXpEl.textContent = 'Error loading data.';
                window.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            profileTotalXpEl.textContent = 'Error loading data.';
            window.showNotification('An error occurred while fetching profile data.', 'error');
        }
    }

    // Function to close a modal
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.add('hidden');
    }

    // Generic click handlers for modal controls
    if (closeLoginModalBtn) closeLoginModalBtn.addEventListener("click", () => closeModal('login-modal'));
    if (closeProfileModalBtn) closeProfileModalBtn.addEventListener("click", () => closeModal('profile-modal'));

    // Close modal if clicking on the background overlay
    if (loginModal) loginModal.addEventListener("click", (e) => {
        if (e.target === loginModal) closeModal('login-modal');
    });
    if (profileModal) profileModal.addEventListener("click", (e) => {
        if (e.target === profileModal) closeModal('profile-modal');
    });

    // Handlers for switching between login/register/forgot views
    if (showRegisterLink) showRegisterLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginView.classList.add('hidden');
        forgotPasswordView.classList.add('hidden');
        registerView.classList.remove('hidden');
    });

    if (showLoginLink) showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        registerView.classList.add('hidden');
        forgotPasswordView.classList.add('hidden');
        loginView.classList.remove('hidden');
    });

    if (forgotPasswordLink) forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginView.classList.add('hidden');
        registerView.classList.add('hidden');
        forgotPasswordView.classList.remove('hidden');
    });

    if (backToLoginLink) backToLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        forgotPasswordView.classList.add('hidden');
        registerView.classList.add('hidden');
        loginView.classList.remove('hidden');
    });

    // Handler for password visibility toggles
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const passwordInput = btn.previousElementSibling;
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        });
    });

    // --- 4. AUTHENTICATION FORM HANDLING ---
    // --- 4. AUTHENTICATION FORM HANDLING ---
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const forgotPasswordForm = document.getElementById('forgot-password-form');

    if(loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const data = {
                action: 'login',
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                window.showNotification(response.message, response.status);
                if (response.status === 'success') {
                    // Reload the page to update the user state everywhere
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                window.showNotification('An error occurred. Check the console.', 'error');
            });
        });
    }

    if(registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const data = {
                action: 'register',
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                window.showNotification(response.message, response.status);
                if (response.status === 'success') {
                    registerForm.reset();
                    // Switch to the login view
                    document.getElementById('register-view').classList.add('hidden');
                    document.getElementById('login-view').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                window.showNotification('An error occurred. Check the console.', 'error');
            });
        });
    }

    if(forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // This part is front-end only for now, as the backend isn't set up for it.
            window.showNotification('Password recovery is not yet implemented.', 'error');
        });
    }
});
