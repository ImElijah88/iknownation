/*
 * Now Nation - My Profile Page Logic
 * -------------------------------------
 * This script handles all dynamic functionality on my_profile.php.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- STATE & ELEMENTS ---
    const currentUser = APP_USER;
    const presentationsList = document.getElementById('my-presentations-list');
    const templateSelect = document.getElementById('template-select');
    const dynamicTemplateFields = document.getElementById('dynamic-template-fields');
    const generationForm = document.getElementById('generate-form');
    const generationStatus = document.getElementById('generation-status');

    const confirmModal = document.getElementById('confirmation-modal');
    const confirmBtnYes = document.getElementById('confirm-btn-yes');
    const confirmBtnNo = document.getElementById('confirm-btn-no');
    let presentationToDelete = null; // Variable to hold the state

    let allTemplates = []; // Store all fetched templates

    // --- INITIALIZATION ---

    /**
     * Main initialization function.
     */
    async function initializeProfile() {
        if (!currentUser) {
            presentationsList.innerHTML = '<p>You must be logged in to see your content.</p>';
            return;
        }
        await loadUserPresentations();
        await loadTemplates();
        
        // Setup modal listeners
        confirmBtnNo.addEventListener('click', () => {
            confirmModal.classList.add('hidden');
            presentationToDelete = null;
        });

        confirmBtnYes.addEventListener('click', () => {
            if (presentationToDelete) {
                proceedWithDeletion(presentationToDelete.id, presentationToDelete.element);
            }
            confirmModal.classList.add('hidden');
            presentationToDelete = null;
        });

        // Event listener for template selection change
        templateSelect.addEventListener('change', renderDynamicTemplateFields);
    }

    // --- DATA FETCHING ---

    /**
     * Fetches and displays the presentations created by the current user.
     */
    async function loadUserPresentations() {
        try {
            const response = await fetch(`api/get_presentations.php?user_id=${currentUser.id}`);
            if (!response.ok) throw new Error('Failed to fetch presentations.');
            
            const allPresentations = await response.json();
            const userPresentations = Object.values(allPresentations).filter(p => p.user_id == currentUser.id);

            presentationsList.innerHTML = ''; // Clear loader/previous content
            if (userPresentations.length === 0) {
                presentationsList.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400">You have not created any presentations yet.</p>';
                return;
            }

            userPresentations.forEach(p => {
                const presentationElement = createPresentationElement(p);
                presentationsList.appendChild(presentationElement);
            });

        } catch (error) {
            console.error('Error loading presentations:', error);
            presentationsList.innerHTML = '<p class="text-red-500">Could not load your presentations.</p>';
        }
    }

    /**
     * Fetches all available templates and populates the dropdown.
     */
    async function loadTemplates() {
        try {
            const response = await fetch('api/get_all_templates.php');
            if (!response.ok) throw new Error('Failed to fetch templates.');
            const result = await response.json();

            if (result.status === 'success') {
                allTemplates = result.templates;
                templateSelect.innerHTML = ''; // Clear existing options
                allTemplates.forEach(template => {
                    const option = document.createElement('option');
                    option.value = template.id;
                    option.textContent = template.template_name;
                    templateSelect.appendChild(option);
                });
                // Trigger change to load dynamic fields for the first template
                templateSelect.dispatchEvent(new Event('change'));
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            showNotification('Could not load templates.', 'error');
        }
    }

    // --- DYNAMIC FORM RENDERING ---

    /**
     * Renders dynamic input fields based on the selected template's features.
     */
    function renderDynamicTemplateFields() {
        const selectedTemplateId = templateSelect.value;
        const selectedTemplate = allTemplates.find(t => t.id == selectedTemplateId);

        dynamicTemplateFields.innerHTML = ''; // Clear previous fields
        dynamicTemplateFields.classList.add('hidden');

        if (selectedTemplate && selectedTemplate.features) {
            let hasDynamicFields = false;
            const features = selectedTemplate.features;

            // Example: Quizzes
            if (features.has_quizzes) {
                hasDynamicFields = true;
                dynamicTemplateFields.innerHTML += `
                    <div class="mb-4">
                        <label for="num_quizzes" class="block mb-2 text-sm font-medium">Number of Quizzes (max ${features.max_quizzes || 3})</label>
                        <input type="number" id="num_quizzes" name="num_quizzes" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" min="0" max="${features.max_quizzes || 3}" value="1">
                    </div>
                `;
            }

            // Example: Tone
            if (features.style) {
                let styleString = typeof features.style === 'string' ? features.style : JSON.stringify(features.style);
                if (styleString.includes('tone')) {
                    hasDynamicFields = true;
                    dynamicTemplateFields.innerHTML += `
                        <div class="mb-4">
                            <label for="tone" class="block mb-2 text-sm font-medium">Presentation Tone</label>
                            <select id="tone" name="tone" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg">
                                <option value="professional">Professional</option>
                                <option value="casual">Casual</option>
                                <option value="humorous">Humorous</option>
                            </select>
                        </div>
                    `;
                }
            }

            // Example: Key Points
            if (features.allow_key_points) {
                hasDynamicFields = true;
                dynamicTemplateFields.innerHTML += `
                    <div class="mb-4">
                        <label for="key_points" class="block mb-2 text-sm font-medium">Key Points to Emphasize (comma-separated)</label>
                        <textarea id="key_points" name="key_points" rows="2" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg"></textarea>
                    </div>
                `;
            }

            if (hasDynamicFields) {
                dynamicTemplateFields.classList.remove('hidden');
            }
        }
    }

    // --- UI RENDERING ---

    /**
     * Creates an HTML element for a single presentation in the vault.
     * @param {object} presentationData The presentation data.
     * @returns {HTMLElement} A div element representing the presentation.
     */
    function createPresentationElement(presentationData) {
        const div = document.createElement('div');
        div.className = 'bg-black/5 dark:bg-white/5 p-4 rounded-lg flex items-center justify-between';
        div.setAttribute('data-presentation-id', presentationData.id);

        const isSaved = presentationData.status === 'saved';

        div.innerHTML = `
            <div>
                <h3 class="font-bold text-lg">${presentationData.title}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status: <span class="font-semibold status-text">${isSaved ? 'Saved' : 'Temporary'}</span></p>
            </div>
            <div class="flex items-center space-x-2">
                <button data-key="${presentationData.presentation_key}" class="view-btn px-3 py-1 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color);">View</button>
                <button data-id="${presentationData.id}" class="save-btn px-3 py-1 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 rounded-lg ${isSaved ? 'hidden' : ''}">Save</button>
                <!-- Export Dropdown -->
                <div class="relative inline-block text-left">
                    <button type="button" class="export-toggle-btn inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-3 py-1 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none">
                        Export
                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 hidden z-10">
                        <div class="py-1" role="menu" aria-orientation="vertical">
                            <a href="api/export.php?presentation_id=${presentationData.id}&format=html" class="text-gray-700 dark:text-gray-200 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem" target="_blank">Export as HTML</a>
                            <a href="api/export.php?presentation_id=${presentationData.id}&format=txt" class="text-gray-700 dark:text-gray-200 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem" target="_blank">Export as TXT</a>
                        </div>
                    </div>
                </div>
                <button data-id="${presentationData.id}" class="delete-btn px-3 py-1 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg">Delete</button>
            </div>
        `;

        // Add event listeners
        div.querySelector('.view-btn').addEventListener('click', handleViewPresentation);
        div.querySelector('.save-btn').addEventListener('click', handleSavePresentation);
        div.querySelector('.delete-btn').addEventListener('click', handleDeletePresentation);

        return div;
    }

    // --- DROPDOWN HANDLING ---
    // Add a global click listener to handle dropdown toggling
    document.addEventListener('click', (e) => {
        const isExportButton = e.target.closest('.export-toggle-btn');
        
        // Close all open dropdowns first
        document.querySelectorAll('.export-toggle-btn').forEach(button => {
            if (button !== isExportButton) {
                button.nextElementSibling.classList.add('hidden');
            }
        });

        // If an export button was clicked, toggle its specific dropdown
        if (isExportButton) {
            isExportButton.nextElementSibling.classList.toggle('hidden');
        }
    });

    // --- EVENT HANDLERS ---

    /**
     * Handles saving a presentation.
     */
    async function handleSavePresentation(e) {
        const presentationId = e.target.dataset.id;
        try {
            const response = await fetch('api/save_presentation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: presentationId })
            });
            const result = await response.json();
            if (result.status === 'success') {
                showNotification(result.message, 'success');
                // Update the UI for this specific presentation
                const presentationElement = document.querySelector(`[data-presentation-id='${presentationId}']`);
                presentationElement.querySelector('.save-btn').classList.add('hidden');
                presentationElement.querySelector('.status-text').textContent = 'Saved';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Save Error:', error);
            showNotification(error.message, 'error');
        }
    }

    /**
     * Handles the click event for deleting a presentation.
     */
    function handleDeletePresentation(e) {
        const presentationId = e.target.dataset.id;
        const presentationElement = e.target.closest('[data-presentation-id]');
        
        // Set the context for the modal
        presentationToDelete = {
            id: presentationId,
            element: presentationElement
        };

        // Open the modal
        confirmModal.classList.remove('hidden');
    }

    /**
     * Proceeds with the deletion after user confirmation.
     */
    async function proceedWithDeletion(id, element) {
        try {
            const response = await fetch('api/delete_presentation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();

            if (result.status === 'success') {
                showNotification(result.message, 'success');
                // Remove the element from the page
                element.style.transition = 'opacity 0.5s';
                element.style.opacity = '0';
                setTimeout(() => {
                    element.remove();
                    if (presentationsList.children.length === 0) {
                        presentationsList.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400">You have not created any presentations yet.</p>';
                    }
                }, 500);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Delete Error:', error);
            showNotification(error.message, 'error');
        }
    }

    /**
     * Handles adding a presentation to the sidemenu and navigating to it.
     */
    function handleViewPresentation(e) {
        const key = e.target.dataset.key;
        const storageKey = `nownation_sidemenu_${currentUser.id}`;
        
        let savedKeys = JSON.parse(localStorage.getItem(storageKey) || '[]');
        
        if (!savedKeys.includes(key)) {
            savedKeys.push(key);
            localStorage.setItem(storageKey, JSON.stringify(savedKeys));
        }

        // Redirect to the main page to view the presentation
        window.location.href = `nownation.php?presentation=${key}`;
    }

    /**
     * Handles the submission of the generation form.
     */
    generationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('upload-file');
        const file = fileInput.files[0];

        if (!file) {
            showNotification('Please select a file to upload.', 'error');
            return;
        }

        generationStatus.textContent = 'Uploading and generating...';
        generationStatus.classList.remove('text-yellow-500', 'text-red-500', 'text-green-500');

        const formData = new FormData();
        formData.append('document', file);
        formData.append('template', templateSelect.value);

        try {
            const response = await fetch('api/generate_presentation.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                showNotification(result.message, 'success');
                generationStatus.textContent = '';
                // Fetch the full data of the new presentation to display it
                await loadUserPresentations(); 
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Generation Error:', error);
            generationStatus.textContent = `Error: ${error.message}`;
            generationStatus.classList.add('text-red-500');
            showNotification(error.message, 'error');
        }
    });

    // --- START ---
    initializeProfile();
});
