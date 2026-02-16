@extends('layouts.app')

@section('title', 'Image Tools - Clever Creator AI')

@push('styles')
<style>
    .view-toggle button.active {
        background: rgba(19, 164, 236, 0.2);
        color: #13a4ec;
    }
    .tool-card {
        transition: all 0.3s ease;
    }
    .tool-card:hover {
        transform: translateY(-4px);
    }
    .list-view .tool-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .list-view .tool-card .tool-icon {
        flex-shrink: 0;
    }
    .list-view .tool-card .tool-content {
        flex: 1;
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 10, 12, 0.8);
        backdrop-filter: blur(8px);
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: rgba(22, 27, 34, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 2rem;
    }
    .image-preview {
        position: relative;
        padding-bottom: 100%;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .image-preview img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
    <div>
        <h2 class="text-4xl font-black text-white mb-2">Image Tools</h2>
        <p class="text-slate-400">Generate stunning images using AI-powered visual tools</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="glass px-4 py-2 rounded-xl border border-primary/20">
            <span class="text-xs text-slate-400">Available Credits:</span>
            <span class="text-lg font-bold text-primary ml-2">120</span>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="glass p-6 rounded-2xl mb-8 border border-white/5">
    <div class="flex flex-col md:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1 relative group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
            <input
                type="text"
                id="toolSearch"
                class="w-full bg-white/5 border-white/10 rounded-xl pl-12 pr-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all placeholder:text-slate-600"
                placeholder="Search tools by name or description..."
            />
        </div>

        <!-- View Toggle -->
        <div class="view-toggle flex items-center gap-2 bg-white/5 p-1 rounded-lg">
            <button
                id="gridViewBtn"
                class="active p-2 rounded-lg transition-all"
                onclick="setView('grid')"
            >
                <span class="material-symbols-outlined text-sm">grid_view</span>
            </button>
            <button
                id="listViewBtn"
                class="p-2 rounded-lg transition-all"
                onclick="setView('list')"
            >
                <span class="material-symbols-outlined text-sm">view_list</span>
            </button>
        </div>
    </div>
</div>

<!-- Tools Container -->
<div id="toolsContainer">
    <div class="glass p-8 rounded-2xl text-center">
        <div class="inline-block p-4 bg-primary/10 rounded-xl mb-4">
            <span class="material-symbols-outlined text-4xl text-primary">autorenew</span>
        </div>
        <p class="text-slate-400">Loading available tools...</p>
    </div>
</div>

<!-- Tool Modal -->
<div id="toolModal" class="modal-overlay">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 id="modalToolName" class="text-2xl font-bold text-white">Tool Name</h3>
            <button onclick="closeToolModal()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-slate-400">close</span>
            </button>
        </div>

        <form id="runToolForm" class="space-y-6">
            <input type="hidden" id="toolId" name="tool_id">
            <input type="hidden" id="toolSlug" name="tool">

            <!-- Prompt Input -->
            <div id="promptGroup" style="display: none;">
                <label class="block text-sm font-medium text-slate-300 mb-2">Prompt</label>
                <textarea
                    id="prompt"
                    name="prompt"
                    rows="3"
                    class="w-full bg-white/5 border-white/10 rounded-xl p-4 text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all resize-none"
                    placeholder="Enter your prompt here..."
                ></textarea>
                <p id="promptHelp" class="text-xs text-slate-500 mt-2"></p>
            </div>

            <!-- Prefix Text -->
            <div id="prefixTextGroup" style="display: none;">
                <label class="block text-sm font-medium text-slate-300 mb-2">Prefix Text (Optional)</label>
                <input
                    type="text"
                    id="prefixText"
                    name="prefix_text"
                    class="w-full bg-white/5 border-white/10 rounded-xl p-3 text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all"
                    placeholder="e.g., Change color to"
                >
            </div>

            <!-- Image Uploads Container -->
            <div id="imageUploadsContainer"></div>

            <!-- Features Container -->
            <div id="featuresContainer"></div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-white/10">
                <button
                    type="submit"
                    id="runToolBtn"
                    class="flex-1 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined">auto_fix</span>
                    Generate Image
                </button>
                <button
                    type="button"
                    onclick="closeToolModal()"
                    class="px-6 py-3 bg-white/5 hover:bg-white/10 text-slate-300 rounded-xl font-medium transition-all"
                >
                    Cancel
                </button>
            </div>

            <!-- Status Message -->
            <div id="formStatus" class="hidden p-4 rounded-xl"></div>
        </form>
    </div>
</div>

<!-- Generated Images Gallery -->
<div id="imageGallerySection" class="mt-10" style="display: none;">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold text-white">Generated Images</h3>
        <button id="clearGalleryBtn" onclick="clearGallery()" class="text-sm text-slate-400 hover:text-white transition-colors">
            Clear Gallery
        </button>
    </div>
    <div id="imageGallery" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"></div>
</div>
@endsection

@push('scripts')
<script>
    let availableTools = [];
    let selectedTool = null;
    let currentView = 'grid';

    // Load tools on page load
    async function loadTools() {
        const container = document.getElementById('toolsContainer');

        try {
            const response = await fetch('{{ route("api.nano.visual.tools.get") }}', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Failed to load tools');
            }

            availableTools = data.data || [];
            renderTools(availableTools);
        } catch (e) {
            container.innerHTML = `
                <div class="glass p-8 rounded-2xl border border-red-500/20 bg-red-500/5">
                    <div class="flex items-center gap-3 text-red-400">
                        <span class="material-symbols-outlined">error</span>
                        <p>Error: ${e.message}</p>
                    </div>
                </div>
            `;
        }
    }

    function renderTools(tools) {
        const container = document.getElementById('toolsContainer');

        if (tools.length === 0) {
            container.innerHTML = `
                <div class="glass p-8 rounded-2xl text-center">
                    <p class="text-slate-400">No tools available at the moment.</p>
                </div>
            `;
            return;
        }

        const viewClass = currentView === 'list' ? 'list-view' : '';
        const gridClass = currentView === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-4';

        container.innerHTML = `
            <div class="${gridClass} ${viewClass}">
                ${tools.map(tool => `
                    <div class="tool-card glass p-6 rounded-2xl border border-white/5 hover:border-primary/30 cursor-pointer transition-all" onclick="selectTool(${tool.id})">
                        <div class="tool-icon mb-4">
                            <div class="size-12 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-2xl text-primary">auto_awesome</span>
                            </div>
                        </div>
                        <div class="tool-content">
                            <h3 class="text-lg font-bold text-white mb-2">${escapeHtml(tool.name)}</h3>
                            <p class="text-sm text-slate-400 mb-4">${escapeHtml(tool.description || 'No description available')}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-primary">${tool.credits_per_generation || 2} credits</span>
                                <button class="px-4 py-2 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg text-xs font-bold transition-all">
                                    Use Tool
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function selectTool(toolId) {
        selectedTool = availableTools.find(t => t.id === toolId);
        if (!selectedTool) return;

        // Show modal
        document.getElementById('toolModal').classList.add('active');
        document.getElementById('modalToolName').textContent = selectedTool.name;
        document.getElementById('toolId').value = selectedTool.id;
        document.getElementById('toolSlug').value = selectedTool.slug;

        // Setup form fields
        setupToolForm(selectedTool);
    }

    function closeToolModal() {
        document.getElementById('toolModal').classList.remove('active');
        document.getElementById('runToolForm').reset();
        document.getElementById('formStatus').classList.add('hidden');
    }

    function setupToolForm(tool) {
        // Prompt field
        const promptGroup = document.getElementById('promptGroup');
        const promptInput = document.getElementById('prompt');
        const promptHelp = document.getElementById('promptHelp');

        if (tool.prompt_required) {
            promptGroup.style.display = 'block';
            promptInput.required = true;
            promptInput.placeholder = tool.prompt_placeholder || 'Enter your prompt...';
            promptHelp.textContent = tool.default_prompt ? `Default: ${tool.default_prompt}` : '';
        } else {
            promptGroup.style.display = 'none';
            promptInput.required = false;
        }

        // Prefix text
        const prefixGroup = document.getElementById('prefixTextGroup');
        prefixGroup.style.display = 'block';

        // Image uploads
        const uploadsContainer = document.getElementById('imageUploadsContainer');
        uploadsContainer.innerHTML = '';

        if (tool.image_uploads && tool.image_uploads.length > 0) {
            tool.image_uploads.forEach(upload => {
                const div = document.createElement('div');
                div.innerHTML = `
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        ${escapeHtml(upload.label || upload.name)}
                        ${upload.required ? '<span class="text-red-400">*</span>' : ''}
                    </label>
                    <input
                        type="file"
                        id="upload_${upload.name}"
                        name="${upload.name}"
                        accept="image/*"
                        ${upload.required ? 'required' : ''}
                        class="w-full bg-white/5 border-white/10 rounded-xl p-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-medium hover:file:bg-primary/90 transition-all"
                    >
                    <p class="text-xs text-slate-500 mt-2">${escapeHtml(upload.description || '')}</p>
                `;
                uploadsContainer.appendChild(div);
            });
        }

        // Features
        const featuresContainer = document.getElementById('featuresContainer');
        featuresContainer.innerHTML = '';

        if (tool.features && tool.features.length > 0) {
            tool.features.forEach(feature => {
                const div = document.createElement('div');

                let inputHtml = '';
                if (feature.type === 'select') {
                    inputHtml = `
                        <select
                            id="feature_${feature.name}"
                            name="features[${feature.name}]"
                            class="w-full bg-white/5 border-white/10 rounded-xl p-3 text-white focus:ring-primary focus:border-primary transition-all"
                        >
                            ${(feature.options || []).map(opt => `
                                <option value="${escapeHtml(opt)}" ${opt === feature.default ? 'selected' : ''}>
                                    ${escapeHtml(opt)}
                                </option>
                            `).join('')}
                        </select>
                    `;
                } else if (feature.type === 'number') {
                    inputHtml = `
                        <input
                            type="number"
                            id="feature_${feature.name}"
                            name="features[${feature.name}]"
                            value="${feature.default || ''}"
                            min="${feature.min || ''}"
                            max="${feature.max || ''}"
                            class="w-full bg-white/5 border-white/10 rounded-xl p-3 text-white focus:ring-primary focus:border-primary transition-all"
                        >
                    `;
                } else {
                    inputHtml = `
                        <input
                            type="text"
                            id="feature_${feature.name}"
                            name="features[${feature.name}]"
                            value="${feature.default || ''}"
                            placeholder="${escapeHtml(feature.placeholder || '')}"
                            class="w-full bg-white/5 border-white/10 rounded-xl p-3 text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all"
                        >
                    `;
                }

                div.innerHTML = `
                    <label class="block text-sm font-medium text-slate-300 mb-2">${escapeHtml(feature.label || feature.name)}</label>
                    ${inputHtml}
                    ${feature.description ? `<p class="text-xs text-slate-500 mt-2">${escapeHtml(feature.description)}</p>` : ''}
                `;
                featuresContainer.appendChild(div);
            });
        }
    }

    // Handle form submission
    document.getElementById('runToolForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('runToolBtn');
        const statusEl = document.getElementById('formStatus');

        btn.disabled = true;
        statusEl.className = 'p-4 rounded-xl bg-primary/10 border border-primary/20 text-primary';
        statusEl.classList.remove('hidden');
        statusEl.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined animate-spin">autorenew</span>
                <span>Generating image... This may take a minute.</span>
            </div>
        `;

        try {
            const formData = new FormData(e.target);

            // Collect features
            const features = {};
            document.querySelectorAll('[name^="features["]').forEach(input => {
                const name = input.name.match(/features\[(.*?)\]/)[1];
                features[name] = input.value;
            });

            if (Object.keys(features).length > 0) {
                formData.set('features', JSON.stringify(features));
            }

            const response = await fetch('{{ route("api.nano.visual.tools.run") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Image generation failed');
            }

            statusEl.className = 'p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400';
            statusEl.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>Image generated successfully! Credits used: ${data.credits_used || 0}</span>
                </div>
            `;

            // Display generated image
            if (data.image_url) {
                displayGeneratedImage(data);
                closeToolModal();
            }
        } catch (error) {
            statusEl.className = 'p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400';
            statusEl.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <span>Error: ${error.message}</span>
                </div>
            `;
        } finally {
            btn.disabled = false;
        }
    });

    function displayGeneratedImage(data) {
        const gallerySection = document.getElementById('imageGallerySection');
        const gallery = document.getElementById('imageGallery');

        gallerySection.style.display = 'block';

        const card = document.createElement('div');
        card.className = 'group relative rounded-2xl overflow-hidden glass border border-white/5';
        card.innerHTML = `
            <div class="image-preview">
                <img src="${escapeHtml(data.image_url)}" alt="Generated image" class="group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end">
                <p class="text-white text-sm font-medium mb-3 line-clamp-2">${escapeHtml(data.image_data?.prompt || 'Generated image')}</p>
                <div class="flex gap-2">
                    <a href="${escapeHtml(data.image_url)}" target="_blank" class="flex-1 py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                        View Full Size
                    </a>
                </div>
            </div>
        `;
        gallery.insertBefore(card, gallery.firstChild);
    }

    function clearGallery() {
        if (confirm('Clear all generated images from this session?')) {
            document.getElementById('imageGallery').innerHTML = '';
            document.getElementById('imageGallerySection').style.display = 'none';
        }
    }

    function setView(view) {
        currentView = view;

        document.getElementById('gridViewBtn').classList.remove('active');
        document.getElementById('listViewBtn').classList.remove('active');

        if (view === 'grid') {
            document.getElementById('gridViewBtn').classList.add('active');
        } else {
            document.getElementById('listViewBtn').classList.add('active');
        }

        renderTools(availableTools);
    }

    // Search functionality
    document.getElementById('toolSearch').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filtered = availableTools.filter(tool =>
            tool.name.toLowerCase().includes(searchTerm) ||
            (tool.description && tool.description.toLowerCase().includes(searchTerm))
        );
        renderTools(filtered);
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal on overlay click
    document.getElementById('toolModal').addEventListener('click', (e) => {
        if (e.target.id === 'toolModal') {
            closeToolModal();
        }
    });

    // Initialize
    loadTools();
</script>
@endpush
