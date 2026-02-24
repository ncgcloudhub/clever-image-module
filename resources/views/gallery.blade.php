@extends('layouts.app')

@section('title', 'My Gallery - Clever Creator AI')

@push('styles')
<style>
    /* ==== MODAL STYLES ==== */
    .image-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.92);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .image-modal.active {
        display: flex;
        opacity: 1;
    }
    .image-modal-content {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        gap: 2rem;
        padding: 5rem 2rem 2rem 2rem;
        overflow: hidden;
    }
    .modal-image-container {
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        min-height: 0;
    }
    .modal-image-container img {
        max-width: 100%;
        max-height: calc(70vh - 120px);
        object-fit: contain;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
        z-index: 2;
        position: relative;
        flex-shrink: 1;
        cursor: zoom-in;
        transition: transform 0.2s ease;
    }
    .modal-image-container img:hover { transform: scale(1.02); }

    /* Fullscreen */
    .fullscreen-viewer {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(0,0,0,0.96);
        display: none;
        align-items: center;
        justify-content: center;
    }
    .fullscreen-viewer.active { display: flex; }
    .fullscreen-image { max-width: 100vw; max-height: 100vh; object-fit: contain; cursor: zoom-out; }
    .fullscreen-close {
        position: absolute; top: 1.5rem; right: 2rem;
        border: none; background: rgba(0,0,0,0.6); border-radius: 9999px;
        color: white; padding: 0.5rem 0.9rem;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: background 0.2s ease, transform 0.2s ease;
    }
    .fullscreen-close:hover { background: rgba(0,0,0,0.85); transform: translateY(-1px); }

    /* Right details panel */
    .modal-details {
        width: 400px;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-details-section { margin-bottom: 1.5rem; }
    .modal-details-heading {
        display: flex; align-items: center; gap: 0.5rem;
        margin-bottom: 0.75rem;
        color: rgba(255,255,255,0.9);
        font-size: 0.75rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        font-weight: 500;
    }
    .neural-description {
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
        line-height: 1.6;
        font-weight: 300;
    }
    .technical-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .technical-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .technical-label { color: rgba(255,255,255,0.6); font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; }
    .technical-value { color: white; font-size: 0.85rem; font-weight: 500; }

    /* Close / share pill */
    .modal-close {
        position: absolute; top: 1.75rem; left: 2.5rem;
        border: none; background: rgba(255,255,255,0.08);
        backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
        border-radius: 9999px; color: white;
        font-size: 0.75rem; letter-spacing: 0.18em; text-transform: uppercase;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        gap: 0.4rem; padding: 0.65rem 1.25rem;
        transition: all 0.25s ease; z-index: 20;
    }
    .modal-close:hover { background: rgba(255,255,255,0.16); transform: translateY(-1px); }
    .modal-close span.material-symbols-outlined { font-size: 1.1rem; }

    /* Nav arrows */
    .modal-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 2.5rem; height: 2.5rem;
        border: none; background: rgba(255,255,255,0.06);
        backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        border-radius: 50%; color: white; font-size: 1.4rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: all 0.3s ease; z-index: 10;
    }
    .modal-nav:hover { background: rgba(43,108,238,0.6); transform: translateY(-50%) scale(1.1); }
    .modal-nav.prev { left: 1rem; }
    .modal-nav.next { right: 1rem; }

    /* Related visions thumbnails */
    .modal-thumbnails-container {
        position: relative; width: 100%; max-width: 600px;
        display: flex; flex-direction: column; align-items: center;
        gap: 0.75rem; margin-top: auto; padding-top: 1rem;
        flex-shrink: 0; z-index: 1;
    }
    .modal-thumbnails-container * { pointer-events: auto; }
    .modal-thumbnails-label {
        color: rgba(255,255,255,0.6); font-size: 0.7rem;
        letter-spacing: 0.15em; text-transform: uppercase; font-weight: 400;
    }
    .modal-thumbnails { display: flex; gap: 0.5rem; padding: 0; }
    .modal-thumbnail {
        width: 4rem; height: 4rem; border-radius: 0.5rem; overflow: hidden;
        border: 1px solid rgba(255,255,255,0.15);
        background-size: cover; background-position: center;
        opacity: 0.6; cursor: pointer; transition: all 0.3s ease; flex-shrink: 0;
    }
    .modal-thumbnail:hover { opacity: 0.9; transform: translateY(-2px); border-color: rgba(255,255,255,0.3); }
    .modal-thumbnail.active { opacity: 1; border-color: rgba(59,130,246,0.8); box-shadow: 0 0 0 2px rgba(59,130,246,0.4); }

    /* Action buttons */
    .modal-action-btn {
        display: flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.75rem 1rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0.5rem; color: white; cursor: pointer;
        transition: all 0.25s ease; font-size: 0.75rem;
    }
    .modal-action-btn:hover { background: rgba(43,108,238,0.3); border-color: rgba(43,108,238,0.5); transform: translateY(-1px); }

    /* Glass panel for details box */
    .glass-panel {
        background: rgba(255,255,255,0.03);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .image-modal { overflow-y: auto; overflow-x: hidden; align-items: flex-start; }
        .image-modal-content { flex-direction: column; max-width: 95vw; max-height: none; overflow: visible; margin: 0 auto; padding: 1rem; }
        .modal-image-container { min-height: auto; }
        .modal-details { width: 100%; max-height: none; }
    }
</style>
@endpush

@section('content')
<section>
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black text-white">My Gallery</h2>
            <p class="text-slate-400 text-sm">All your AI-generated images</p>
        </div>
        <a href="{{ route('nano.visual.tools') }}"
           class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all">
            <span class="material-symbols-outlined">add</span>
            Create New
        </a>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">search</span>
            <input type="text" id="gallery-search-input"
                   placeholder="Search by prompt or model..."
                   class="w-full glass rounded-xl py-3 pl-12 pr-12 text-white placeholder-slate-500 bg-white/5 border border-white/10 focus:border-primary/50 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-all">
            <button id="gallery-search-clear" class="hidden absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <p id="gallery-search-results" class="hidden text-slate-400 text-sm mt-2"></p>
    </div>

    <!-- Gallery Grid -->
    <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @for ($i = 0; $i < 8; $i++)
        <div class="skeleton-card glass rounded-2xl aspect-square animate-pulse bg-white/5"></div>
        @endfor
    </div>

    <!-- Empty state -->
    <div id="gallery-empty" class="hidden glass p-12 rounded-2xl text-center">
        <span class="material-symbols-outlined text-5xl text-slate-600 mb-4 block">image_not_supported</span>
        <p class="text-white font-bold text-lg mb-2">No images yet</p>
        <p class="text-slate-400 text-sm mb-6">Generate your first image with our visual tools.</p>
        <a href="{{ route('nano.visual.tools') }}"
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl font-bold transition-all">
            <span class="material-symbols-outlined">auto_fix</span>
            Open Studio
        </a>
    </div>

    <!-- Error state -->
    <div id="gallery-error" class="hidden glass p-8 rounded-2xl text-center">
        <span class="material-symbols-outlined text-4xl text-red-400 mb-3 block">error</span>
        <p class="text-red-300 font-bold mb-1">Failed to load gallery</p>
        <p id="gallery-error-msg" class="text-slate-500 text-sm mb-4"></p>
        <button onclick="loadGallery(1)" class="text-primary text-sm font-bold hover:underline">Try again</button>
    </div>

    <!-- Pagination -->
    <div id="gallery-pagination" class="hidden flex justify-center items-center gap-3 mt-10">
        <button id="btn-prev" onclick="loadGallery(currentPage - 1)"
                class="flex items-center gap-1 px-4 py-2 rounded-xl glass text-slate-300 hover:text-white hover:bg-white/10 transition-all disabled:opacity-30 disabled:cursor-not-allowed font-medium">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            Prev
        </button>
        <span id="page-info" class="text-slate-400 text-sm"></span>
        <button id="btn-next" onclick="loadGallery(currentPage + 1)"
                class="flex items-center gap-1 px-4 py-2 rounded-xl glass text-slate-300 hover:text-white hover:bg-white/10 transition-all disabled:opacity-30 disabled:cursor-not-allowed font-medium">
            Next
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
    </div>
</section>

@endsection

@push('modals')
<!-- ============================================================ -->
<!--  IMAGE DETAIL MODAL                                          -->
<!-- ============================================================ -->
<div id="imageModal" class="image-modal">
    <button class="modal-close" id="modalClose">
        <span class="material-symbols-outlined">chevron_left</span>
        <span>Back</span>
    </button>
    <button class="modal-nav prev" id="modalPrev">
        <span class="material-symbols-outlined">chevron_left</span>
    </button>
    <button class="modal-nav next" id="modalNext">
        <span class="material-symbols-outlined">chevron_right</span>
    </button>

    <div class="image-modal-content">
        <!-- Left: Image + Related Visions -->
        <div class="modal-image-container">
            <img id="modalImage" src="" alt="Generated image" />
            <div class="modal-thumbnails-container">
                <div class="modal-thumbnails-label">RELATED VISIONS</div>
                <div id="modalThumbnails" class="modal-thumbnails"></div>
            </div>
        </div>

        <!-- Right: Details Panel -->
        <div class="modal-details">
            <div class="glass-panel rounded-xl p-6">
                <h2 class="text-white text-xl font-bold mb-4">Image Details</h2>

                <!-- Action Buttons -->
                <div class="flex flex-nowrap gap-2 mb-6">
                    <button id="modalDownloadBtn" class="modal-action-btn group" title="Download">
                        <span class="material-symbols-outlined text-sm text-emerald-400 group-hover:text-emerald-300">download</span>
                    </button>
                    <button id="modalShareBtn" class="modal-action-btn group" title="Share / copy link">
                        <span class="material-symbols-outlined text-sm text-sky-400 group-hover:text-sky-300">share</span>
                    </button>
                    <button id="modalOpenFullBtn" class="modal-action-btn group" title="Open fullscreen">
                        <span class="material-symbols-outlined text-sm text-violet-300 group-hover:text-violet-200">fullscreen</span>
                    </button>
                    <button id="modalUseInGeneratorBtn" class="modal-action-btn group" title="Use in studio">
                        <span class="material-symbols-outlined text-sm text-primary group-hover:text-primary/80">auto_awesome</span>
                    </button>
                </div>

                <!-- Prompt -->
                <div class="modal-details-section">
                    <div class="modal-details-heading"><span>PROMPT</span></div>
                    <div class="flex items-start gap-2">
                        <p id="modalPrompt" class="neural-description flex-1" style="max-height:150px;overflow-y:auto;word-break:break-word;"></p>
                        <button id="modalCopyPromptBtn" class="flex-shrink-0 p-2 text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-all" title="Copy prompt">
                            <span class="material-symbols-outlined text-lg">content_copy</span>
                        </button>
                    </div>
                </div>

                <!-- Model -->
                <div class="modal-details-section">
                    <div class="modal-details-heading"><span>MODEL</span></div>
                    <p id="modalModel" class="technical-value"></p>
                </div>

                <!-- Generation Parameters -->
                <div class="modal-details-section">
                    <div class="modal-details-heading"><span>GENERATION PARAMETERS</span></div>
                    <div id="modalParamsGrid" class="technical-grid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Viewer -->
<div id="fullscreenViewer" class="fullscreen-viewer">
    <button id="fullscreenClose" class="fullscreen-close">
        <span class="material-symbols-outlined">close</span>
    </button>
    <img id="fullscreenImage" src="" alt="Fullscreen image" class="fullscreen-image">
</div>
@endpush

@push('scripts')
<script>
// ============================================================
//  GALLERY DATA & STATE
// ============================================================
let currentPage       = 1;
let lastPage          = 1;
let imagesData        = [];
let allImages         = [];   // current browsed page
let allPagesCache     = null; // all pages combined, null = not fetched yet
let allPagesFetching  = false;
let currentIndex      = 0;
let searchTerm        = '';

// ============================================================
//  GALLERY LOADING
// ============================================================
function showSkeletons() {
    const grid = document.getElementById('gallery-grid');
    grid.innerHTML = '';
    for (let i = 0; i < 8; i++) {
        const div = document.createElement('div');
        div.className = 'skeleton-card glass rounded-2xl aspect-square animate-pulse bg-white/5';
        grid.appendChild(div);
    }
    grid.classList.remove('hidden');
    document.getElementById('gallery-empty').classList.add('hidden');
    document.getElementById('gallery-error').classList.add('hidden');
    document.getElementById('gallery-pagination').classList.add('hidden');
}

function formatDate(iso) {
    const d = new Date(iso);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function renderGallery(images) {
    imagesData = images;   // store for modal use
    const grid = document.getElementById('gallery-grid');
    grid.innerHTML = '';

    if (!images.length) {
        grid.classList.add('hidden');
        document.getElementById('gallery-empty').classList.remove('hidden');
        document.getElementById('gallery-pagination').classList.add('hidden');
        return;
    }

    grid.classList.remove('hidden');
    document.getElementById('gallery-empty').classList.add('hidden');

    images.forEach(function(image, index) {
        const card = document.createElement('div');
        card.className = 'group relative rounded-2xl overflow-hidden glass aspect-square cursor-zoom-in hover:ring-2 hover:ring-primary/50 transition-all image-modal-trigger';
        card.dataset.index = index;

        const img = document.createElement('img');
        img.src        = image.image_url;
        img.alt        = image.prompt || 'Generated image';
        img.className  = 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105';
        img.loading    = 'lazy';

        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4';

        const promptEl = document.createElement('p');
        promptEl.className  = 'text-white text-xs font-medium line-clamp-2 mb-1';
        promptEl.textContent = image.prompt || 'No prompt';

        const meta = document.createElement('div');
        meta.className = 'flex items-center gap-2 text-slate-400 text-[10px]';

        if (image.tool) {
            const badge = document.createElement('span');
            badge.className  = 'bg-primary/20 text-primary px-2 py-0.5 rounded-full font-bold';
            badge.textContent = image.tool.name;
            meta.appendChild(badge);
        }
        if (image.created_at) {
            const dateSpan = document.createElement('span');
            dateSpan.textContent = formatDate(image.created_at);
            meta.appendChild(dateSpan);
        }

        overlay.appendChild(promptEl);
        overlay.appendChild(meta);
        card.appendChild(img);
        card.appendChild(overlay);

        card.addEventListener('click', function() { openModal(index); });

        grid.appendChild(card);
    });
}

// ============================================================
//  SEARCH
// ============================================================
function filterImages(images, term) {
    return images.filter(function(img) {
        var prompt   = (img.prompt || '').toLowerCase();
        var model    = (img.model || '').toLowerCase();
        var toolName = img.tool ? (img.tool.name || '').toLowerCase() : '';
        return prompt.includes(term) || model.includes(term) || toolName.includes(term);
    });
}

function showFilteredResults(filtered, term) {
    var resultsEl = document.getElementById('gallery-search-results');
    resultsEl.textContent = filtered.length + ' result' + (filtered.length !== 1 ? 's' : '') + ' for "' + term + '"';
    resultsEl.classList.remove('hidden');
    document.getElementById('gallery-pagination').classList.add('hidden');
    renderGallery(filtered);
}

function applySearch() {
    var term     = searchTerm.trim().toLowerCase();
    var resultsEl = document.getElementById('gallery-search-results');
    var clearBtn  = document.getElementById('gallery-search-clear');

    clearBtn.classList.toggle('hidden', term === '');

    if (!term) {
        resultsEl.classList.add('hidden');
        renderGallery(allImages);
        if (lastPage > 1) {
            document.getElementById('gallery-pagination').classList.remove('hidden');
            document.getElementById('gallery-pagination').classList.add('flex');
        }
        return;
    }

    if (allPagesCache !== null) {
        showFilteredResults(filterImages(allPagesCache, term), searchTerm.trim());
        return;
    }

    if (lastPage <= 1) {
        allPagesCache = allImages.slice();
        showFilteredResults(filterImages(allPagesCache, term), searchTerm.trim());
        return;
    }

    if (allPagesFetching) return;
    allPagesFetching = true;

    resultsEl.textContent = 'Searching all pages…';
    resultsEl.classList.remove('hidden');

    var promises = [];
    for (var p = 1; p <= lastPage; p++) {
        promises.push(
            fetch('/api/gallery?page=' + p + '&per_page=20', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.json(); })
        );
    }

    Promise.all(promises).then(function(results) {
        allPagesCache = [];
        results.forEach(function(data) {
            if (data.success && data.data) {
                allPagesCache = allPagesCache.concat(data.data);
            }
        });
        allPagesFetching = false;
        var currentTerm = searchTerm.trim().toLowerCase();
        if (currentTerm) {
            showFilteredResults(filterImages(allPagesCache, currentTerm), searchTerm.trim());
        }
    }).catch(function() {
        allPagesFetching = false;
        showFilteredResults(filterImages(allImages, term), searchTerm.trim());
    });
}

function loadGallery(page) {
    page = page || 1;
    if (page < 1 || page > lastPage) return;

    searchTerm   = '';
    allPagesCache = null;
    allPagesFetching = false;
    var searchInput = document.getElementById('gallery-search-input');
    if (searchInput) searchInput.value = '';
    var searchResults = document.getElementById('gallery-search-results');
    if (searchResults) searchResults.classList.add('hidden');
    var searchClear = document.getElementById('gallery-search-clear');
    if (searchClear) searchClear.classList.add('hidden');

    showSkeletons();

    fetch('/api/gallery?page=' + page + '&per_page=20', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success) {
            document.getElementById('gallery-grid').classList.add('hidden');
            document.getElementById('gallery-error').classList.remove('hidden');
            document.getElementById('gallery-error-msg').textContent = data.error || 'Unknown error';
            return;
        }

        currentPage = data.meta.current_page;
        lastPage    = data.meta.last_page;
        allImages   = data.data;
        renderGallery(data.data);

        if (data.meta.last_page > 1) {
            document.getElementById('gallery-pagination').classList.remove('hidden');
            document.getElementById('gallery-pagination').classList.add('flex');
            document.getElementById('page-info').textContent = 'Page ' + currentPage + ' of ' + lastPage;
            document.getElementById('btn-prev').disabled = currentPage <= 1;
            document.getElementById('btn-next').disabled = currentPage >= lastPage;
        } else {
            document.getElementById('gallery-pagination').classList.add('hidden');
        }
    })
    .catch(function(err) {
        document.getElementById('gallery-grid').classList.add('hidden');
        document.getElementById('gallery-error').classList.remove('hidden');
        document.getElementById('gallery-error-msg').textContent = err.message;
    });
}

// ============================================================
//  MODAL
// ============================================================
const modal          = document.getElementById('imageModal');
const modalImage     = document.getElementById('modalImage');
const modalClose     = document.getElementById('modalClose');
const modalPrev      = document.getElementById('modalPrev');
const modalNext      = document.getElementById('modalNext');
const modalPrompt    = document.getElementById('modalPrompt');
const modalModel     = document.getElementById('modalModel');
const modalParamsGrid = document.getElementById('modalParamsGrid');
const modalThumbnails = document.getElementById('modalThumbnails');
const modalDownloadBtn = document.getElementById('modalDownloadBtn');
const modalShareBtn   = document.getElementById('modalShareBtn');
const modalOpenFullBtn = document.getElementById('modalOpenFullBtn');
const modalUseInGeneratorBtn = document.getElementById('modalUseInGeneratorBtn');
const modalCopyPromptBtn = document.getElementById('modalCopyPromptBtn');
const fullscreenViewer = document.getElementById('fullscreenViewer');
const fullscreenImage  = document.getElementById('fullscreenImage');
const fullscreenClose  = document.getElementById('fullscreenClose');

function updateThumbnails() {
    if (!modalThumbnails || !imagesData.length) return;
    modalThumbnails.innerHTML = '';

    let indices = [];
    if (currentIndex === 0) {
        for (let i = 0; i < Math.min(4, imagesData.length); i++) indices.push(i);
    } else if (currentIndex === imagesData.length - 1) {
        let start = Math.max(0, currentIndex - 3);
        for (let i = start; i <= currentIndex; i++) indices.push(i);
    } else {
        indices = [currentIndex - 1, currentIndex, currentIndex + 1, currentIndex + 2]
                  .filter(i => i >= 0 && i < imagesData.length);
    }

    indices.slice(0, 4).forEach(function(idx) {
        const thumb = document.createElement('button');
        thumb.type = 'button';
        thumb.className = 'modal-thumbnail' + (idx === currentIndex ? ' active' : '');
        thumb.style.backgroundImage = "url('" + imagesData[idx].image_url + "')";
        thumb.addEventListener('click', function() { openModal(idx); });
        modalThumbnails.appendChild(thumb);
    });
}

function updateModalContent() {
    const image = imagesData[currentIndex];
    if (!image) return;

    // Image
    modalImage.src = image.image_url;

    // Prompt
    modalPrompt.textContent = image.prompt || 'No prompt';

    // Model
    modalModel.textContent = image.model || 'Unknown';

    // Generation parameters
    modalParamsGrid.innerHTML = '';
    const params = [];
    if (image.resolution) params.push({ label: 'Resolution', value: image.resolution });
    if (image.tool)       params.push({ label: 'Tool',       value: image.tool.name });
    if (image.created_at) params.push({ label: 'Created',    value: formatDate(image.created_at) });

    if (params.length) {
        params.forEach(function(p) {
            const item = document.createElement('div');
            item.className = 'technical-item';
            item.innerHTML = '<span class="technical-label">' + p.label + '</span>'
                           + '<span class="technical-value">' + p.value + '</span>';
            modalParamsGrid.appendChild(item);
        });
    } else {
        modalParamsGrid.innerHTML = '<p class="text-white/60 text-sm">No additional parameters available</p>';
    }

    // Nav arrows
    modalPrev.style.display = currentIndex > 0 ? 'flex' : 'none';
    modalNext.style.display = currentIndex < imagesData.length - 1 ? 'flex' : 'none';

    updateThumbnails();
}

function openModal(index) {
    currentIndex = index;
    updateModalContent();
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// -- Modal events --
modalClose.addEventListener('click', closeModal);

modalPrev.addEventListener('click', function() {
    if (currentIndex > 0) openModal(currentIndex - 1);
});
modalNext.addEventListener('click', function() {
    if (currentIndex < imagesData.length - 1) openModal(currentIndex + 1);
});

modal.addEventListener('click', function(e) {
    if (e.target === modal) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (!modal.classList.contains('active')) return;
    if (e.key === 'Escape')       closeModal();
    if (e.key === 'ArrowLeft'  && currentIndex > 0)                    openModal(currentIndex - 1);
    if (e.key === 'ArrowRight' && currentIndex < imagesData.length - 1) openModal(currentIndex + 1);
});

// -- Download --
modalDownloadBtn.addEventListener('click', function() {
    const image = imagesData[currentIndex];
    if (!image) return;
    const link = document.createElement('a');
    link.href     = image.image_url;
    link.download = 'generated-image-' + Date.now() + '.png';
    link.target   = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

// -- Share / copy URL --
modalShareBtn.addEventListener('click', async function() {
    const image = imagesData[currentIndex];
    if (!image) return;
    const icon = this.querySelector('.material-symbols-outlined');
    try {
        if (navigator.share) {
            await navigator.share({ title: 'AI Generated Image', text: image.prompt || '', url: image.image_url });
        } else {
            await navigator.clipboard.writeText(image.image_url);
            icon.textContent = 'check';
            setTimeout(function() { icon.textContent = 'share'; }, 2000);
        }
    } catch(err) {
        if (err.name !== 'AbortError') {
            await navigator.clipboard.writeText(image.image_url).catch(function(){});
            icon.textContent = 'check';
            setTimeout(function() { icon.textContent = 'share'; }, 2000);
        }
    }
});

// -- Copy prompt --
modalCopyPromptBtn.addEventListener('click', function() {
    const text = modalPrompt.textContent;
    const icon = this.querySelector('.material-symbols-outlined');
    navigator.clipboard.writeText(text).then(function() {
        icon.textContent = 'check';
        icon.style.color = '#4ade80';
        setTimeout(function() { icon.textContent = 'content_copy'; icon.style.color = ''; }, 2000);
    });
});

// -- Fullscreen --
function openFullscreen(url) {
    fullscreenImage.src = url;
    fullscreenViewer.classList.add('active');
}
function closeFullscreen() {
    fullscreenViewer.classList.remove('active');
    fullscreenImage.src = '';
}

modalOpenFullBtn.addEventListener('click', function() {
    openFullscreen(imagesData[currentIndex]?.image_url || modalImage.src);
});
modalImage.addEventListener('click', function() {
    openFullscreen(imagesData[currentIndex]?.image_url || modalImage.src);
});
fullscreenClose.addEventListener('click', closeFullscreen);
fullscreenViewer.addEventListener('click', function(e) {
    if (e.target === fullscreenViewer) closeFullscreen();
});

// -- Use in generator --
modalUseInGeneratorBtn.addEventListener('click', function() {
    window.location.href = '{{ route("nano.visual.tools") }}';
});

// ============================================================
//  INIT
// ============================================================
(function() {
    var searchInput = document.getElementById('gallery-search-input');
    var searchClear = document.getElementById('gallery-search-clear');
    var debounceTimer;

    searchInput.addEventListener('input', function() {
        searchTerm = this.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applySearch, 250);
    });

    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchTerm = '';
        applySearch();
        searchInput.focus();
    });
})();

loadGallery(1);
</script>
@endpush
