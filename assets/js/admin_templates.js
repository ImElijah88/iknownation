/*
 * Now Nation - Admin Templates Page Logic
 * -----------------------------------------
 * This script handles all dynamic functionality on admin_templates.php.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- STATE & ELEMENTS ---
    const form = document.getElementById('template-creator-form');
    const statusDiv = document.getElementById('generation-status');
    const templatesListDiv = document.getElementById('existing-templates-list');

    // --- INITIALIZATION ---
    loadTemplates();

    // --- EVENT LISTENERS ---
    form.addEventListener('submit', handleFormSubmit);

    templatesListDiv.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-btn')) {
            const button = e.target;
            const templateEl = button.closest('[data-template-id]');
            const templateId = templateEl.dataset.templateId;

            if (confirm('Are you sure you want to delete this template? This cannot be undone.')) {
                handleDeleteTemplate(templateId, templateEl);
            }
        }
    });

    // --- FUNCTIONS ---

    /**
     * Fetches and displays the list of existing templates.
     */
    async function loadTemplates() {
        try {
            const response = await fetch('api/get_all_templates.php');
            if (!response.ok) throw new Error('Network response was not ok.');
            const result = await response.json();

            if (result.status === 'success') {
                renderTemplates(result.templates);
            } else {
                templatesListDiv.innerHTML = `<p class="text-red-500">${result.message}</p>`;
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            templatesListDiv.innerHTML = `<p class="text-red-500">Could not fetch templates.</p>`;
        }
    }

    /**
     * Renders the list of templates into the DOM.
     * @param {Array} templates - An array of template objects.
     */
    function renderTemplates(templates) {
        templatesListDiv.innerHTML = ''; // Clear current list
        if (templates.length === 0) {
            templatesListDiv.innerHTML = '<p class="text-gray-500">No templates found.</p>';
            return;
        }

        templates.forEach(template => {
            const templateEl = document.createElement('div');
            templateEl.className = 'bg-black/5 dark:bg-white/5 p-4 rounded-lg flex items-center justify-between';
            templateEl.setAttribute('data-template-id', template.id);
            
            let featuresHtml = '';
            if (template.features) {
                // Safely parse features, which might be a JSON string
                const features = typeof template.features === 'string' ? JSON.parse(template.features) : template.features;
                featuresHtml = Object.keys(features).filter(key => features[key]).map(key => 
                    `<span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-cyan-600 bg-cyan-200 last:mr-0 mr-1">${key.replace('_', ' ')}</span>`
                ).join('');
            }

            templateEl.innerHTML = `
                <div>
                    <h3 class="font-bold text-lg">${template.template_name}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${template.description}</p>
                    <div class="mt-2">${featuresHtml}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <button data-id="${template.id}" class="delete-btn px-3 py-1 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg">Delete</button>
                </div>
            `;
            templatesListDiv.appendChild(templateEl);
        });
    }

    /**
     * Handles the submission of the new template form.
     * @param {Event} e - The form submission event.
     */
    async function handleFormSubmit(e) {
        e.preventDefault();
        statusDiv.textContent = 'Saving...';
        statusDiv.className = 'mt-4 text-center text-yellow-500';

        const formData = new FormData(form);
        const templateName = formData.get('template_name');
        const description = formData.get('description');
        const featureCheckboxes = form.querySelectorAll('input[name="features"]:checked');
        
        const features = {};
        featureCheckboxes.forEach(cb => {
            features[cb.value] = true;
        });

        try {
            const response = await fetch('api/create_template.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    template_name: templateName, 
                    description: description,
                    features: features
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = result.message;
                statusDiv.className = 'mt-4 text-center text-green-500';
                form.reset();
                loadTemplates(); // Refresh the list
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusDiv.textContent = `Error: ${error.message}`;
            statusDiv.className = 'mt-4 text-center text-red-500';
            console.error('Form submission error:', error);
        }
    }

    /**
     * Handles the deletion of a template.
     * @param {string} templateId - The ID of the template to delete.
     * @param {HTMLElement} templateEl - The HTML element to remove on success.
     */
    async function handleDeleteTemplate(templateId, templateEl) {
        try {
            const response = await fetch('api/delete_template.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: templateId })
            });

            const result = await response.json();

            if (result.status === 'success') {
                statusDiv.textContent = result.message;
                statusDiv.className = 'mt-4 text-center text-green-500';
                templateEl.remove();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            statusDiv.textContent = `Error: ${error.message}`;
            statusDiv.className = 'mt-4 text-center text-red-500';
            console.error('Delete error:', error);
        }
    }
});
