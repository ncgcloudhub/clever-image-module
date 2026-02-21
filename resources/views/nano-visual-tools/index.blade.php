@extends('layouts.app')

@section('title', 'Image Tools - Clever Creator AI')

@push('styles')
<style>
    .view-toggle button.active {
        background: rgba(19, 164, 236, 0.2);
        color: #13a4ec;
    }
    .tool-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        backdrop-filter: blur(12px);
    }
    .tool-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(19, 164, 236, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1);
        border-color: rgba(19, 164, 236, 0.4) !important;
    }
    .tool-card:active {
        transform: translateY(0);
    }
    .glass-panel {
        background: rgba(22, 27, 34, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(19, 164, 236, 0.3);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(19, 164, 236, 0.5);
    }
    .list-view .tool-card {
        display: flex;
        align-items: center;
        gap: 0;
        overflow: visible;
    }
    .list-view .tool-card .tool-thumbnail {
        border-radius: 0.75rem 0 0 0.75rem;
    }
    .list-view .tool-card .tool-icon {
        flex-shrink: 0;
    }
    .list-view .tool-card .tool-content {
        flex: 1;
        min-width: 0;
    }
    .tool-thumbnail {
        position: relative;
        width: 100%;
        padding-bottom: 56%;
        background: linear-gradient(135deg, rgba(19, 164, 236, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%);
        border-radius: 0.75rem 0.75rem 0 0;
        overflow: hidden;
        margin-bottom: 0;
    }
    .tool-thumbnail img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .tool-thumbnail .icon-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .tool-thumbnail img {
        transition: transform 0.3s ease;
    }
    .tool-card:hover .tool-thumbnail img {
        transform: scale(1.05);
    }
    .list-view .tool-thumbnail {
        width: 100px;
        padding-bottom: 65px;
        margin-bottom: 0;
        flex-shrink: 0;
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
    .regen-count-btn.active-count {
        background: rgba(19, 164, 236, 0.15);
        border-color: rgba(19, 164, 236, 0.5);
        color: #13a4ec;
    }
    #regenOffcanvas.open {
        transform: translateX(0);
    }
    .regen-checkbox:checked + div span.material-symbols-outlined {
        color: #13a4ec;
    }
    .regen-card {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
        transition: all 0.3s ease;
    }
    .regen-card:hover {
        border-color: rgba(19,164,236,0.3);
        box-shadow: 0 4px 20px rgba(19,164,236,0.1);
    }
    @keyframes shimmer {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .shimmer-sweep::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.06) 50%, transparent 100%);
        animation: shimmer 1.6s infinite;
    }
    @keyframes regen-pulse-border {
        0%, 100% { border-color: rgba(19,164,236,0.2); box-shadow: 0 0 0 0 rgba(19,164,236,0); }
        50%       { border-color: rgba(19,164,236,0.6); box-shadow: 0 0 12px 2px rgba(19,164,236,0.15); }
    }
    .regen-skeleton {
        border-radius: 0.75rem;
        overflow: hidden;
        position: relative;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(19,164,236,0.2);
        animation: regen-pulse-border 2s ease-in-out infinite;
    }
    #regenGenerateBtn:disabled {
        opacity: 1;
        cursor: not-allowed;
        background: rgba(19,164,236,0.5);
    }
    @keyframes offcanvas-glow {
        0%, 100% { box-shadow: inset 0 0 0 1px rgba(19,164,236,0.25), 0 0 0 0 rgba(19,164,236,0); }
        50%       { box-shadow: inset 0 0 0 1px rgba(19,164,236,0.7), 0 0 24px 4px rgba(19,164,236,0.12); }
    }
    .offcanvas-generating {
        animation: offcanvas-glow 1.8s ease-in-out infinite;
        border-left-color: rgba(19,164,236,0.5) !important;
    }
    .offcanvas-generating .offcanvas-form-overlay {
        display: flex;
    }
    .offcanvas-form-overlay {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(10,10,14,0.45);
        backdrop-filter: blur(2px);
        z-index: 10;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    /* ── Regen layout: source thumbnail → variations at a glance ─────── */
    #previewRightSection.regen-active {
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 0 !important;
    }

    /* Source image row – compact top strip */
    #previewRightSection.regen-active #previewContent {
        min-height: unset !important;
        flex: 0 0 auto;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 0.875rem 1.25rem !important;
        gap: 1rem;
        background: rgba(0,0,0,0.25);
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    /* Smooth thumbnail shrink – driven by CSS transition */
    #previewContent > div {
        transition: width 0.5s cubic-bezier(0.16,1,0.3,1),
                    max-width 0.5s cubic-bezier(0.16,1,0.3,1),
                    padding 0.5s cubic-bezier(0.16,1,0.3,1);
    }
    #previewRightSection.regen-active #previewContent > div {
        width: 82px !important;
        max-width: 82px !important;
        padding: 0 !important;
        flex-shrink: 0;
    }
    #previewRightSection.regen-active #previewContent > div > div {
        border-radius: 0.75rem !important;
    }

    /* Source meta label – only shown in regen mode */
    .regen-source-meta {
        display: none;
        flex-direction: column;
        gap: 0.2rem;
    }
    #previewRightSection.regen-active .regen-source-meta {
        display: flex;
    }

    /* Variations section fills the remaining height, no scroll */
    #previewRightSection.regen-active #regeneratedSection {
        flex: 1 !important;
        min-height: 0;
        overflow: hidden !important;
        padding: 0.875rem 1.25rem !important;
        display: flex !important;
        flex-direction: column;
    }
    #previewRightSection.regen-active #regeneratedSection > div {
        flex: 1;
        display: flex;
        flex-direction: column;
        border-top: none !important;
        padding-top: 0 !important;
        min-height: 0;
    }
    /* Label row stays compact */
    #previewRightSection.regen-active #regeneratedSection > div > div:first-child {
        flex-shrink: 0;
        margin-bottom: 0.625rem;
    }
    /* Grid becomes a flex row that fills the remaining height */
    #previewRightSection.regen-active #regeneratedGrid {
        flex: 1 !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 0.75rem;
        min-height: 0;
        grid-template-columns: unset !important;
    }
    #previewRightSection.regen-active #regeneratedGrid > * {
        flex: 1;
        min-width: 0;
        min-height: 0;
        aspect-ratio: unset !important; /* override inline style */
    }
    #previewRightSection.regen-active .regen-card {
        display: flex !important;
        flex-direction: column;
        height: 100%;
    }
    #previewRightSection.regen-active .regen-card > div:first-child {
        flex: 1 !important;
        aspect-ratio: unset !important;
    }
    #previewRightSection.regen-active .regen-skeleton {
        height: auto !important;
        display: flex !important;
        flex-direction: column;
    }
    #previewRightSection.regen-active .regen-skeleton > div {
        flex: 1;
        min-height: 0;
    }
    /* Fade-in for the variations section when it appears */
    #previewRightSection.regen-active #regeneratedSection {
        animation: regenSectionIn 0.35s 0.15s cubic-bezier(0.16,1,0.3,1) both;
    }
    @keyframes regenSectionIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="h-full">

<!-- Floating Test Button (dev only) -->
<button onclick="testRegenerateUI()" id="floatingTestBtn"
    class="fixed bottom-6 right-6 z-40 flex items-center gap-2 px-4 py-2.5 bg-slate-800/90 hover:bg-slate-700/90 border border-white/10 hover:border-primary/40 rounded-full shadow-xl backdrop-blur-sm text-xs text-slate-300 hover:text-primary transition-all group">
    <span class="material-symbols-outlined text-sm group-hover:animate-spin" style="animation-duration:2s">science</span>
    Test Regenerate UI
</button>

<!-- Directory/Grid View -->
<div id="directoryView">
<!-- Search and Filter Bar -->
<div class="glass p-4 rounded-xl mb-6 border border-white/5">
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
</div>

<!-- Tool Interface View -->
<div id="toolInterfaceView" class="hidden -m-10">
    <div class="flex min-h-screen overflow-hidden">
        <!-- Left Configuration Panel -->
        <section class="w-80 lg:w-96 glass-panel border-r border-white/5 flex flex-col overflow-hidden">
            <!-- Header with Back Button -->
            <div class="p-4 border-b border-white/5 flex items-center gap-3 flex-shrink-0">
                <button onclick="backToDirectory()" class="p-2 hover:bg-white/5 rounded-lg transition-colors flex-shrink-0">
                    <span class="material-symbols-outlined text-slate-400 hover:text-white">arrow_back</span>
                </button>
                <!-- Tool thumbnail -->
                <div id="toolHeaderThumbnail" class="hidden w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-white/10 bg-gradient-to-br from-primary/20 to-secondary/20">
                    <img id="toolHeaderThumbnailImg" src="" alt="" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0">
                    <h2 id="toolInterfaceTitle" class="font-bold text-white text-sm truncate"></h2>
                    <p class="text-[10px] text-slate-500">Configure & Generate</p>
                </div>
            </div>

            <!-- Form Content - Scrollable -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                <form id="toolInterfaceForm" class="space-y-4">
                    <input type="hidden" id="interfaceToolId" name="tool_id">
                    <input type="hidden" id="interfaceToolSlug" name="tool">

                    <!-- Dynamic Form Content -->
                    <div id="interfaceFormContent"></div>

                    <!-- Generate Button -->
                    <button
                        type="submit"
                        id="interfaceGenerateBtn"
                        class="w-full py-2.5 bg-primary hover:bg-primary/90 rounded-lg font-bold text-sm text-white shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 group mt-4"
                    >
                        <span class="material-symbols-outlined text-lg group-hover:animate-pulse">bolt</span>
                        GENERATE
                    </button>

                    <!-- Status Message -->
                    <div id="interfaceFormStatus" class="hidden p-3 rounded-lg text-xs"></div>
                </form>
            </div>
        </section>

        <!-- Right Preview Area -->
        <section id="previewRightSection" class="flex-1 bg-black/40 flex flex-col items-center p-6 relative overflow-y-auto custom-scrollbar">
            <!-- Preview Controls -->
            <div id="previewControls" class="absolute top-4 right-4 flex items-center gap-2 z-20 hidden">
                <div class="bg-background-dark/90 backdrop-blur-md border border-white/10 rounded-lg flex p-1 shadow-xl">
                    <button class="p-2 hover:bg-white/5 rounded-md text-slate-400 hover:text-white transition-colors" onclick="downloadCurrentImage()" title="Download">
                        <span class="material-symbols-outlined text-xl">download</span>
                    </button>
                    <button class="p-2 hover:bg-white/5 rounded-md text-slate-400 hover:text-white transition-colors" onclick="shareCurrentImage()" title="Share">
                        <span class="material-symbols-outlined text-xl">share</span>
                    </button>
                    <div class="w-px h-5 bg-white/10 mx-1 self-center"></div>
                    <button class="p-2 hover:bg-primary/10 rounded-md text-slate-400 hover:text-primary transition-colors flex items-center gap-1.5 pr-3" onclick="openRegenerateCanvas()" title="Regenerate Variations" id="regenerateBtn">
                        <span class="material-symbols-outlined text-xl">auto_fix_high</span>
                        <span class="text-xs font-semibold">Regenerate</span>
                    </button>
                </div>
            </div>

            <!-- Preview Content -->
            <div id="previewContent" class="w-full flex items-center justify-center min-h-[60%]">
                <div class="text-center">
                    <div class="inline-block p-5 bg-primary/10 rounded-xl mb-3">
                        <span class="material-symbols-outlined text-5xl text-primary">image</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Preview Area</h3>
                    <p class="text-sm text-slate-400">Your generated image will appear here</p>
                    <button onclick="testRegenerateUI()" class="mt-4 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-primary/30 rounded-lg text-xs text-slate-400 hover:text-primary transition-all flex items-center gap-2 mx-auto">
                        <span class="material-symbols-outlined text-sm">science</span>
                        Preview Regenerate UI
                    </button>
                </div>
            </div>

            <!-- Regenerated Variations Section -->
            <div id="regeneratedSection" class="hidden w-full px-6 pb-6">
                <div class="border-t border-white/10 pt-5">
                    <!-- Section Label -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-full px-3 py-1">
                            <span class="material-symbols-outlined text-primary text-sm">auto_fix_high</span>
                            <span class="text-xs font-bold text-primary tracking-wide">REGENERATED VARIATIONS</span>
                        </div>
                        <div class="flex-1 h-px bg-white/5"></div>
                        <span class="text-[10px] text-slate-500" id="regenTimestamp"></span>
                    </div>
                    <!-- Regenerated Images Grid -->
                    <div id="regeneratedGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Regenerate Offcanvas -->
<div id="regenOffcanvas" class="fixed inset-y-0 right-0 z-50 w-full sm:w-96 flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out overflow-hidden">
    <!-- Backdrop (click-outside close) -->
    <div id="regenBackdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm -z-10 hidden" onclick="closeRegenerateCanvas()"></div>
    <div class="flex-1 flex flex-col bg-[#0f1117] border-l border-white/10 shadow-2xl overflow-hidden" id="regenOffcanvasPanel">
        <!-- Header -->
        <div class="p-4 border-b border-white/10 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-primary/10 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-lg">auto_fix_high</span>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm">Regenerate Image</h3>
                    <p class="text-[10px] text-slate-500">Create variations of your result</p>
                </div>
            </div>
            <button onclick="closeRegenerateCanvas()" class="p-2 hover:bg-white/5 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-slate-400 text-lg">close</span>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-5 relative" id="regenFormBody">
            <!-- Generating overlay (shown via .offcanvas-generating) -->
            <div class="offcanvas-form-overlay" id="regenFormOverlay">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <p class="text-xs text-primary/80 font-medium animate-pulse" id="regenFormOverlayText">Generating…</p>
            </div>

            <!-- Source Image Preview -->
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Source Image</p>
                <div class="rounded-xl overflow-hidden border border-white/10 bg-white/5 aspect-square w-full max-w-[120px] mx-auto relative">
                    <img id="regenSourceImage" src="" alt="Source" class="w-full h-full object-contain">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent py-1.5 px-2">
                        <p class="text-[9px] text-white/70 text-center font-medium">Original</p>
                    </div>
                </div>
            </div>

            <!-- Modification Prompt -->
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">Modification Prompt</label>
                <textarea
                    id="regenPrompt"
                    rows="3"
                    class="w-full bg-white/5 border border-white/10 rounded-lg p-2.5 text-sm text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all resize-none"
                    placeholder="Describe how you want to modify the image..."
                ></textarea>
            </div>

            <!-- Number of Variations -->
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Number of Variations</p>
                <div class="flex gap-2">
                    <button type="button" onclick="selectRegenCount(1)" id="regenCount1"
                        class="regen-count-btn flex-1 py-2 rounded-lg border border-white/10 text-sm font-bold transition-all hover:border-primary/50 bg-white/5 text-slate-300 active-count">
                        1
                    </button>
                    <button type="button" onclick="selectRegenCount(2)" id="regenCount2"
                        class="regen-count-btn flex-1 py-2 rounded-lg border border-white/10 text-sm font-bold transition-all hover:border-primary/50 bg-white/5 text-slate-300">
                        2
                    </button>
                    <button type="button" onclick="selectRegenCount(3)" id="regenCount3"
                        class="regen-count-btn flex-1 py-2 rounded-lg border border-white/10 text-sm font-bold transition-all hover:border-primary/50 bg-white/5 text-slate-300">
                        3
                    </button>
                </div>
            </div>

            <!-- Modification Options -->
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Modification Options</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer group transition-all">
                        <input type="checkbox" id="regenLayoutChange" class="regen-checkbox w-4 h-4 rounded border-white/20 bg-white/5 accent-primary cursor-pointer">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-base">dashboard_customize</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-200">Layout Change</p>
                                <p class="text-[9px] text-slate-500">Alter composition & arrangement</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer group transition-all">
                        <input type="checkbox" id="regenStyleChange" class="regen-checkbox w-4 h-4 rounded border-white/20 bg-white/5 accent-primary cursor-pointer">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-base">style</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-200">Style Change</p>
                                <p class="text-[9px] text-slate-500">Modify artistic style & aesthetics</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer group transition-all">
                        <input type="checkbox" id="regenColorChange" class="regen-checkbox w-4 h-4 rounded border-white/20 bg-white/5 accent-primary cursor-pointer">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-base">palette</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-200">Color Adjustment</p>
                                <p class="text-[9px] text-slate-500">Change color tones & palette</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer group transition-all">
                        <input type="checkbox" id="regenLightingChange" class="regen-checkbox w-4 h-4 rounded border-white/20 bg-white/5 accent-primary cursor-pointer">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-base">light_mode</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-200">Lighting Change</p>
                                <p class="text-[9px] text-slate-500">Adjust lighting & shadows</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 cursor-pointer group transition-all">
                        <input type="checkbox" id="regenDetailEnhance" class="regen-checkbox w-4 h-4 rounded border-white/20 bg-white/5 accent-primary cursor-pointer">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-base">lens_blur</span>
                            <div>
                                <p class="text-xs font-semibold text-slate-200">Detail Enhancement</p>
                                <p class="text-[9px] text-slate-500">Sharpen details & textures</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Regen Status -->
            <div id="regenStatus" class="hidden p-3 rounded-lg text-xs"></div>
        </div>

        <!-- Footer / Generate Button -->
        <div class="p-4 border-t border-white/10 flex-shrink-0 space-y-2">
            <button
                id="regenGenerateBtn"
                onclick="submitRegenerate()"
                class="w-full py-3 bg-primary hover:bg-primary/90 rounded-xl font-bold text-sm text-white shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 group"
            >
                <span class="material-symbols-outlined text-lg group-hover:animate-pulse">auto_fix_high</span>
                GENERATE VARIATIONS
            </button>
            <button
                type="button"
                onclick="testRegeneratedResults(); setTimeout(closeRegenerateCanvas, 800);"
                class="w-full py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs text-slate-400 hover:text-white transition-all flex items-center justify-center gap-2"
            >
                <span class="material-symbols-outlined text-sm">science</span>
                Preview Results (Test Only)
            </button>
        </div>
    </div>
</div>

<!-- Tool Modal (Hidden - kept for backwards compatibility) -->
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

<!-- Generated Images Gallery (Hidden - using preview area instead) -->
<div id="imageGallerySection" class="mt-8 hidden">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-white">Generated Images</h3>
        <button id="clearGalleryBtn" onclick="clearGallery()" class="text-sm text-slate-400 hover:text-white transition-colors">
            Clear Gallery
        </button>
    </div>
    <div id="imageGallery" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"></div>
</div>
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
        const gridClass = currentView === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3';

        container.innerHTML = `
            <div class="${gridClass} ${viewClass}">
                ${tools.map(tool => {
                    const thumbnailHtml = tool.preview_image
                        ? `<img src="${escapeHtml(tool.preview_image)}"
                               alt="${escapeHtml(tool.name)}"
                               loading="lazy"
                               onerror="this.style.display='none'; this.parentElement.innerHTML = '<div class=\\'icon-placeholder\\'><div class=\\'size-12 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center\\'><span class=\\'material-symbols-outlined text-3xl text-primary\\'>auto_awesome</span></div></div>';">`
                        : `<div class="icon-placeholder">
                            <div class="size-12 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-3xl text-primary">auto_awesome</span>
                            </div>
                           </div>`;

                    return `
                        <div class="tool-card glass rounded-xl border border-white/5 hover:border-primary/30 cursor-pointer transition-all overflow-hidden" onclick="selectTool(${tool.id})">
                            <div class="tool-thumbnail">
                                ${thumbnailHtml}
                            </div>
                            <div class="tool-content p-4">
                                <h3 class="text-base font-bold text-white mb-1.5">${escapeHtml(tool.name)}</h3>
                                <p class="text-xs text-slate-400 mb-3 line-clamp-2 leading-relaxed">${escapeHtml(tool.description || 'No description available')}</p>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-primary/90 bg-primary/10 px-2 py-1 rounded">${tool.credits_per_generation || 2} credits</span>
                                    <button class="px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">play_arrow</span>
                                        Use
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function selectTool(toolId) {
        selectedTool = availableTools.find(t => t.id === toolId);
        if (!selectedTool) return;

        // Hide directory view and show interface view
        document.getElementById('directoryView').classList.add('hidden');
        document.getElementById('toolInterfaceView').classList.remove('hidden');

        // Set tool info
        document.getElementById('toolInterfaceTitle').textContent = selectedTool.name;
        document.getElementById('interfaceToolId').value = selectedTool.id;
        document.getElementById('interfaceToolSlug').value = selectedTool.slug;

        // Show tool thumbnail in panel header
        const thumbWrap = document.getElementById('toolHeaderThumbnail');
        const thumbImg  = document.getElementById('toolHeaderThumbnailImg');
        if (selectedTool.preview_image) {
            thumbImg.src = selectedTool.preview_image;
            thumbImg.alt = selectedTool.name;
            thumbWrap.classList.remove('hidden');
        } else {
            thumbWrap.classList.add('hidden');
        }

        // Collapse the main navigation sidebar for more workspace
        collapseSidebar();

        // Setup form fields
        setupInterfaceForm(selectedTool);

        // Reset preview
        resetPreview();
    }

    function backToDirectory() {
        document.getElementById('toolInterfaceView').classList.add('hidden');
        document.getElementById('directoryView').classList.remove('hidden');
        document.getElementById('toolInterfaceForm').reset();
        selectedTool = null;

        // Restore main navigation sidebar
        expandSidebar();
    }

    function collapseSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const main    = document.getElementById('appMain');
        const icon    = document.getElementById('sidebarToggleIcon');
        if (sidebar && !sidebar.classList.contains('sidebar-collapsed')) {
            sidebar.classList.add('sidebar-collapsed');
            if (main) main.style.marginLeft = '4.5rem';
            if (icon) icon.textContent = 'chevron_right';
        }
    }

    function expandSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const main    = document.getElementById('appMain');
        const icon    = document.getElementById('sidebarToggleIcon');
        if (sidebar) {
            sidebar.classList.remove('sidebar-collapsed');
            if (main) main.style.marginLeft = '';
            if (icon) icon.textContent = 'chevron_left';
        }
    }

    function resetPreview() {
        exitRegenLayout();
        const previewContent = document.getElementById('previewContent');
        previewContent.innerHTML = `
            <div class="text-center">
                <div class="inline-block p-5 bg-primary/10 rounded-xl mb-3">
                    <span class="material-symbols-outlined text-5xl text-primary">image</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Preview Area</h3>
                <p class="text-sm text-slate-400">Your generated image will appear here</p>
            </div>
        `;
        document.getElementById('previewControls').classList.add('hidden');
        document.getElementById('regeneratedSection').classList.add('hidden');
        document.getElementById('regeneratedGrid').innerHTML = '';
        window.currentImageUrl = null;
        window.currentImageData = null;
    }

    function setupInterfaceForm(tool) {
        const formContent = document.getElementById('interfaceFormContent');
        formContent.innerHTML = '';

        let sectionNumber = 1;

        // Prompt field
        if (tool.prompt_required) {
            const promptDiv = document.createElement('div');
            promptDiv.innerHTML = `
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">${sectionNumber}. Prompt</label>
                <textarea
                    id="interface_prompt"
                    name="prompt"
                    rows="2"
                    required
                    class="w-full bg-white/5 border-white/10 rounded-lg p-2.5 text-sm text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all resize-none"
                    placeholder="${escapeHtml(tool.prompt_placeholder || 'Enter your prompt...')}"
                ></textarea>
                ${tool.default_prompt ? `<p class="text-[10px] text-slate-500 mt-1">Default: ${escapeHtml(tool.default_prompt)}</p>` : ''}
            `;
            formContent.appendChild(promptDiv);
            sectionNumber++;
        }

        // Image uploads
        if (tool.image_uploads && tool.image_uploads.length > 0) {
            const uploadsWrapper = document.createElement('div');
            uploadsWrapper.innerHTML = `
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">
                    ${sectionNumber}. Image Uploads
                </label>
            `;

            const uploadsGrid = document.createElement('div');
            uploadsGrid.className = 'grid grid-cols-3 gap-3';

            tool.image_uploads.forEach(upload => {
                const uploadDiv = document.createElement('div');
                uploadDiv.className = 'space-y-1';
                uploadDiv.innerHTML = `
                    <label class="text-[10px] font-medium text-slate-300 block">
                        ${escapeHtml(upload.label || upload.name)}
                        ${upload.required ? '<span class="text-red-400">*</span>' : ''}
                    </label>
                    <div class="relative group">
                        <div class="aspect-[4/3] rounded-lg border-2 border-dashed border-white/10 hover:border-primary/50 transition-colors flex flex-col items-center justify-center bg-white/5 cursor-pointer overflow-hidden">
                            <input
                                type="file"
                                id="interface_upload_${upload.name}"
                                name="${upload.name}"
                                accept="image/*"
                                ${upload.required ? 'required' : ''}
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="previewUploadedImage(this, 'preview_${upload.name}')"
                            >
                            <div id="preview_${upload.name}" class="absolute inset-0 hidden">
                                <img class="w-full h-full object-cover" alt="Preview">
                            </div>
                            <div class="relative z-0 flex flex-col items-center pointer-events-none">
                                <span class="material-symbols-outlined text-primary text-lg mb-1">cloud_upload</span>
                                <span class="text-[10px] font-medium">Upload</span>
                                <span class="text-[8px] text-slate-500 mt-0.5">PNG/JPG</span>
                            </div>
                        </div>
                    </div>
                    ${upload.description ? `<p class="text-[9px] text-slate-500 mt-1 leading-tight">${escapeHtml(upload.description)}</p>` : ''}
                `;
                uploadsGrid.appendChild(uploadDiv);
            });

            uploadsWrapper.appendChild(uploadsGrid);
            formContent.appendChild(uploadsWrapper);
            sectionNumber++;
        }

        // Features
        if (tool.features && tool.features.length > 0) {
            const featuresDiv = document.createElement('div');
            featuresDiv.className = 'space-y-4';
            featuresDiv.innerHTML = `<label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">${sectionNumber}. Settings</label>`;

            tool.features.forEach(feature => {
                const featureDiv = document.createElement('div');
                featureDiv.className = 'space-y-1.5';

                let inputHtml = '';
                if (feature.type === 'select') {
                    inputHtml = `
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-medium">${escapeHtml(feature.label || feature.name)}</span>
                        </div>
                        <select
                            id="interface_feature_${feature.name}"
                            name="features[${feature.name}]"
                            class="w-full bg-white/5 border-white/10 rounded-lg p-2 text-sm text-white focus:ring-primary focus:border-primary transition-all"
                        >
                            ${(feature.options || []).map(opt => `
                                <option value="${escapeHtml(opt)}" ${opt === feature.default ? 'selected' : ''}>
                                    ${escapeHtml(opt)}
                                </option>
                            `).join('')}
                        </select>
                    `;
                } else if (feature.type === 'number' || feature.type === 'range') {
                    const value = feature.default || feature.min || 50;
                    inputHtml = `
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-medium">${escapeHtml(feature.label || feature.name)}</span>
                            <span class="text-[10px] text-primary font-bold" id="interface_feature_${feature.name}_value">${value}${feature.unit || ''}</span>
                        </div>
                        <input
                            type="range"
                            id="interface_feature_${feature.name}"
                            name="features[${feature.name}]"
                            value="${value}"
                            min="${feature.min || 0}"
                            max="${feature.max || 100}"
                            class="w-full h-1 bg-white/10 rounded-lg appearance-none cursor-pointer accent-primary"
                            oninput="document.getElementById('interface_feature_${feature.name}_value').textContent = this.value + '${feature.unit || ''}'"
                        >
                    `;
                } else {
                    inputHtml = `
                        <label class="text-[10px] font-medium block mb-1">${escapeHtml(feature.label || feature.name)}</label>
                        <input
                            type="text"
                            id="interface_feature_${feature.name}"
                            name="features[${feature.name}]"
                            value="${feature.default || ''}"
                            placeholder="${escapeHtml(feature.placeholder || '')}"
                            class="w-full bg-white/5 border-white/10 rounded-lg p-2 text-sm text-white placeholder:text-slate-600 focus:ring-primary focus:border-primary transition-all"
                        >
                    `;
                }

                featureDiv.innerHTML = inputHtml;
                if (feature.description) {
                    featureDiv.innerHTML += `<p class="text-[9px] text-slate-500 mt-1">${escapeHtml(feature.description)}</p>`;
                }
                featuresDiv.appendChild(featureDiv);
            });

            formContent.appendChild(featuresDiv);
        }
    }

    function previewUploadedImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
                preview.previousElementSibling.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
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

    // Handle tool interface form submission
    document.getElementById('toolInterfaceForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('interfaceGenerateBtn');
        const statusEl = document.getElementById('interfaceFormStatus');
        const previewContent = document.getElementById('previewContent');

        btn.disabled = true;
        statusEl.className = 'p-3 rounded-lg bg-primary/10 border border-primary/20 text-primary text-xs';
        statusEl.classList.remove('hidden');
        statusEl.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined animate-spin text-sm">autorenew</span>
                <span>Generating...</span>
            </div>
        `;

        // Show loading in preview
        previewContent.innerHTML = `
            <div class="text-center">
                <div class="inline-block p-5 bg-primary/10 rounded-xl mb-3 animate-pulse">
                    <span class="material-symbols-outlined text-5xl text-primary animate-spin">autorenew</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Generating...</h3>
                <p class="text-sm text-slate-400">Your image is being created</p>
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

            statusEl.className = 'p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs';
            statusEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span>Success! Credits: ${data.credits_used || 0}</span>
                </div>
            `;

            // Display generated image in preview
            if (data.image_url) {
                displayInPreview(data);
            }
        } catch (error) {
            statusEl.className = 'p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs';
            statusEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span>Error: ${error.message}</span>
                </div>
            `;

            // Reset preview on error
            resetPreview();
        } finally {
            btn.disabled = false;
        }
    });

    function displayInPreview(data) {
        exitRegenLayout();
        const previewContent = document.getElementById('previewContent');
        const previewControls = document.getElementById('previewControls');

        previewContent.innerHTML = `
            <div class="relative w-full max-w-2xl px-4 pt-4">
                <div class="relative w-full aspect-square rounded-2xl overflow-hidden shadow-[0_8px_32px_rgba(0,0,0,0.4)] ring-1 ring-white/10">
                    <img src="${escapeHtml(data.image_url)}" alt="Generated image" class="w-full h-full object-contain bg-black/20" id="currentPreviewImage">
                </div>
            </div>
        `;

        previewControls.classList.remove('hidden');

        // Reset regenerated section for new generation
        document.getElementById('regeneratedSection').classList.add('hidden');
        document.getElementById('regeneratedGrid').innerHTML = '';

        // Store current image URL and data for download/share/regenerate
        window.currentImageUrl = data.image_url;
        window.currentImageData = data;
    }

    function downloadCurrentImage() {
        if (window.currentImageUrl) {
            const link = document.createElement('a');
            link.href = window.currentImageUrl;
            link.download = `generated-image-${Date.now()}.png`;
            link.click();
        }
    }

    function shareCurrentImage() {
        if (window.currentImageUrl && navigator.share) {
            navigator.share({
                title: 'Generated Image',
                text: 'Check out this AI-generated image!',
                url: window.currentImageUrl
            }).catch(() => {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.currentImageUrl);
                alert('Image URL copied to clipboard!');
            });
        } else if (window.currentImageUrl) {
            navigator.clipboard.writeText(window.currentImageUrl);
            alert('Image URL copied to clipboard!');
        }
    }

    // Handle form submission (modal - kept for backwards compatibility)
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

    // ─── Dev / Test Helper ───────────────────────────────────────────────────
    function testRegenerateUI() {
        // Ensure we're in the interface view
        document.getElementById('directoryView').classList.add('hidden');
        const interfaceView = document.getElementById('toolInterfaceView');
        interfaceView.classList.remove('hidden');

        // Set a mock tool title if none is set
        const titleEl = document.getElementById('toolInterfaceTitle');
        if (!titleEl.textContent.trim()) {
            titleEl.textContent = 'Test Tool';
        }

        // Mock a successful generation response with a real placeholder image
        const mockData = {
            success: true,
            image_url: 'https://picsum.photos/seed/regen1/800/800',
            credits_used: 2,
            image_data: { id: 9999, prompt: 'Test prompt for UI preview' },
        };

        // Show the image in preview (also resets regen section + shows controls bar)
        displayInPreview(mockData);

        // Open the offcanvas after the image renders
        setTimeout(() => openRegenerateCanvas(), 250);
    }

    function testRegeneratedResults() {
        // Simulate what the API returns with 3 variations
        displayRegeneratedImages({
            success: true,
            images: [
                { url: 'https://picsum.photos/seed/var1/800/800' },
                { url: 'https://picsum.photos/seed/var2/800/800' },
                { url: 'https://picsum.photos/seed/var3/800/800' },
            ],
        });
    }

    // ─── Regen layout helpers ────────────────────────────────────────────────
    function enterRegenLayout() {
        const section = document.getElementById('previewRightSection');
        if (!section) return;
        section.classList.add('regen-active');

        // Inject source meta label next to thumbnail
        const previewContent = document.getElementById('previewContent');
        if (previewContent && !previewContent.querySelector('.regen-source-meta')) {
            const meta = document.createElement('div');
            meta.className = 'regen-source-meta';
            meta.innerHTML = `
                <span class="text-[9px] font-bold text-primary/60 uppercase tracking-widest">Source</span>
                <span class="text-[10px] text-slate-500 regen-meta-status">Generating variations…</span>
            `;
            previewContent.appendChild(meta);
        }
    }

    function updateRegenLayoutStatus(text) {
        const el = document.querySelector('.regen-meta-status');
        if (el) el.textContent = text;
    }

    function exitRegenLayout() {
        const section = document.getElementById('previewRightSection');
        if (!section) return;
        section.classList.remove('regen-active');
        document.querySelectorAll('.regen-source-meta').forEach(el => el.remove());
    }

    // ─── Regenerate Offcanvas ────────────────────────────────────────────────
    let selectedRegenCount = 1;

    function openRegenerateCanvas() {
        if (!window.currentImageUrl) return;

        // Populate source image
        document.getElementById('regenSourceImage').src = window.currentImageUrl;

        // Reset form
        document.getElementById('regenPrompt').value = '';
        document.querySelectorAll('.regen-checkbox').forEach(cb => cb.checked = false);
        selectRegenCount(1);

        const statusEl = document.getElementById('regenStatus');
        statusEl.classList.add('hidden');

        // Open offcanvas
        const offcanvas = document.getElementById('regenOffcanvas');
        const backdrop = document.getElementById('regenBackdrop');
        offcanvas.classList.add('open');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Focus the prompt textarea after the slide-in transition finishes
        setTimeout(() => document.getElementById('regenPrompt').focus(), 320);
    }

    function closeRegenerateCanvas() {
        const offcanvas = document.getElementById('regenOffcanvas');
        const backdrop = document.getElementById('regenBackdrop');
        offcanvas.classList.remove('open');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function selectRegenCount(n) {
        selectedRegenCount = n;
        document.querySelectorAll('.regen-count-btn').forEach(btn => btn.classList.remove('active-count'));
        document.getElementById('regenCount' + n).classList.add('active-count');
    }

    const REGEN_BTN_DEFAULT_HTML = `<span class="material-symbols-outlined text-lg group-hover:animate-pulse">auto_fix_high</span> GENERATE VARIATIONS`;

    async function submitRegenerate() {
        const imageData = window.currentImageData;
        const imageId = imageData?.image_data?.id;
        const statusEl = document.getElementById('regenStatus');

        if (!imageId) {
            statusEl.className = 'p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs';
            statusEl.classList.remove('hidden');
            statusEl.innerHTML = '<div class="flex items-center gap-2"><span class="material-symbols-outlined text-sm">error</span><span>Image ID not available. Try regenerating after a fresh generation.</span></div>';
            return;
        }

        const btn = document.getElementById('regenGenerateBtn');
        const n = selectedRegenCount;

        // ── Loading state ────────────────────────────────────────────────────
        document.getElementById('regenOffcanvasPanel').classList.add('offcanvas-generating');
        document.getElementById('regenFormOverlayText').textContent = `Generating ${n} variation${n > 1 ? 's' : ''}…`;

        btn.disabled = true;
        btn.innerHTML = `
            <span class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Generating ${n} Variation${n > 1 ? 's' : ''}…
            </span>
        `;

        // Skeleton shimmer cards in status area
        const cols = n === 1 ? 'grid-cols-1' : 'grid-cols-2';
        statusEl.className = 'rounded-lg text-xs';
        statusEl.classList.remove('hidden');
        statusEl.innerHTML = `
            <div class="grid ${cols} gap-2 mb-3">
                ${Array.from({length: n}).map((_, i) => `
                    <div class="regen-skeleton shimmer-sweep aspect-square flex flex-col items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-primary/40 text-2xl animate-pulse">auto_fix_high</span>
                        <span class="text-[9px] text-slate-600">Variation ${i + 1}</span>
                    </div>
                `).join('')}
            </div>
            <p class="text-center text-[10px] text-slate-500 animate-pulse">AI is crafting your variations…</p>
        `;

        // Scroll offcanvas body so the skeleton cards are immediately visible
        const formBody = document.getElementById('regenFormBody');
        requestAnimationFrame(() => {
            document.getElementById('regenStatus').scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        });

        // Shift layout: source image → thumbnail, variations fill the stage
        enterRegenLayout();

        // Also show skeleton in the main preview results area immediately
        showRegenSkeletons(n);

        const payload = {
            image_id: imageId,
            count: n,
            modification_prompt: document.getElementById('regenPrompt').value || null,
            layout_change: document.getElementById('regenLayoutChange').checked,
            style_change: document.getElementById('regenStyleChange').checked,
            color_change: document.getElementById('regenColorChange').checked,
            lighting_change: document.getElementById('regenLightingChange').checked,
            detail_enhance: document.getElementById('regenDetailEnhance').checked,
        };

        try {
            const response = await fetch('{{ route("api.nano.visual.tools.regenerate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Regeneration failed');
            }

            // Success state
            statusEl.className = 'p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs';
            statusEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    <span>Done! ${n} variation${n > 1 ? 's' : ''} ready below.</span>
                </div>
            `;

            // Replace skeletons with real images, then close
            displayRegeneratedImages(data);
            updateRegenLayoutStatus(`${n} variation${n > 1 ? 's' : ''} ready`);
            setTimeout(() => closeRegenerateCanvas(), 1000);

        } catch (error) {
            clearRegenSkeletons();
            statusEl.className = 'p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs';
            statusEl.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span>Error: ${escapeHtml(error.message)}</span>
                </div>
            `;
        } finally {
            btn.disabled = false;
            btn.innerHTML = REGEN_BTN_DEFAULT_HTML;
            document.getElementById('regenOffcanvasPanel').classList.remove('offcanvas-generating');
        }
    }

    function showRegenSkeletons(n) {
        const section = document.getElementById('regeneratedSection');
        const grid = document.getElementById('regeneratedGrid');
        const timestamp = document.getElementById('regenTimestamp');

        // Clear old skeletons/results and mark with a temp id
        grid.innerHTML = '';
        timestamp.textContent = 'Generating…';

        for (let i = 0; i < n; i++) {
            const el = document.createElement('div');
            el.className = 'regen-skeleton shimmer-sweep regen-skeleton-placeholder';
            el.style.aspectRatio = '1';
            el.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center gap-2 p-4">
                    <span class="material-symbols-outlined text-primary/30 text-4xl animate-pulse">auto_fix_high</span>
                    <span class="text-[10px] text-slate-600 animate-pulse">Variation ${i + 1}</span>
                    <div class="w-16 h-1 rounded-full bg-primary/10 overflow-hidden mt-1">
                        <div class="h-full bg-primary/40 rounded-full" style="animation: shimmer 1.6s infinite; width: 40%"></div>
                    </div>
                </div>
            `;
            grid.appendChild(el);
        }

        section.classList.remove('hidden');
    }

    function clearRegenSkeletons() {
        document.querySelectorAll('.regen-skeleton-placeholder').forEach(el => el.remove());
        const grid = document.getElementById('regeneratedGrid');
        if (grid.children.length === 0) {
            document.getElementById('regeneratedSection').classList.add('hidden');
        }
    }

    function displayRegeneratedImages(data) {
        const section = document.getElementById('regeneratedSection');
        const grid = document.getElementById('regeneratedGrid');
        const timestamp = document.getElementById('regenTimestamp');

        // Update timestamp
        const now = new Date();
        timestamp.textContent = 'Generated at ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Collect images from response (support single or array)
        const images = [];
        if (data.images && Array.isArray(data.images)) {
            images.push(...data.images);
        } else if (data.image_url) {
            images.push({ url: data.image_url, prompt: data.image_data?.prompt || '' });
        }

        if (images.length === 0) return;

        // Remove any skeleton placeholders first
        grid.querySelectorAll('.regen-skeleton-placeholder').forEach(el => el.remove());

        const fragment = document.createDocumentFragment();
        images.forEach((img, idx) => {
            const url = img.url || img.image_url || img;
            const card = document.createElement('div');
            card.className = 'regen-card group';
            card.innerHTML = `
                <div class="aspect-square overflow-hidden rounded-t-xl bg-black/20">
                    <img src="${escapeHtml(url)}" alt="Variation ${idx + 1}" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                </div>
                <div class="p-3 flex items-center justify-between gap-2">
                    <span class="text-[10px] text-slate-500 font-medium">Variation ${idx + 1}</span>
                    <div class="flex gap-1">
                        <button onclick="downloadRegenImage('${escapeHtml(url)}', ${idx + 1})" class="p-1.5 hover:bg-white/5 rounded-md text-slate-400 hover:text-white transition-colors" title="Download">
                            <span class="material-symbols-outlined text-base">download</span>
                        </button>
                        <button onclick="shareRegenImage('${escapeHtml(url)}')" class="p-1.5 hover:bg-white/5 rounded-md text-slate-400 hover:text-white transition-colors" title="Share">
                            <span class="material-symbols-outlined text-base">share</span>
                        </button>
                    </div>
                </div>
            `;
            fragment.appendChild(card);
        });

        // If this is the first batch, clear any previous regen results
        // (prepend new batch before existing ones)
        grid.insertBefore(fragment, grid.firstChild);

        // Reveal section with smooth scroll
        section.classList.remove('hidden');
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function downloadRegenImage(url, index) {
        const link = document.createElement('a');
        link.href = url;
        link.download = `regen-variation-${index}-${Date.now()}.png`;
        link.click();
    }

    function shareRegenImage(url) {
        if (navigator.share) {
            navigator.share({ title: 'Regenerated Image', url }).catch(() => {
                navigator.clipboard.writeText(url);
                alert('Image URL copied to clipboard!');
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Image URL copied to clipboard!');
        }
    }
</script>
@endpush
