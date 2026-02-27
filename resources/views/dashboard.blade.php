@extends('layouts.app')

@section('title', 'Dashboard - Clever Creator AI')

@section('content')
<!-- Hero Section -->
<section class="relative rounded-3xl overflow-hidden p-12">
<div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-secondary/10 to-transparent z-0"></div>
<div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 z-0"></div>
<div class="relative z-10 max-w-2xl">
<h2 class="text-5xl font-black tracking-tight text-white mb-4">Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">{{ auth()->user()->name ?? 'User' }}!</span></h2>
<p class="text-lg text-slate-400 font-medium">What will you imagine today? Your creative tools are ready and waiting.</p>
</div>
</section>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-primary hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Images Generated</p>
<p class="text-3xl font-black text-white">342</p>
</div>
<div class="p-3 bg-primary/10 rounded-xl text-primary">
<span class="material-symbols-outlined text-3xl">image</span>
</div>
</div>
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-secondary hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Available Tokens</p>
<p class="text-3xl font-black text-white">178</p>
</div>
<div class="p-3 bg-secondary/10 rounded-xl text-secondary">
<span class="material-symbols-outlined text-3xl">toll</span>
</div>
</div>
<div class="glass p-6 rounded-2xl flex items-center justify-between border-l-4 border-l-emerald-500 hover:translate-y-[-4px] transition-transform">
<div>
<p class="text-sm text-slate-400 font-medium mb-1">Total Likes</p>
<p class="text-3xl font-black text-white">1.2k</p>
</div>
<div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500">
<span class="material-symbols-outlined text-3xl">favorite</span>
</div>
</div>
</div>

<!-- Quick Start Prompt -->
<section class="glass p-8 rounded-3xl border border-primary/20 bg-gradient-to-b from-white/[0.02] to-transparent">
    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">rocket_launch</span>
        Quick Start
        <span class="ml-auto text-[10px] px-2 py-1 bg-primary/10 text-primary rounded-full font-bold uppercase tracking-widest border border-primary/20">Nano Banana AI</span>
    </h3>
    <div class="relative">
        <textarea
            id="quickPrompt"
            class="w-full bg-background-dark/50 border border-white/10 rounded-2xl p-6 pr-36 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all resize-none"
            placeholder="Describe the image you want to create... (e.g., 'Cyberpunk city street at night in 8k resolution, cinematic lighting, neon blue and pink')"
            rows="3"
            onkeydown="if(event.ctrlKey && event.key==='Enter') quickGenerate()"
        ></textarea>
        <button
            id="quickGenerateBtn"
            onclick="quickGenerate()"
            data-tooltip="Ctrl+Enter to generate" data-tooltip-pos="top"
            class="absolute bottom-4 right-4 bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed text-white px-5 py-3 rounded-xl font-bold flex items-center gap-2 transition-all shadow-lg shadow-primary/20"
        >
            <span id="quickBtnText">Generate</span>
            <span id="quickBtnIcon" class="material-symbols-outlined text-lg">auto_fix</span>
        </button>
    </div>
    <div class="flex flex-wrap gap-3 mt-4 items-center">
        <span class="text-xs text-slate-500 shrink-0">Presets:</span>
        <button onclick="setPreset('Photorealistic portrait in golden hour lighting, 8k, ultra detailed, cinematic')" class="text-[10px] px-3 py-1 bg-white/5 hover:bg-primary/10 hover:text-primary hover:border-primary/30 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Photorealistic</button>
        <button onclick="setPreset('Oil painting of a serene mountain landscape with lake, impressionist style, vibrant colors')" class="text-[10px] px-3 py-1 bg-white/5 hover:bg-primary/10 hover:text-primary hover:border-primary/30 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Oil Painting</button>
        <button onclick="setPreset('Cyberpunk city street at night in 8k resolution, cinematic lighting, neon blue and pink, rain reflections')" class="text-[10px] px-3 py-1 bg-white/5 hover:bg-primary/10 hover:text-primary hover:border-primary/30 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">Cyberpunk</button>
        <button onclick="setPreset('3D render of a futuristic spaceship in deep space, volumetric lighting, ultra realistic, 4k')" class="text-[10px] px-3 py-1 bg-white/5 hover:bg-primary/10 hover:text-primary hover:border-primary/30 rounded-full text-slate-400 border border-white/5 transition-colors uppercase font-bold tracking-wide">3D Render</button>
        <span class="ml-auto text-[10px] text-slate-600">Ctrl+Enter to generate</span>
    </div>
</section>

<!-- Generation Result Panel -->
<section id="quickResultSection" class="hidden">
    <div class="glass rounded-3xl border border-primary/30 overflow-hidden">

        <!-- Loading State -->
        <div id="quickLoadingState" class="p-16 flex flex-col items-center gap-8">
            <div class="relative w-28 h-28">
                <div class="absolute inset-0 rounded-full border-4 border-primary/10 animate-ping" style="animation-duration:2s"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-primary border-r-primary/40 border-b-transparent border-l-transparent animate-spin"></div>
                <div class="absolute inset-2 rounded-full border-4 border-t-secondary border-r-transparent border-b-transparent border-l-secondary/40 animate-spin" style="animation-direction:reverse;animation-duration:1.5s"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-3xl">auto_fix</span>
                </div>
            </div>
            <div class="text-center space-y-2">
                <p class="text-white font-black text-2xl">Generating your image</p>
                <p class="text-slate-500 text-sm">Nano Banana AI is crafting your vision...</p>
                <div class="flex items-center justify-center gap-1 mt-4">
                    <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay:0s"></div>
                    <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay:0.15s"></div>
                    <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay:0.3s"></div>
                </div>
            </div>
        </div>

        <!-- Result State -->
        <div id="quickResultContent" class="hidden">
            <div class="flex flex-col lg:flex-row min-h-[420px]">
                <!-- Image Panel -->
                <div class="lg:w-[55%] relative overflow-hidden bg-black group/img cursor-zoom-in" onclick="openLightbox()">
                    <img
                        id="quickResultImg"
                        src=""
                        alt=""
                        class="w-full h-full object-cover min-h-80 lg:min-h-[420px] transition-all duration-500 group-hover/img:scale-105"
                        style="opacity:0"
                        onload="this.style.opacity='1'"
                    >
                    <!-- Gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-background-dark/60 hidden lg:block pointer-events-none"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-background-dark/80 via-transparent to-transparent lg:hidden pointer-events-none"></div>
                    <!-- Success badge -->
                    <div class="absolute top-4 left-4 flex items-center gap-2 bg-emerald-500/20 backdrop-blur-sm border border-emerald-500/30 text-emerald-400 px-3 py-1.5 rounded-full text-xs font-bold">
                        <span class="material-symbols-outlined text-sm" style="font-size:14px">check_circle</span>
                        Generated
                    </div>
                    <!-- Zoom hint -->
                    <div class="absolute bottom-4 right-4 flex items-center gap-1.5 bg-black/50 backdrop-blur-sm border border-white/10 text-white/70 px-3 py-1.5 rounded-full text-xs opacity-0 group-hover/img:opacity-100 transition-opacity pointer-events-none">
                        <span class="material-symbols-outlined" style="font-size:14px">zoom_in</span>
                        Click to expand
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="lg:w-[45%] p-8 flex flex-col justify-between gap-6 bg-gradient-to-br from-surface-dark to-background-dark">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] px-2.5 py-1 bg-primary/15 text-primary rounded-full font-bold uppercase tracking-widest border border-primary/20">Nano Banana AI</span>
                            <span class="text-[10px] text-slate-600">gemini-2.5-flash-image</span>
                        </div>
                        <div>
                            <h4 class="text-white font-black text-xl mb-1">Generation Complete</h4>
                            <p class="text-slate-500 text-xs mb-3">Your image has been saved to gallery</p>
                        </div>
                        <div class="bg-white/[0.03] border border-white/5 rounded-xl p-4">
                            <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest mb-2">Prompt</p>
                            <p id="quickResultPrompt" class="text-slate-300 text-sm leading-relaxed"></p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a
                            id="quickDownloadBtn"
                            href="#"
                            target="_blank"
                            data-tooltip="Save image to device" data-tooltip-pos="top"
                            class="flex items-center justify-center gap-2 py-3 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/20"
                        >
                            <span class="material-symbols-outlined text-lg">download</span>
                            Download Image
                        </a>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('gallery') }}" data-tooltip="View all your images" data-tooltip-pos="top" class="flex items-center justify-center gap-1.5 py-2.5 bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-bold rounded-xl transition-all border border-white/10">
                                <span class="material-symbols-outlined text-base">photo_library</span>
                                Gallery
                            </a>
                            <button onclick="resetQuickStart()" data-tooltip="Start a fresh generation" data-tooltip-pos="top" class="flex items-center justify-center gap-1.5 py-2.5 bg-secondary/10 hover:bg-secondary/20 text-secondary text-sm font-bold rounded-xl transition-all border border-secondary/20">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                New Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="quickErrorState" class="hidden p-16 flex flex-col items-center gap-6">
            <div class="w-20 h-20 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-400 text-4xl">error_outline</span>
            </div>
            <div class="text-center space-y-2">
                <p class="text-white font-black text-xl">Generation Failed</p>
                <p id="quickErrorMsg" class="text-slate-500 text-sm max-w-sm"></p>
            </div>
            <button onclick="resetQuickStart()" class="flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/15 text-white font-bold rounded-xl transition-all border border-white/10">
                <span class="material-symbols-outlined text-base">refresh</span>
                Try Again
            </button>
        </div>
    </div>
</section>

<!-- Recent Generations -->
<section>
<div class="flex justify-between items-end mb-8">
<div>
<h3 class="text-2xl font-black text-white">Recent Generations</h3>
<p class="text-slate-400 text-sm">Your latest creative outputs</p>
</div>
<a href="{{ route('gallery') }}" class="text-primary text-sm font-bold flex items-center gap-1 hover:underline">
                            View all gallery
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>

<!-- Skeleton loaders -->
<div id="recentSkeletons" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @for ($i = 0; $i < 4; $i++)
    <div class="rounded-2xl overflow-hidden glass aspect-square border-none animate-pulse bg-white/5"></div>
    @endfor
</div>

<!-- Dynamic grid -->
<div id="recentGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 hidden"></div>

<!-- Empty state -->
<div id="recentEmpty" class="hidden flex flex-col items-center justify-center py-16 text-center">
    <span class="material-symbols-outlined text-5xl text-slate-600 mb-3">image_not_supported</span>
    <p class="text-slate-400 text-sm">No images yet. Generate your first one above!</p>
</div>
</section>
@endsection

@push('modals')
<!-- Fullscreen Lightbox Modal -->
<div
    id="lightboxModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    onclick="if(event.target===this) closeLightbox()"
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>

    <!-- Image container -->
    <div class="relative z-10 max-w-[92vw] max-h-[92vh] flex flex-col items-center gap-4">
        <img
            id="lightboxImg"
            src=""
            alt=""
            class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl shadow-black/80 object-contain"
            style="transition: opacity 0.2s ease"
        >
        <!-- Toolbar -->
        <div class="flex items-center gap-3">
            <a
                id="lightboxDownload"
                href="#"
                target="_blank"
                data-tooltip="Download this image" data-tooltip-pos="top"
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-primary/20"
            >
                <span class="material-symbols-outlined text-base">download</span>
                Download
            </a>
            <button
                onclick="closeLightbox()"
                class="flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-all border border-white/10"
            >
                <span class="material-symbols-outlined text-base">close</span>
                Close
            </button>
        </div>
    </div>

    <!-- Close button (top-right) -->
    <button
        onclick="closeLightbox()"
        data-tooltip="Close (Esc)" data-tooltip-pos="left"
        class="absolute top-5 right-5 z-20 w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 text-white rounded-full transition-all border border-white/10"
    >
        <span class="material-symbols-outlined">close</span>
    </button>
</div>
@endpush

@push('scripts')
<script>
async function quickGenerate() {
    const prompt = document.getElementById('quickPrompt').value.trim();
    if (!prompt) {
        document.getElementById('quickPrompt').focus();
        document.getElementById('quickPrompt').classList.add('ring-2', 'ring-red-500/50', 'border-red-500/50');
        setTimeout(() => document.getElementById('quickPrompt').classList.remove('ring-2', 'ring-red-500/50', 'border-red-500/50'), 2000);
        return;
    }

    const resultSection  = document.getElementById('quickResultSection');
    const loadingState   = document.getElementById('quickLoadingState');
    const resultContent  = document.getElementById('quickResultContent');
    const errorState     = document.getElementById('quickErrorState');
    const btn            = document.getElementById('quickGenerateBtn');
    const btnText        = document.getElementById('quickBtnText');
    const btnIcon        = document.getElementById('quickBtnIcon');

    // Show result panel with loading
    resultSection.classList.remove('hidden');
    loadingState.classList.remove('hidden');
    resultContent.classList.add('hidden');
    errorState.classList.add('hidden');

    // Disable button
    btn.disabled = true;
    btnText.textContent = 'Generating...';
    btnIcon.textContent = 'hourglass_empty';

    // Smooth scroll to result
    setTimeout(() => resultSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/dashboard/image', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ prompt }),
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Image generation failed. Please try again.');
        }

        // Populate result
        const img = document.getElementById('quickResultImg');
        img.style.opacity = '0';
        img.src = data.image_url;
        img.alt = prompt;

        document.getElementById('quickResultPrompt').textContent = data.prompt || prompt;
        document.getElementById('quickDownloadBtn').href = data.image_url;

        loadingState.classList.add('hidden');
        resultContent.classList.remove('hidden');

        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        loadingState.classList.add('hidden');
        document.getElementById('quickErrorMsg').textContent = err.message;
        errorState.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Generate';
        btnIcon.textContent = 'auto_fix';
    }
}

function setPreset(text) {
    const ta = document.getElementById('quickPrompt');
    ta.value = text;
    ta.focus();
    ta.classList.remove('ring-2', 'ring-red-500/50', 'border-red-500/50');
}

function openLightbox() {
    const src = document.getElementById('quickResultImg').src;
    if (!src) return;
    const modal = document.getElementById('lightboxModal');
    const img   = document.getElementById('lightboxImg');
    const dl    = document.getElementById('lightboxDownload');
    img.src  = src;
    img.alt  = document.getElementById('quickResultImg').alt;
    dl.href  = src;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});

function resetQuickStart() {
    document.getElementById('quickResultSection').classList.add('hidden');
    document.getElementById('quickPrompt').value = '';
    document.getElementById('quickPrompt').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Recent Generations ────────────────────────────────────────
(function loadRecentGenerations() {
    fetch('/api/gallery?per_page=4&page=1', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('recentSkeletons').classList.add('hidden');

        if (!data.success || !data.data || data.data.length === 0) {
            document.getElementById('recentEmpty').classList.remove('hidden');
            document.getElementById('recentEmpty').classList.add('flex');
            return;
        }

        var grid = document.getElementById('recentGrid');
        grid.classList.remove('hidden');

        data.data.forEach(function(image) {
            var card = document.createElement('div');
            card.className = 'group relative rounded-2xl overflow-hidden glass aspect-square border-none';

            var img = document.createElement('img');
            img.className = 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500';
            img.src    = image.image_url;
            img.alt    = image.prompt || 'Generated image';
            img.loading = 'lazy';

            var overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 bg-gradient-to-t from-background-dark/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-5 flex flex-col justify-end';

            var promptEl = document.createElement('p');
            promptEl.className = 'text-white text-sm font-bold mb-3 truncate';
            promptEl.textContent = image.prompt || 'Generated image';

            var btn = document.createElement('a');
            btn.href = '{{ route('gallery') }}';
            btn.className = 'w-full py-2 bg-primary text-white text-xs font-bold rounded-lg flex items-center justify-center gap-2';
            btn.innerHTML = '<span class="material-symbols-outlined text-base">high_quality</span> View in Gallery';

            overlay.appendChild(promptEl);
            overlay.appendChild(btn);
            card.appendChild(img);
            card.appendChild(overlay);
            grid.appendChild(card);
        });
    })
    .catch(function() {
        document.getElementById('recentSkeletons').classList.add('hidden');
        document.getElementById('recentEmpty').classList.remove('hidden');
        document.getElementById('recentEmpty').classList.add('flex');
    });
})();
</script>
@endpush
