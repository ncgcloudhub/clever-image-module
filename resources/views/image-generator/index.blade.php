@extends('layouts.app')

@section('title', 'Image Generator — Clever Creator')

@push('styles')
<style>
    /* ── Kill default padding ─────────────────────────── */
    #appMain > div.p-10 { padding: 0 !important; }

    /* ── Root layout ──────────────────────────────────── */
    #genWrap {
        display: flex;
        width: 100%;
        height: calc(100vh - 5rem);
        overflow: hidden;
    }

    /* ── Settings panel (left) ───────────────────────── */
    #genSettings {
        width: 300px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: rgba(13, 16, 22, 0.95);
        border-right: 1px solid rgba(255,255,255,0.06);
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.08) transparent;
    }
    #genSettings::-webkit-scrollbar { width: 3px; }
    #genSettings::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 99px; }

    .settings-section {
        padding: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .settings-label {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: 0.625rem;
    }

    /* Provider cards */
    .provider-card {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.625rem;
        border-radius: 0.625rem;
        border: 1px solid rgba(255,255,255,0.06);
        cursor: pointer;
        transition: all 0.15s;
        background: transparent;
        color: #94a3b8;
        font-size: 0.78rem;
        font-weight: 500;
        margin-bottom: 0.375rem;
    }
    .provider-card:last-child { margin-bottom: 0; }
    .provider-card:hover {
        border-color: rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.03);
        color: #e2e8f0;
    }
    .provider-card.active {
        border-color: rgba(19,164,236,0.4);
        background: rgba(19,164,236,0.08);
        color: #13a4ec;
    }
    .provider-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Select / inputs */
    .gen-select, .gen-input, .gen-textarea {
        width: 100%;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 0.625rem;
        color: #e2e8f0;
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
        outline: none;
        transition: border-color 0.15s;
        font-family: inherit;
    }
    .gen-select:focus, .gen-input:focus, .gen-textarea:focus {
        border-color: rgba(19,164,236,0.35);
    }
    .gen-select option { background: #161b22; }
    .gen-textarea { resize: none; min-height: 80px; max-height: 140px; line-height: 1.5; }
    .gen-textarea::placeholder { color: rgba(100,116,139,0.5); }

    /* Resolution grid */
    #resolutionGrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.375rem;
    }
    .res-btn {
        padding: 0.375rem 0.25rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(255,255,255,0.07);
        background: transparent;
        color: #64748b;
        font-size: 0.65rem;
        font-weight: 500;
        cursor: pointer;
        text-align: center;
        transition: all 0.15s;
        line-height: 1.2;
    }
    .res-btn:hover { border-color: rgba(255,255,255,0.15); color: #94a3b8; }
    .res-btn.active {
        border-color: rgba(19,164,236,0.5);
        background: rgba(19,164,236,0.1);
        color: #13a4ec;
    }

    /* Slider */
    .gen-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 4px;
        border-radius: 99px;
        background: rgba(255,255,255,0.1);
        outline: none;
    }
    .gen-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #13a4ec;
        cursor: pointer;
        border: 2px solid rgba(255,255,255,0.15);
    }

    /* Num images */
    .num-img-row { display: flex; align-items: center; gap: 0.5rem; }
    .num-btn {
        width: 28px; height: 28px;
        border-radius: 0.5rem;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        color: #94a3b8;
        cursor: pointer;
        font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .num-btn:hover { border-color: rgba(19,164,236,0.4); color: #13a4ec; }

    /* Generate button */
    #genBtn {
        width: 100%;
        padding: 0.75rem;
        border-radius: 0.875rem;
        border: none;
        background: linear-gradient(135deg, #13a4ec, #8b5cf6);
        color: white;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    #genBtn:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
    #genBtn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    /* Cost badge */
    #costBadge {
        font-size: 0.7rem;
        color: #64748b;
        text-align: center;
        margin-top: 0.5rem;
    }
    #costBadge span { color: #13a4ec; font-weight: 700; }

    /* Ref image upload */
    .ref-upload-zone {
        border: 1.5px dashed rgba(255,255,255,0.12);
        border-radius: 0.75rem;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s;
        color: #475569;
        font-size: 0.78rem;
    }
    .ref-upload-zone:hover { border-color: rgba(19,164,236,0.4); color: #94a3b8; }
    .ref-preview-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-top: 0.5rem;
    }
    .ref-thumb {
        width: 48px; height: 48px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 1px solid rgba(255,255,255,0.08);
        position: relative;
    }
    .ref-thumb-wrap { position: relative; }
    .ref-thumb-del {
        position: absolute;
        top: -4px; right: -4px;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: #ef4444;
        border: none;
        color: white;
        font-size: 9px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        line-height: 1;
    }

    /* Toggle switch */
    .toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .toggle-label { font-size: 0.78rem; color: #94a3b8; }
    .toggle-switch {
        position: relative;
        width: 36px; height: 20px;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-track {
        position: absolute;
        inset: 0;
        border-radius: 99px;
        background: rgba(255,255,255,0.1);
        cursor: pointer;
        transition: background 0.2s;
    }
    .toggle-track::after {
        content: '';
        position: absolute;
        left: 2px; top: 2px;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: white;
        transition: transform 0.2s;
    }
    .toggle-switch input:checked + .toggle-track { background: #13a4ec; }
    .toggle-switch input:checked + .toggle-track::after { transform: translateX(16px); }

    /* ── Main stage (center) ─────────────────────────── */
    #genStage {
        flex: 1;
        min-width: 0;
        position: relative;
        overflow: hidden;
        background: #09090b;
        background-image:
            linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
        background-size: 40px 40px;
    }

    /* Top-left info bar */
    #genToolbar {
        position: absolute;
        top: 1rem; left: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(22,27,34,0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.875rem;
        padding: 0.5rem 0.875rem;
        z-index: 10;
        font-size: 0.7rem;
        color: #64748b;
    }
    #genToolbar .toolbar-sep { width: 1px; height: 14px; background: rgba(255,255,255,0.08); }

    /* Progress bar */
    #genProgressWrap {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: rgba(255,255,255,0.05);
        display: none;
        z-index: 20;
    }
    #genProgressBar {
        height: 100%;
        background: linear-gradient(90deg, #13a4ec, #8b5cf6);
        border-radius: 99px;
        transition: width 0.4s ease;
        width: 0%;
    }

    /* Empty hint */
    #genEmptyHint {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        user-select: none;
        opacity: 0.4;
    }

    /* Image display */
    #genImageContainer {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1rem 1rem;
        display: none;
    }
    #genMainImage {
        max-width: min(600px, 80%);
        max-height: 72vh;
        border-radius: 1.25rem;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.07), 0 32px 80px rgba(0,0,0,0.6);
        object-fit: contain;
        animation: imgFadeIn 0.4s cubic-bezier(0.16,1,0.3,1);
    }
    @keyframes imgFadeIn {
        from { opacity: 0; transform: scale(0.94) translateY(10px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    #genImageActions {
        position: absolute;
        bottom: calc(1rem + 12px);
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    #genImageContainer:hover #genImageActions { opacity: 1; }
    .img-action-btn {
        padding: 0.4rem 0.875rem;
        border-radius: 0.625rem;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(18,22,30,0.9);
        backdrop-filter: blur(8px);
        color: #e2e8f0;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        display: flex; align-items: center; gap: 0.35rem;
        transition: all 0.15s;
    }
    .img-action-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }

    /* Multi-image grid */
    #genMultiGrid {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem 2rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .gen-grid-img {
        max-width: 280px;
        max-height: 45vh;
        border-radius: 1rem;
        object-fit: contain;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.07), 0 16px 40px rgba(0,0,0,0.5);
        cursor: pointer;
        transition: transform 0.15s;
        animation: imgFadeIn 0.4s cubic-bezier(0.16,1,0.3,1);
    }
    .gen-grid-img:hover { transform: scale(1.02); }

    /* Generating spinner overlay */
    #genSpinner {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        z-index: 15;
    }
    .spinner-ring {
        width: 48px; height: 48px;
        border: 3px solid rgba(19,164,236,0.2);
        border-top-color: #13a4ec;
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── History strip (right) ───────────────────────── */
    #genStrip {
        width: 72px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        background: rgba(15,17,22,0.9);
        border-left: 1px solid rgba(255,255,255,0.05);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0.75rem 0;
        scroll-behavior: smooth;
    }
    #genStrip::-webkit-scrollbar { width: 2px; }
    #genStrip::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.07); border-radius: 99px; }

    .strip-thumb-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 0 8px;
    }
    .strip-connector {
        width: 2px; height: 10px;
        background: linear-gradient(to bottom, rgba(19,164,236,0.3), rgba(139,92,246,0.3));
        flex-shrink: 0;
    }
    .strip-thumb {
        width: 52px; height: 52px;
        border-radius: 0.625rem;
        object-fit: cover;
        border: 1.5px solid rgba(255,255,255,0.07);
        cursor: pointer;
        transition: all 0.15s;
        display: block;
    }
    .strip-thumb:hover {
        border-color: rgba(19,164,236,0.5);
        box-shadow: 0 0 0 2px rgba(19,164,236,0.15);
        transform: scale(1.05);
    }
    .strip-thumb.active {
        border-color: #13a4ec;
        box-shadow: 0 0 0 2px rgba(19,164,236,0.25);
    }

    /* ── Lightbox ─────────────────────────────────────── */
    #genLightbox {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        backdrop-filter: blur(8px);
        z-index: 200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    #genLightbox img {
        max-width: 90vw;
        max-height: 88vh;
        border-radius: 1rem;
        box-shadow: 0 32px 80px rgba(0,0,0,0.8);
        object-fit: contain;
    }
    #lbClose {
        position: absolute;
        top: 1.25rem; right: 1.25rem;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 0.625rem;
        color: white;
        padding: 0.375rem 0.75rem;
        cursor: pointer;
        font-size: 0.78rem;
        display: flex; align-items: center; gap: 0.35rem;
    }
    #lbClose:hover { background: rgba(255,255,255,0.18); }
    #lbDownload {
        position: absolute;
        bottom: 1.5rem; left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #13a4ec, #8b5cf6);
        border: none;
        border-radius: 0.75rem;
        color: white;
        padding: 0.5rem 1.25rem;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex; align-items: center; gap: 0.4rem;
    }

    /* Autocomplete dropdown */
    #acDropdown {
        position: absolute;
        left: 0; right: 0;
        background: rgba(18,22,30,0.98);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 0.75rem;
        margin-top: 0.25rem;
        z-index: 50;
        max-height: 180px;
        overflow-y: auto;
        display: none;
    }
    .ac-item {
        padding: 0.5rem 0.75rem;
        font-size: 0.78rem;
        color: #94a3b8;
        cursor: pointer;
        transition: background 0.1s;
    }
    .ac-item:hover { background: rgba(255,255,255,0.05); color: #e2e8f0; }
</style>
@endpush

@section('content')
<div id="genWrap">

    {{-- ── Settings Panel ─────────────────────────────── --}}
    <div id="genSettings">

        {{-- Provider --}}
        <div class="settings-section">
            <div class="settings-label">AI Provider</div>
            <div id="providerList">
                <div class="text-xs text-slate-600 py-2">Loading providers…</div>
            </div>
        </div>

        {{-- Model --}}
        <div class="settings-section">
            <div class="settings-label">Model</div>
            <select id="modelSelect" class="gen-select" onchange="onModelChange()">
                <option value="">Select a provider first</option>
            </select>
        </div>

        {{-- Prompt --}}
        <div class="settings-section">
            <div class="settings-label">Prompt</div>
            <div style="position:relative;">
                <textarea id="promptInput" class="gen-textarea" placeholder="Describe the image you want to generate…"
                    oninput="onPromptInput()" rows="4"></textarea>
                <div id="acDropdown"></div>
            </div>
        </div>

        {{-- Resolution --}}
        <div class="settings-section" id="resSection">
            <div class="settings-label">Resolution / Aspect Ratio</div>
            <div id="resolutionGrid"></div>
        </div>

        {{-- Quality --}}
        <div class="settings-section" id="qualitySection" style="display:none;">
            <div class="settings-label">Quality</div>
            <select id="qualitySelect" class="gen-select" onchange="S.settings.quality = this.value; updateCost()"></select>
        </div>

        {{-- Style --}}
        <div class="settings-section" id="styleSection" style="display:none;">
            <div class="settings-label">Style</div>
            <select id="styleSelect" class="gen-select" onchange="S.settings.style = this.value"></select>
        </div>

        {{-- Output Format --}}
        <div class="settings-section" id="formatSection" style="display:none;">
            <div class="settings-label">Output Format</div>
            <select id="formatSelect" class="gen-select" onchange="S.settings.output_format = this.value">
                <option value="png">PNG</option>
                <option value="jpg">JPG</option>
                <option value="webp">WebP</option>
            </select>
        </div>

        {{-- Number of images --}}
        <div class="settings-section">
            <div class="settings-label">Number of Images</div>
            <div class="num-img-row">
                <button class="num-btn" onclick="changeNumImages(-1)">−</button>
                <div id="numImagesDisplay" class="gen-input" style="text-align:center; cursor:default; width:auto; flex:1;">1</div>
                <button class="num-btn" onclick="changeNumImages(1)">+</button>
            </div>
        </div>

        {{-- Negative Prompt --}}
        <div class="settings-section" id="negSection" style="display:none;">
            <div class="settings-label">Negative Prompt</div>
            <textarea id="negPromptInput" class="gen-textarea" placeholder="Things to exclude from the image…" rows="2"
                oninput="S.negativePrompt = this.value"></textarea>
        </div>

        {{-- Advanced (SD only) --}}
        <div class="settings-section" id="advancedSection" style="display:none;">
            <div class="settings-label" style="display:flex;align-items:center;justify-content:space-between;">
                Advanced
                <button onclick="S.showAdvanced=!S.showAdvanced; document.getElementById('advancedInner').style.display=S.showAdvanced?'block':'none'"
                    style="font-size:0.65rem;color:#475569;background:none;border:none;cursor:pointer;">toggle</button>
            </div>
            <div id="advancedInner" style="display:none;">
                <div style="margin-bottom:0.75rem;" id="cfgSection">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.375rem;">
                        <span style="font-size:0.75rem;color:#64748b;">Guidance Scale (CFG)</span>
                        <span id="cfgVal" style="font-size:0.75rem;color:#13a4ec;font-weight:700;">7.5</span>
                    </div>
                    <input type="range" class="gen-slider" min="1" max="20" step="0.5" value="7.5" id="cfgSlider"
                        oninput="S.guidanceScale=parseFloat(this.value); document.getElementById('cfgVal').textContent=this.value">
                </div>
                <div style="margin-bottom:0.75rem;" id="stepsSection">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.375rem;">
                        <span style="font-size:0.75rem;color:#64748b;">Steps</span>
                        <span id="stepsVal" style="font-size:0.75rem;color:#13a4ec;font-weight:700;">30</span>
                    </div>
                    <input type="range" class="gen-slider" min="1" max="100" step="1" value="30" id="stepsSlider"
                        oninput="S.steps=parseInt(this.value); document.getElementById('stepsVal').textContent=this.value">
                </div>
                <div>
                    <div class="settings-label" style="margin-bottom:0.375rem;">Seed (blank = random)</div>
                    <div style="display:flex;gap:0.375rem;align-items:center;">
                        <input type="number" id="seedInput" class="gen-input" placeholder="e.g. 42"
                            oninput="S.seed=this.value" style="flex:1;">
                        <button onclick="document.getElementById('seedInput').value=''; S.seed='';"
                            title="Clear seed"
                            style="padding:0.5rem;border-radius:0.5rem;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:#64748b;cursor:pointer;font-size:0.7rem;">
                            <span class="material-symbols-outlined" style="font-size:14px;">casino</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional params (dynamic) --}}
        <div class="settings-section" id="extraParamsSection" style="display:none;">
            <div class="settings-label">Model Options</div>
            <div id="extraParamsInner"></div>
        </div>

        {{-- Reference images (Gemini) --}}
        <div class="settings-section" id="refSection" style="display:none;">
            <div class="settings-label">Reference Images</div>
            <div class="ref-upload-zone" onclick="document.getElementById('refImageInput').click()">
                <span class="material-symbols-outlined" style="font-size:20px;display:block;margin-bottom:0.25rem;">add_photo_alternate</span>
                Click to add reference images
            </div>
            <input type="file" id="refImageInput" accept="image/*" multiple style="display:none;" onchange="handleRefImages(this)">
            <div class="ref-preview-row" id="refPreviews"></div>
        </div>

        {{-- Generate button --}}
        <div class="settings-section" style="border-bottom:none; padding-bottom:1.5rem;">
            <button id="genBtn" onclick="generateImage()">
                <span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span>
                Generate
            </button>
            <div id="costBadge">
                Cost: <span id="costVal">0</span> credits
            </div>
        </div>

    </div>

    {{-- ── Main Stage ───────────────────────────────────── --}}
    <div id="genStage">

        {{-- Toolbar --}}
        <div id="genToolbar">
            <span class="material-symbols-outlined" style="font-size:14px;color:#13a4ec;">auto_awesome</span>
            <span id="tbModelLabel" style="color:#94a3b8;">No model selected</span>
            <div class="toolbar-sep"></div>
            <span id="tbCount" style="color:#475569;">0 generations</span>
        </div>

        {{-- Progress bar --}}
        <div id="genProgressWrap">
            <div id="genProgressBar"></div>
        </div>

        {{-- Spinner --}}
        <div id="genSpinner">
            <div class="spinner-ring"></div>
            <span style="font-size:0.78rem;color:#475569;">Generating…</span>
        </div>

        {{-- Empty hint --}}
        <div id="genEmptyHint">
            <span class="material-symbols-outlined" style="font-size:48px;margin-bottom:0.75rem;color:#1e293b;">image</span>
            <p style="font-size:0.85rem;color:#1e293b;font-weight:500;">Configure settings and generate an image</p>
        </div>

        {{-- Single image --}}
        <div id="genImageContainer">
            <img id="genMainImage" src="" alt="Generated image">
            <div id="genImageActions">
                <button class="img-action-btn" onclick="downloadImage(S.activeImageUrl)">
                    <span class="material-symbols-outlined" style="font-size:14px;">download</span>
                    Download
                </button>
                <button class="img-action-btn" onclick="openLightbox(S.activeImageUrl)">
                    <span class="material-symbols-outlined" style="font-size:14px;">open_in_full</span>
                    Expand
                </button>
            </div>
        </div>

        {{-- Multi-image grid --}}
        <div id="genMultiGrid"></div>

    </div>

    {{-- ── History Strip ────────────────────────────────── --}}
    <div id="genStrip"></div>

</div>

{{-- Lightbox --}}
<div id="genLightbox" onclick="if(event.target===this)closeLightbox()">
    <button id="lbClose" onclick="closeLightbox()">
        <span class="material-symbols-outlined" style="font-size:14px;">close</span>
        Close
    </button>
    <img id="lbImage" src="" alt="">
    <button id="lbDownload" onclick="downloadImage(document.getElementById('lbImage').src)">
        <span class="material-symbols-outlined" style="font-size:16px;">download</span>
        Download
    </button>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── State ──────────────────────────────────────────────
const S = {
    providers:       @json($providers),
    selectedProvider: null,
    selectedModel:   null,
    currentModelData: {},
    prompt:          '',
    negativePrompt:  '',
    settings: {
        resolution:    '',
        style:         '',
        quality:       '',
        output_format: 'png',
        num_images:    1,
    },
    guidanceScale:   7.5,
    steps:           30,
    seed:            '',
    additionalParams:        {},
    additionalParamsEnabled: {},
    referenceFiles:  [],
    loading:         false,
    loadingProgress: 0,
    generatedImages: [],   // [{url, prompt, model, ts}]
    activeImageUrl:  null,
    activeIdx:       -1,
    showAdvanced:    false,
    acTimer:         null,
};

// ── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderProviders();
    if (S.providers.length > 0) {
        selectProvider(S.providers[0].id);
    }
});

// ── Providers ──────────────────────────────────────────
function renderProviders() {
    const el = document.getElementById('providerList');
    if (!S.providers.length) {
        el.innerHTML = '<div class="text-xs text-slate-600 py-2">No providers available. Check API configuration.</div>';
        return;
    }
    const colors = ['#13a4ec','#8b5cf6','#f59e0b','#10b981','#ef4444','#ec4899'];
    el.innerHTML = S.providers.map((p, i) => `
        <div class="provider-card${S.selectedProvider === p.id ? ' active' : ''}" id="pcard-${p.id}" onclick="selectProvider(${p.id})">
            <div class="provider-dot" style="background:${colors[i % colors.length]}"></div>
            <span>${esc(p.name)}</span>
            <span style="margin-left:auto;font-size:0.65rem;color:#334155;">${p.active_models?.length ?? 0} models</span>
        </div>
    `).join('');
}

function selectProvider(id) {
    S.selectedProvider = id;
    document.querySelectorAll('.provider-card').forEach(c => c.classList.remove('active'));
    const card = document.getElementById('pcard-' + id);
    if (card) card.classList.add('active');

    const provider = S.providers.find(p => p.id === id);
    const models   = provider?.active_models ?? [];
    const sel      = document.getElementById('modelSelect');
    sel.innerHTML  = models.length
        ? models.map(m => `<option value="${m.id}">${esc(m.name)}</option>`).join('')
        : '<option value="">No models available</option>';

    if (models.length) {
        sel.value = models[0].id;
        onModelChange();
    }
}

// ── Model change ───────────────────────────────────────
function onModelChange() {
    const provider = S.providers.find(p => p.id === S.selectedProvider);
    const modelId  = parseInt(document.getElementById('modelSelect').value);
    const model    = provider?.active_models?.find(m => m.id === modelId);
    if (!model) return;

    S.selectedModel   = modelId;
    S.currentModelData = model;

    // Apply defaults
    S.settings.resolution    = model.default_resolution    || model.available_resolutions?.[0]   || '';
    S.settings.style         = model.default_style         || model.available_styles?.[0]         || '';
    S.settings.quality       = model.default_quality       || model.available_qualities?.[0]      || '';
    S.settings.output_format = model.default_output_format || 'png';
    S.settings.num_images    = 1;
    S.guidanceScale          = 7.5;
    S.steps                  = 30;

    updateToolbar();
    renderResolutions();
    renderQuality();
    renderStyle();
    renderFormat();
    renderNegativePrompt();
    renderAdvanced();
    renderExtraParams();
    renderRefSection();
    updateCost();
    document.getElementById('numImagesDisplay').textContent = 1;
}

// ── Toolbar ────────────────────────────────────────────
function updateToolbar() {
    const p = S.providers.find(p => p.id === S.selectedProvider);
    const m = S.currentModelData;
    const label = m?.name ? `${p?.name ?? ''} — ${m.name}` : 'No model selected';
    document.getElementById('tbModelLabel').textContent = label;
}

// ── Resolution grid ────────────────────────────────────
function renderResolutions() {
    const grid = document.getElementById('resolutionGrid');
    const resos = S.currentModelData.available_resolutions ?? [];
    if (!resos.length) { grid.innerHTML = ''; return; }

    grid.innerHTML = resos.map(r => `
        <button class="res-btn${S.settings.resolution === r ? ' active' : ''}"
            onclick="selectResolution('${r}')">${r.replace('x','×')}</button>
    `).join('');
}

function selectResolution(r) {
    S.settings.resolution = r;
    document.querySelectorAll('.res-btn').forEach(b => {
        b.classList.toggle('active', b.textContent === r.replace('x','×'));
    });
    updateCost();
}

// ── Quality / Style / Format ───────────────────────────
function renderQuality() {
    const qs  = S.currentModelData.available_qualities ?? [];
    const sec = document.getElementById('qualitySection');
    sec.style.display = qs.length ? '' : 'none';
    if (!qs.length) return;
    const sel = document.getElementById('qualitySelect');
    sel.innerHTML = qs.map(q => `<option value="${q}"${S.settings.quality===q?' selected':''}>${cap(q)}</option>`).join('');
    S.settings.quality = sel.value;
}

function renderStyle() {
    const ss  = S.currentModelData.available_styles ?? [];
    const sec = document.getElementById('styleSection');
    sec.style.display = ss.length ? '' : 'none';
    if (!ss.length) return;
    const sel = document.getElementById('styleSelect');
    sel.innerHTML = ss.map(s => `<option value="${s}"${S.settings.style===s?' selected':''}>${cap(s)}</option>`).join('');
    S.settings.style = sel.value;
}

function renderFormat() {
    const fs  = S.currentModelData.available_output_formats ?? [];
    const sec = document.getElementById('formatSection');
    sec.style.display = fs.length ? '' : 'none';
    if (!fs.length) return;
    const sel = document.getElementById('formatSelect');
    sel.value = S.settings.output_format || 'png';
}

// ── Negative prompt ────────────────────────────────────
function renderNegativePrompt() {
    document.getElementById('negSection').style.display =
        S.currentModelData.supports_negative_prompt ? '' : 'none';
}

// ── Advanced (SD) ──────────────────────────────────────
function supportsGuidanceScale() {
    return S.providers.find(p => p.id === S.selectedProvider)?.slug === 'stable-diffusion';
}
function renderAdvanced() {
    document.getElementById('advancedSection').style.display = supportsGuidanceScale() ? '' : 'none';
}

// ── Extra params ───────────────────────────────────────
function renderExtraParams() {
    const params = S.currentModelData.additional_parameters;
    const sec    = document.getElementById('extraParamsSection');
    if (!params || !Object.keys(params).length) { sec.style.display = 'none'; return; }
    sec.style.display = '';

    S.additionalParams        = {};
    S.additionalParamsEnabled = {};

    let html = '';
    for (const [key, cfg] of Object.entries(params)) {
        if (cfg.type === 'checkbox') {
            const checked = cfg.enabled_by_default ?? false;
            S.additionalParams[key]        = checked;
            S.additionalParamsEnabled[key] = checked;
            html += `
                <div class="toggle-row" style="margin-bottom:0.625rem;">
                    <span class="toggle-label">${esc(cfg.label)}</span>
                    <label class="toggle-switch">
                        <input type="checkbox" ${checked?'checked':''} onchange="S.additionalParams['${key}']=this.checked; S.additionalParamsEnabled['${key}']=this.checked;">
                        <span class="toggle-track"></span>
                    </label>
                </div>`;
        } else if (cfg.type === 'select') {
            const def = cfg.default ?? cfg.options?.[0] ?? '';
            S.additionalParams[key]        = def;
            S.additionalParamsEnabled[key] = true;
            html += `
                <div style="margin-bottom:0.625rem;">
                    <div class="settings-label" style="margin-bottom:0.25rem;">${esc(cfg.label)}</div>
                    <select class="gen-select" onchange="S.additionalParams['${key}']=this.value">
                        ${(cfg.options ?? []).map(o => `<option value="${o}"${o===def?' selected':''}>${o}</option>`).join('')}
                    </select>
                </div>`;
        } else if (cfg.type === 'number') {
            const def = cfg.default ?? cfg.min ?? 0;
            S.additionalParams[key]        = def;
            S.additionalParamsEnabled[key] = true;
            html += `
                <div style="margin-bottom:0.625rem;">
                    <div class="settings-label" style="margin-bottom:0.25rem;">${esc(cfg.label)}</div>
                    <input type="number" class="gen-input" value="${def}" min="${cfg.min??0}" max="${cfg.max??9999}"
                        onchange="S.additionalParams['${key}']=this.value">
                </div>`;
        }
    }
    document.getElementById('extraParamsInner').innerHTML = html;
}

function getEnabledAdditionalParams() {
    const result = {};
    const params = S.currentModelData.additional_parameters || {};
    for (const [key, cfg] of Object.entries(params)) {
        if (cfg.type === 'checkbox') {
            if (S.additionalParamsEnabled[key]) result[key] = S.additionalParams[key];
        } else {
            result[key] = S.additionalParams[key];
        }
    }
    return result;
}

// ── Reference images (Gemini) ──────────────────────────
function renderRefSection() {
    document.getElementById('refSection').style.display =
        S.currentModelData.supports_image_to_image ? '' : 'none';
}

function handleRefImages(input) {
    S.referenceFiles = [...S.referenceFiles, ...Array.from(input.files)];
    renderRefPreviews();
    input.value = '';
}

function renderRefPreviews() {
    const row = document.getElementById('refPreviews');
    row.innerHTML = S.referenceFiles.map((f, i) => `
        <div class="ref-thumb-wrap">
            <img class="ref-thumb" src="${URL.createObjectURL(f)}" alt="">
            <button class="ref-thumb-del" onclick="removeRef(${i})">×</button>
        </div>
    `).join('');
}

function removeRef(i) {
    S.referenceFiles.splice(i, 1);
    renderRefPreviews();
}

// ── Num images ─────────────────────────────────────────
function changeNumImages(delta) {
    const max = S.currentModelData.max_images_per_request ?? 4;
    S.settings.num_images = Math.max(1, Math.min(max, S.settings.num_images + delta));
    document.getElementById('numImagesDisplay').textContent = S.settings.num_images;
    updateCost();
}

// ── Cost ───────────────────────────────────────────────
function calculateCost() {
    const m = S.currentModelData;
    if (!m?.id) return 0;
    if (m.pricing_matrix) {
        const mx  = typeof m.pricing_matrix === 'string' ? JSON.parse(m.pricing_matrix) : m.pricing_matrix;
        const q   = S.settings.quality || 'standard';
        const r   = S.settings.resolution || '';
        return (mx[q]?.[r] ?? m.credits_per_image ?? 0) * S.settings.num_images;
    }
    return (m.credits_per_image ?? 0) * S.settings.num_images;
}

function updateCost() {
    document.getElementById('costVal').textContent = calculateCost();
}

// ── Generate ───────────────────────────────────────────
async function generateImage() {
    const prompt = document.getElementById('promptInput').value.trim();
    if (!prompt)            return showError('Please enter a prompt.');
    if (!S.selectedModel)   return showError('Please select a model.');
    if (S.loading)          return;

    S.loading        = true;
    S.loadingProgress = 0;
    setGenerating(true);
    simulateProgress();

    try {
        let body, headers = {};
        const hasRefImages = S.referenceFiles.length > 0;
        const extraParams  = getEnabledAdditionalParams();

        if (hasRefImages) {
            const fd = new FormData();
            fd.append('model_id',     S.selectedModel);
            fd.append('prompt',       prompt);
            if (S.negativePrompt)     fd.append('negative_prompt',   S.negativePrompt);
            fd.append('resolution',    S.settings.resolution);
            fd.append('style',         S.settings.style);
            fd.append('quality',       S.settings.quality);
            fd.append('output_format', S.settings.output_format);
            fd.append('num_images',    S.settings.num_images);
            if (supportsGuidanceScale()) fd.append('guidance_scale', S.guidanceScale);
            if (supportsGuidanceScale()) fd.append('steps', S.steps);
            if (S.seed) fd.append('seed', S.seed);
            fd.append('additional_parameters', JSON.stringify(extraParams));
            S.referenceFiles.forEach(f => fd.append('reference_images[]', f));
            body = fd;
        } else {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify({
                model_id:              S.selectedModel,
                prompt,
                negative_prompt:       S.negativePrompt || undefined,
                resolution:            S.settings.resolution,
                style:                 S.settings.style || undefined,
                quality:               S.settings.quality || undefined,
                output_format:         S.settings.output_format,
                num_images:            S.settings.num_images,
                guidance_scale:        supportsGuidanceScale() ? S.guidanceScale : undefined,
                steps:                 supportsGuidanceScale() ? S.steps         : undefined,
                seed:                  S.seed || undefined,
                additional_parameters: extraParams,
            });
        }

        const res  = await fetch('/image-generator/generate', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, ...headers },
            body,
        });
        const data = await res.json();

        if (data.success) {
            const imgs = data.data ?? [];
            const provider = S.providers.find(p => p.id === S.selectedProvider);
            imgs.forEach(img => {
                S.generatedImages.unshift({
                    url:   img.url ?? img.image_url ?? img,
                    prompt,
                    model: S.currentModelData.name,
                    provider: provider?.name ?? '',
                    ts:    Date.now(),
                });
            });

            renderStrip();
            showImages(imgs.map(i => i.url ?? i.image_url ?? i));
            document.getElementById('tbCount').textContent = S.generatedImages.length + ' generation' + (S.generatedImages.length !== 1 ? 's' : '');
        } else {
            showError(data.message ?? data.error ?? 'Generation failed.');
        }
    } catch (e) {
        showError('Request failed: ' + e.message);
    } finally {
        S.loading         = false;
        S.loadingProgress = 100;
        setGenerating(false);
    }
}

// ── Progress simulation ────────────────────────────────
function simulateProgress() {
    const steps = [
        {t:12, d:400}, {t:30, d:900}, {t:52, d:1600},
        {t:70, d:2000}, {t:83, d:1200}, {t:92, d:2500},
    ];
    let i = 0;
    const bar = document.getElementById('genProgressBar');
    const tick = () => {
        if (i >= steps.length || !S.loading) return;
        const {t, d} = steps[i++];
        S.loadingProgress = t;
        bar.style.width = t + '%';
        setTimeout(tick, d);
    };
    tick();
}

// ── UI state ───────────────────────────────────────────
function setGenerating(gen) {
    const btn  = document.getElementById('genBtn');
    const prog = document.getElementById('genProgressWrap');
    const spin = document.getElementById('genSpinner');

    btn.disabled = gen;
    btn.innerHTML = gen
        ? '<span class="material-symbols-outlined" style="font-size:16px;">hourglass_empty</span> Generating…'
        : '<span class="material-symbols-outlined" style="font-size:18px;">auto_awesome</span> Generate';
    prog.style.display = gen ? '' : 'none';
    spin.style.display = gen ? 'flex' : 'none';

    if (!gen) {
        document.getElementById('genProgressBar').style.width = '0%';
    }
}

// ── Image display ──────────────────────────────────────
function showImages(urls) {
    const empty  = document.getElementById('genEmptyHint');
    const single = document.getElementById('genImageContainer');
    const multi  = document.getElementById('genMultiGrid');

    empty.style.display = 'none';

    if (urls.length === 1) {
        single.style.display = 'flex';
        multi.style.display  = 'none';
        const img = document.getElementById('genMainImage');
        img.src   = urls[0];
        S.activeImageUrl = urls[0];
        S.activeIdx      = 0;
    } else {
        single.style.display = 'none';
        multi.style.display  = 'flex';
        multi.innerHTML = urls.map(u => `
            <img class="gen-grid-img" src="${u}" alt="" onclick="openLightbox('${u}')">
        `).join('');
        S.activeImageUrl = urls[0];
    }
}

// ── History strip ──────────────────────────────────────
function renderStrip() {
    const strip = document.getElementById('genStrip');
    strip.innerHTML = S.generatedImages.map((img, i) => {
        const isFirst = i === 0;
        return `
            ${!isFirst ? '<div class="strip-connector"></div>' : ''}
            <div class="strip-thumb-wrap">
                <img class="strip-thumb${S.activeIdx === i ? ' active' : ''}" src="${img.url}"
                    alt="" title="${esc(img.prompt)}" onclick="viewFromStrip(${i}, '${img.url}')">
            </div>
        `;
    }).join('');
}

function viewFromStrip(i, url) {
    S.activeIdx      = i;
    S.activeImageUrl = url;
    document.querySelectorAll('.strip-thumb').forEach((t, ti) =>
        t.classList.toggle('active', ti === i));

    const multi  = document.getElementById('genMultiGrid');
    const single = document.getElementById('genImageContainer');
    const empty  = document.getElementById('genEmptyHint');

    empty.style.display  = 'none';
    multi.style.display  = 'none';
    single.style.display = 'flex';
    const img = document.getElementById('genMainImage');
    img.src   = url;
}

// ── Lightbox ───────────────────────────────────────────
function openLightbox(url) {
    document.getElementById('lbImage').src    = url;
    document.getElementById('genLightbox').style.display = 'flex';
    S.activeImageUrl = url;
}
function closeLightbox() {
    document.getElementById('genLightbox').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Download ───────────────────────────────────────────
function downloadImage(url) {
    if (!url) return;
    const a   = document.createElement('a');
    a.href    = url;
    a.download = 'generated-' + Date.now() + '.png';
    a.target  = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// ── Autocomplete ───────────────────────────────────────
function onPromptInput() {
    S.prompt = document.getElementById('promptInput').value;
    clearTimeout(S.acTimer);
    const q = S.prompt.trim();
    if (q.length < 3) { closeAc(); return; }
    S.acTimer = setTimeout(() => fetchAutocomplete(q), 400);
}

async function fetchAutocomplete(q) {
    try {
        const res  = await fetch('/image-generator/autocomplete?query=' + encodeURIComponent(q));
        const data = await res.json();
        const suggestions = data.data ?? data.suggestions ?? [];
        if (suggestions.length) renderAc(suggestions);
        else closeAc();
    } catch { closeAc(); }
}

function renderAc(items) {
    const el = document.getElementById('acDropdown');
    el.innerHTML = items.slice(0, 6).map(s =>
        `<div class="ac-item" onclick="applyAc('${esc(s)}')">${esc(s)}</div>`
    ).join('');
    el.style.display = '';
}

function applyAc(text) {
    document.getElementById('promptInput').value = text;
    S.prompt = text;
    closeAc();
}

function closeAc() {
    document.getElementById('acDropdown').style.display = 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('#promptInput') && !e.target.closest('#acDropdown')) closeAc();
});

// ── Helpers ────────────────────────────────────────────
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function cap(s) {
    return String(s).charAt(0).toUpperCase() + String(s).slice(1).replace(/-/g,' ');
}
function showError(msg) {
    // Simple inline toast
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:0.75rem;padding:0.625rem 1.25rem;color:#fca5a5;font-size:0.8rem;z-index:999;backdrop-filter:blur(8px);';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
</script>
@endpush
