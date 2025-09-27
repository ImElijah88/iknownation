/*
 * Now Nation - Main Page Logic
 * ------------------------------
 * This script controls all the dynamic functionality on nownation.php.
 */

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
}

document.addEventListener("DOMContentLoaded", () => {
    // --- GLOBAL STATE ---
    let allPresentations = {};
    let sidemenuKeys = []; // Array of presentation keys currently in the sidemenu
    let activePresentationId = null;
    const currentUser = APP_USER;
    const storageKey = `nownation_sidemenu_${currentUser ? currentUser.id : 'guest'}`;

    let currentPage = 1;
    const limit = 10;
    let totalPresentations = 0;

    // --- DOM ELEMENTS ---
    const sidebarMenu = document.getElementById("sidebar-menu");
    const userAddControls = document.getElementById("user-add-controls");
    const presentationWrapper = document.getElementById("presentation-wrapper");
    const authContainer = document.getElementById("auth-container");
    const addModal = document.getElementById('add-presentation-modal');
    const closeModalBtn = document.getElementById('close-add-modal');
    const selectionList = document.getElementById('presentation-selection-list');
    const sidebar = document.getElementById('sidebar');
    const hoverTrigger = document.getElementById('sidebar-hover-trigger');

    const searchInput = document.getElementById('search-presentations');

    // --- SIDEBAR HOVER LOGIC ---
    if (hoverTrigger && sidebar) {
        hoverTrigger.addEventListener("mouseenter", () => sidebar.classList.add("open"));
        sidebar.addEventListener("mouseleave", () => sidebar.classList.remove("open"));
    }

    function filterAndRenderSidemenu() {
        const searchTerm = searchInput.value.toLowerCase();
        if (searchTerm === '') {
            renderSidemenu();
            return;
        }
        const filteredKeys = sidemenuKeys.filter(key => {
            const presentation = allPresentations[key];
            return presentation && presentation.title.toLowerCase().includes(searchTerm);
        });
        renderSidemenu(filteredKeys);
    }

    function renderPagination() {
        const paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = '';
        const totalPages = Math.ceil(totalPresentations / limit);

        if (totalPages <= 1) {
            return;
        }

        for (let i = 1; i <= totalPages; i++) {
            const pageLink = document.createElement('button');
            pageLink.textContent = i;
            pageLink.className = `px-3 py-1 mx-1 rounded-lg ${i === currentPage ? 'bg-cyan-500 text-white' : 'bg-black/10 dark:bg-white/10'}`;
            pageLink.addEventListener('click', () => {
                currentPage = i;
                initialize();
            });
            paginationControls.appendChild(pageLink);
        }
    }

    // --- INITIALIZATION ---
    async function initialize() {
        try {
            const response = await fetch(`api/get_presentations.php?page=${currentPage}&limit=${limit}`);
            if (!response.ok) throw new Error(`API Error: ${response.status}`);
            const data = await response.json();
            allPresentations = data.presentations;
            totalPresentations = data.total_presentations;
            
            loadSidemenuState();
            updateUIForUser();
            renderSidemenu();
            renderPagination();

            searchInput.addEventListener('input', filterAndRenderSidemenu);

            const urlParams = new URLSearchParams(window.location.search);
            const presentationKeyFromUrl = urlParams.get('presentation');

            if (presentationKeyFromUrl && allPresentations[presentationKeyFromUrl]) {
                switchPresentation(presentationKeyFromUrl);
            } else if (sidemenuKeys.length > 0) {
                switchPresentation(sidemenuKeys[0]);
            }

        } catch (error) {
            console.error("Initialization failed:", error);
            presentationWrapper.innerHTML = `<div class="text-red-500">Error loading presentations.</div>`;
        }
    }

    // --- STATE MANAGEMENT ---
    function loadSidemenuState() {
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
        localStorage.setItem(storageKey, JSON.stringify(sidemenuKeys));
    }

    // --- UI RENDERING ---
    function updateUIForUser() {
        // Auth buttons in header
        if (currentUser) {
            authContainer.innerHTML = `
                <div class="flex items-center space-x-2">
                    <a href="logout.php" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color-hover)">Logout</a>
                </div>
            `;
            // '+' button in sidebar
            userAddControls.innerHTML = `
                <a href="my_profile.php" class="w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span class="font-semibold ml-3">My Profile</span>
                </a>
                <a href="leaderboard.php" class="w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2z" /></svg>
                    <span class="font-semibold ml-3">Leaderboard</span>
                </a>
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

    function renderSidemenu(keysToRender = sidemenuKeys) {
        sidebarMenu.innerHTML = '';
        keysToRender.forEach(key => {
            if (allPresentations[key]) {
                const link = createSidemenuLink(allPresentations[key]);
                sidebarMenu.appendChild(link);
            }
        });
        // Highlight the active presentation
        document.querySelectorAll(".sidebar-item").forEach(item => {
            item.classList.toggle("active", item.dataset.presentation === activePresentationId);
        });
    }

    function createSidemenuLink(p) {
        const link = document.createElement('a');
        link.href = "#";
        link.dataset.presentation = p.presentation_key;
        link.className = "sidebar-item w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10 cursor-pointer";
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
                switchPresentation(p.presentation_key);
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
            renderSidemenu();
            e.target.closest('.p-3').remove(); // Remove from modal list
            if (selectionList.children.length === 0) {
                 selectionList.innerHTML = '<p class="text-center text-gray-500">No other presentations available to add.</p>';
            }
        }
    });

    closeModalBtn.addEventListener('click', () => addModal.classList.add('hidden'));

    // --- PRESENTATION LOGIC ---
    function removePresentationFromSidemenu(key) {
        sidemenuKeys = sidemenuKeys.filter(k => k !== key);
        saveSidemenuState();
        renderSidemenu();
        if (activePresentationId === key) {
            switchPresentation(sidemenuKeys[0] || null);
        }
    }

    function downloadSpeech(key) {
        const presentation = allPresentations[key];
        const blob = new Blob([presentation.speech], { type: 'text/plain' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${presentation.title.replace(/\s+/g, '_')}_Speech.txt`;
        link.click();
        URL.revokeObjectURL(link.href);
    }

    function switchPresentation(presentationId) {
        const exportControls = document.getElementById('export-controls');
        if (!presentationId || !allPresentations[presentationId]) {
            presentationWrapper.innerHTML = '';
            exportControls.innerHTML = '';
            return;
        }
        activePresentationId = presentationId;
        const presentationData = allPresentations[presentationId];
        document.querySelectorAll(".sidebar-item").forEach(item => {
            item.classList.toggle("active", item.dataset.presentation === presentationId);
        });
        const colors = presentationData.colors || { primary: '#06b6d4', light: '#67e8f9', hover: '#0891b2' };
        document.documentElement.style.setProperty("--primary-color", colors.primary);
        document.documentElement.style.setProperty("--primary-color-light", colors.light);
        document.documentElement.style.setProperty("--primary-color-hover", colors.hover);

        // Populate export controls
        exportControls.innerHTML = `
            <div class="flex justify-around">
                <a href="api/export.php?presentation_id=${presentationData.id}&format=html" target="_blank" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color)">Export HTML</a>
                <a href="api/export.php?presentation_id=${presentationData.id}&format=txt" target="_blank" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color)">Export TXT</a>
            </div>
        `;

        renderPresentation(presentationData);
    }

    function renderPresentation(presentationData) {
        presentationWrapper.innerHTML = `
            <div class="w-full max-w-4xl mx-auto bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden">
                <div class="presentation-header p-6 border-b border-black/10 dark:border-white/10 hidden">
                    <h1 class="text-2xl font-bold" style="color: var(--primary-color-light);">${presentationData.title}</h1>
                </div>
                <div class="slides-container p-8 md:p-12 min-h-[50vh] flex items-center">
                    ${presentationData.slides.map(slide => slide.html).join('')}
                </div>
                <div class="presentation-nav p-6 border-t border-black/10 dark:border-white/10 flex justify-between items-center hidden">
                    <button class="prev-btn bg-black/10 dark:bg-white/10 hover:bg-black/20 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold py-2 px-4 rounded-lg transition-all disabled:opacity-50" disabled>Previous</button>
                    <div class="slide-counter text-sm text-gray-500 dark:text-gray-400"></div>
                    <button class="next-btn text-white font-bold py-2 px-4 rounded-lg transition-all" style="background-color: var(--primary-color);">Next</button>
                </div>
            </div>
        `;
        runPresentationLogic(presentationData);
    }

    function runPresentationLogic(presentationData) {
        let currentSlide = 0;
        let xpScore = 0;
        const slideXP = 10;
        const quizXP = 50;

        const slides = presentationWrapper.querySelectorAll(".slide");
        const totalSlides = slides.length;
        const contentSlidesCount = totalSlides > 1 ? totalSlides - 1 : 1;

        const startBtn = presentationWrapper.querySelector(".start-btn");
        const nextBtn = presentationWrapper.querySelector(".next-btn");
        const prevBtn = presentationWrapper.querySelector(".prev-btn");
        const restartBtn = presentationWrapper.querySelector(".restart-btn");
        const headerEl = presentationWrapper.querySelector(".presentation-header");
        const navEl = presentationWrapper.querySelector(".presentation-nav");
        const progressBar = presentationWrapper.querySelector(".progress-bar");
        const xpScoreEl = presentationWrapper.querySelector(".xp-score");
        const slideCounterEl = presentationWrapper.querySelector(".slide-counter");
        const finalXpScoreEl = presentationWrapper.querySelector(".final-xp-score");

        const quizModal = document.getElementById("quiz-modal");
        const quizQuestionEl = document.getElementById("quiz-question");
        const quizOptionsEl = document.getElementById("quiz-options");
        const quizFeedbackEl = document.getElementById("quiz-feedback");

        const debouncedSaveProgress = debounce(saveProgress, 1000);

        async function saveProgress(presentationKey, slide, xp) {
            if (!currentUser) return;

            try {
                await fetch('api/save_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        presentation_key: presentationKey,
                        current_slide: slide,
                        xp: xp
                    })
                });
            } catch (error) {
                console.error("Failed to save progress:", error);
            }
        }

        function updateSlide() {
            const isStarted = currentSlide > 0;
            headerEl.classList.toggle("hidden", !isStarted);
            navEl.classList.toggle("hidden", !isStarted || totalSlides <= 1);

            slides.forEach((slide, index) => slide.classList.toggle("active", index === currentSlide));

            if (navEl) {
                prevBtn.disabled = currentSlide <= 0;
                nextBtn.disabled = currentSlide >= totalSlides - 1;
                slideCounterEl.textContent = `Slide ${currentSlide + 1} of ${totalSlides}`;
            }

            if (finalXpScoreEl && currentSlide === totalSlides - 1) {
              finalXpScoreEl.textContent = xpScore;
            }
            if(xpScoreEl) xpScoreEl.textContent = xpScore;

            debouncedSaveProgress(activePresentationId, currentSlide, xpScore);
        }

        function showQuiz(quiz) {
            quizQuestionEl.textContent = quiz.question;
            quizOptionsEl.innerHTML = "";
            quizFeedbackEl.innerHTML = "";

            quiz.options.forEach((optionText) => {
              const button = document.createElement("button");
              button.textContent = optionText;
              button.className = "w-full text-left bg-black/10 dark:bg-white/10 hover:bg-opacity-20 p-4 rounded-lg transition-all";
              button.onclick = () => {
                const buttons = quizOptionsEl.querySelectorAll("button");
                buttons.forEach((btn) => {
                  btn.disabled = true;
                  if (btn.textContent === quiz.correctAnswer) btn.classList.add("bg-green-500/50");
                  else if (btn.textContent === optionText) btn.classList.add("bg-red-500/50");
                });
                if (optionText === quiz.correctAnswer) {
                  xpScore += quizXP;
                  if(xpScoreEl) xpScoreEl.textContent = xpScore;
                  quizFeedbackEl.textContent = `Correct! +${quizXP} XP`;
                  quizFeedbackEl.className = "mt-6 text-center font-semibold text-green-500";
                } else {
                  quizFeedbackEl.textContent = `Not quite. The correct answer was \"${quiz.correctAnswer}\".`;
                  quizFeedbackEl.className = "mt-6 text-center font-semibold text-red-500";
                }
                setTimeout(() => quizModal.classList.add("hidden"), 2000);
              };
              quizOptionsEl.appendChild(button);
            });
            quizModal.classList.remove("hidden");
        }

        if (startBtn) startBtn.addEventListener("click", () => { currentSlide++; updateSlide(); });
        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
              if (currentSlide < totalSlides - 1) {
                if (currentSlide > 0) xpScore += slideXP;
                currentSlide++;
                updateSlide();
                const quizToShow = presentationData.quizzes.find(q => q.slideAfter === currentSlide - 1);
                if (quizToShow) showQuiz(quizToShow);
              }
            });
        }
        if (prevBtn) prevBtn.addEventListener("click", () => { if (currentSlide > 0) { currentSlide--; updateSlide(); } });
        if (restartBtn) restartBtn.addEventListener("click", () => { currentSlide = 0; xpScore = 0; updateSlide(); });

        updateSlide();
    }

    initialize();
});
