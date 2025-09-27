/*
 * Now Nation - Common JavaScript Logic
 * ------------------------------------
 * This script contains shared JavaScript functions and logic used across multiple pages,
 * particularly for the sidemenu and user authentication UI.
 */

document.addEventListener("DOMContentLoaded", () => {
    // --- GLOBAL STATE (Shared) ---
    let allPresentations = {}; // Will hold all presentation data fetched from the API
    let sidemenuKeys = []; // Array of presentation keys currently in the sidemenu
    let currentUser = APP_USER; // User data from the global variable set in header.php

    // --- DOM ELEMENTS (Shared) ---
    const sidebarMenu = document.getElementById("sidebar-menu");
    const userAddControls = document.getElementById("user-add-controls");
    const authContainer = document.getElementById("auth-container");
    const addModal = document.getElementById('add-presentation-modal');
    const closeModalBtn = document.getElementById('close-add-modal');
    const selectionList = document.getElementById('presentation-selection-list');

    // --- STATE MANAGEMENT ---
    function loadSidemenuState() {
        const storageKey = `nownation_sidemenu_${currentUser ? currentUser.id : 'guest'}`;
        const savedKeys = localStorage.getItem(storageKey);
        const defaultKey = Object.keys(allPresentations).find(key => allPresentations[key].user_id === null);

        if (savedKeys) {
            sidemenuKeys = JSON.parse(savedKeys);
        } else {
            sidemenuKeys = defaultKey ? [defaultKey] : [];
        }
        // Ensure the default presentation is always included for logged-in users too
        if (defaultKey && !sidemenuKeys.includes(defaultKey)) {
            sidemenuKeys.unshift(defaultKey);
        }
    }

    function saveSidemenuState() {
        const storageKey = `nownation_sidemenu_${currentUser ? currentUser.id : 'guest'}`;
        localStorage.setItem(storageKey, JSON.stringify(sidemenuKeys));
    }

    // --- UI RENDERING (Shared) ---
    window.updateUIForUser = function() {
        // Auth buttons in header
        if (currentUser) {
            authContainer.innerHTML = `
                <div class="flex items-center space-x-2">
                     <a href="my_profile.php" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color)">My Profile</a>
                    <a href="logout.php" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color-hover)">Logout</a>
                </div>
            `;
            // '+' button in sidebar
            userAddControls.innerHTML = `
                <button id="add-presentation-btn" class="w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <span class="font-semibold ml-3">Add Presentation</span>
                </button>
            `;
            document.getElementById('add-presentation-btn').addEventListener('click', openAddModal);
        } else {
            authContainer.innerHTML = `
                <button id="login-btn" class="px-4 py-2 font-semibold text-white rounded-lg" style="background-color: var(--primary-color)">Login</button>
            `;
            document.getElementById('login-btn').addEventListener('click', () => window.openModal('login-modal'));
        }
    }

    window.renderSidemenu = function(activePresentationId = null) {
        sidebarMenu.innerHTML = '';
        sidemenuKeys.forEach(key => {
            if (allPresentations[key]) {
                const link = createSidemenuLink(allPresentations[key], activePresentationId);
                sidebarMenu.appendChild(link);
            }
        });
    }

    function createSidemenuLink(p, activePresentationId) {
        const link = document.createElement('a');
        link.href = "#";
        link.dataset.presentation = p.presentation_key;
        link.className = `sidebar-item w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10 cursor-pointer ${p.presentation_key === activePresentationId ? 'active bg-black/10 dark:bg-white/10' : ''}`;
        link.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a2 2 0 00-2 2v4a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2z" /></svg>
            <div class="sidebar-text-container flex-grow ml-3">
              <span class="font-semibold">${p.title}</span>
            </div>
            <div class="sidebar-actions flex items-center space-x-1 ml-auto">
                <button title="Download Speech" class="download-speech-btn p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg></button>
                ${currentUser ? `<button title="Remove" class="remove-btn p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>` : ''}
            </div>
        `;
        link.addEventListener('click', (e) => {
            if (e.target.closest('.download-speech-btn')) {
                e.stopPropagation();
                downloadSpeech(p.presentation_key);
            } else if (e.target.closest('.remove-btn')) {
                e.stopPropagation();
                removePresentationFromSidemenu(p.presentation_key);
            } else {
                e.preventDefault();
                // This part will be handled by the page-specific script (e.g., nownation.js)
                // For now, we just ensure the active class is set
                document.querySelectorAll(".sidebar-item").forEach(item => {
                    item.classList.toggle("active", item.dataset.presentation === p.presentation_key);
                });
            }
        });
        return link;
    }

    // --- MODAL LOGIC ---
    function openAddModal() {
        selectionList.innerHTML = '';
        const availablePresentations = Object.values(allPresentations).filter(p => 
            p.status === 'saved' && 
            !sidemenuKeys.includes(p.presentation_key) && 
            (p.user_id === currentUser.id || p.user_id === null)
        );
        
        if (availablePresentations.length === 0) {
            selectionList.innerHTML = '<p class="text-center text-gray-500">No other presentations available to add.</p>';
        } else {
            availablePresentations.forEach(p => {
                const item = document.createElement('div');
                item.className = 'p-3 rounded-lg hover:bg-black/10 dark:hover:bg-white/10 flex justify-between items-center';
                item.innerHTML = `<span>${p.title}</span><button data-key="${p.presentation_key}" class="add-btn px-3 py-1 text-sm text-white rounded" style="background-color: var(--primary-color)">Add</button>`;
                selectionList.appendChild(item);
            });
        }
        addModal.classList.remove('hidden');
    }

    selectionList.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-btn')) {
            const key = e.target.dataset.key;
            sidemenuKeys.push(key);
            saveSidemenuState();
            window.renderSidemenu(); // Re-render sidemenu after adding
            e.target.closest('.p-3').remove(); // Remove from modal list
            if (selectionList.children.length === 0) {
                 selectionList.innerHTML = '<p class="text-center text-gray-500">No other presentations available to add.</p>';
            }
        }
    });

    closeModalBtn.addEventListener('click', () => addModal.classList.add('hidden'));

    // --- PRESENTATION LOGIC (Shared) ---
    window.removePresentationFromSidemenu = function(key) {
        sidemenuKeys = sidemenuKeys.filter(k => k !== key);
        saveSidemenuState();
        window.renderSidemenu();
        // If the removed presentation was active, switch to the first one
        if (window.activePresentationId === key) {
            const firstKey = sidemenuKeys[0];
            if (firstKey) {
                // This will trigger a page reload for nownation.php
                window.location.href = `nownation.php?presentation=${firstKey}`;
            } else {
                // If no presentations left, redirect to a default empty state or login
                window.location.href = `nownation.php`;
            }
        }
    }

    window.downloadSpeech = function(key) {
        const presentation = allPresentations[key];
        if (!presentation || !presentation.speech) {
            console.error('Speech content not found for key:', key);
            return;
        }
        const blob = new Blob([presentation.speech], { type: 'text/plain' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${presentation.title.replace(/\s+/g, '_')}_Speech.txt`;
        link.click();
        URL.revokeObjectURL(link.href);
    }

    // --- INITIAL FETCH & RENDER ---
    async function initialFetchAndRender() {
        try {
            const response = await fetch('api/get_presentations.php');
            if (!response.ok) throw new Error(`API Error: ${response.status}`);
            allPresentations = await response.json();
            
            loadSidemenuState();
            window.updateUIForUser();
            window.renderSidemenu();

        } catch (error) {
            console.error("Initial fetch failed:", error);
            // Handle error gracefully, e.g., show a message
        }
    }

    initialFetchAndRender();
});
