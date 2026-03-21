@extends('layouts.app')

@section('title', 'Canvas Studio — Clever Creator')

@push('styles')
<style>
    :root {
        --mobile-mini-dock-height: 60px;
    }

    /* ── Make this page fully viewport-fitted (no page scroll) ─── */
    #appMain {
        height: 100dvh;
        overflow: hidden;
    }
    #appMain > div {
        padding: 0 !important;
        height: calc(100dvh - 4rem); /* mobile topbar: h-16 */
        overflow: hidden;
    }
    @media (min-width: 1024px) {
        #appMain > div { height: calc(100dvh - 5rem); } /* desktop topbar: h-20 */
    }

    /* ── Root layout ─────────────────────────────────── */
    #studioWrap {
        display: flex;
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        min-height: 0;
    }

    /* ── Canvas stage ────────────────────────────────── */
    #canvasStage {
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

    /* Canvas toolbar (top-left floating) */
    #canvasToolbar {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(22,27,34,0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.875rem;
        padding: 0.5rem 0.75rem;
        z-index: 10;
    }

    /* Canvas info badge (top-right) */
    #canvasInfo {
        position: absolute;
        top: 1rem;
        right: calc(72px + 1rem);   /* right of flow strip */
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(22,27,34,0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.875rem;
        padding: 0.5rem 0.875rem;
        z-index: 10;
        font-size: 0.7rem;
        color: #64748b;
    }

    /* ── Generated image on canvas ───────────────────── */
    #canvasImageContainer {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5rem 1rem 1rem;
    }
    #canvasMainImage {
        max-width: min(640px, 80%);
        max-height: 70vh;
        border-radius: 1.25rem;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.07), 0 32px 80px rgba(0,0,0,0.6);
        object-fit: contain;
        cursor: zoom-in;
        animation: canvasFadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes canvasFadeIn {
        from { opacity: 0; transform: scale(0.94) translateY(12px); }
        to   { opacity: 1; transform: scale(1)    translateY(0); }
    }

    /* Image action buttons that overlay the canvas image */
    #canvasImageActions {
        position: absolute;
        bottom: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        z-index: 2;
        transition: opacity 0.2s;
    }
    #canvasImageContainer:hover #canvasImageActions { opacity: 1; }

    /* Empty canvas hint */
    #canvasEmptyHint {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        user-select: none;
    }
    #canvasGeneratingOverlay {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.75rem;
        background: rgba(8, 10, 14, 0.26);
        backdrop-filter: blur(1px);
        z-index: 16;
        pointer-events: none;
    }
    #canvasGeneratingOverlay .canvas-gen-ring {
        width: 44px;
        height: 44px;
        border-radius: 9999px;
        border: 3px solid rgba(19,164,236,0.22);
        border-top-color: #13a4ec;
        animation: canvasGenSpin 0.9s linear infinite;
    }
    @keyframes canvasGenSpin { to { transform: rotate(360deg); } }

    /* ── Example image cards in empty state ─────────── */
    .canvas-example-card {
        pointer-events: auto;
        position: relative;
        width: 148px;
        height: 148px;
        border-radius: 0.875rem;
        overflow: visible;
        border: 1px solid rgba(255,255,255,0.06);
        cursor: pointer;
        background: #16181f;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        flex-shrink: 0;
    }
    .canvas-example-card:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 12px 32px rgba(0,0,0,0.5);
        border-color: rgba(99,102,241,0.35);
    }
    .canvas-example-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0.875rem;
        display: block;
    }
    .canvas-example-tooltip {
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%);
        background: rgba(12, 15, 23, 0.97);
        border: 1px solid rgba(255,255,255,0.09);
        color: #94a3b8;
        font-size: 11px;
        line-height: 1.5;
        padding: 7px 11px;
        border-radius: 9px;
        white-space: normal;
        width: 210px;
        text-align: center;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s;
        z-index: 50;
    }
    .canvas-example-card:hover .canvas-example-tooltip { opacity: 1; }
    .canvas-example-use-label {
        pointer-events: none;
        position: absolute;
        bottom: 6px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.72);
        color: #e2e8f0;
        font-size: 10px;
        font-weight: 500;
        padding: 3px 9px;
        border-radius: 999px;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.15s;
        backdrop-filter: blur(4px);
    }
    .canvas-example-card:hover .canvas-example-use-label { opacity: 1; }

    /* ── Flow strip (right, vertical) ───────────────── */
    #flowStrip {
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
    #flowStrip::-webkit-scrollbar { width: 2px; }
    #flowStrip::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.07); border-radius: 99px; }

    .flow-thumb-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        padding: 0 8px;
    }
    .flow-connector-line {
        width: 2px;
        height: 10px;
        background: linear-gradient(to bottom, rgba(19,164,236,0.4), rgba(139,92,246,0.4));
        flex-shrink: 0;
    }
    .flow-thumb {
        width: 52px;
        height: 52px;
        border-radius: 0.625rem;
        object-fit: cover;
        border: 1.5px solid rgba(255,255,255,0.07);
        cursor: pointer;
        transition: all 0.15s;
        display: block;
    }
    .flow-thumb:hover {
        border-color: rgba(19,164,236,0.5);
        box-shadow: 0 0 0 2px rgba(19,164,236,0.15);
        transform: scale(1.05);
    }
    .flow-thumb.active {
        border-color: #13a4ec;
        box-shadow: 0 0 0 2px rgba(19,164,236,0.25);
    }
    .flow-thumb-label {
        font-size: 9px;
        color: #475569;
        margin-top: 3px;
        margin-bottom: 0;
        text-align: center;
    }

    /* ── Minichat panel (floating bottom-right of stage) ── */
    #minichat {
        position: absolute;
        bottom: 1.25rem;
        right: calc(72px + 1.25rem);   /* right strip offset */
        width: 360px;
        max-height: 520px;
        display: flex;
        flex-direction: column;
        background: rgba(18, 22, 30, 0.92);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.09);
        border-radius: 1.25rem;
        box-shadow: 0 24px 64px rgba(0,0,0,0.6), 0 0 0 1px rgba(19,164,236,0.06);
        z-index: 30;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.16,1,0.3,1), opacity 0.2s;
    }
    #minichat.collapsed {
        max-height: 52px;
    }

    /* Minichat FAB (shown when minichat is fully hidden) */
    #minichatFab {
        position: absolute;
        bottom: 1.25rem;
        right: calc(72px + 1.25rem);
        z-index: 30;
        display: none;
    }

    /* Minichat header */
    #minichatHeader {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        cursor: pointer;
        user-select: none;
    }

    /* Minichat messages */
    #minichatMessages {
        flex: 1;
        overflow-y: auto;
        padding: 0.875rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
    }
    #minichatMessages::-webkit-scrollbar { width: 3px; }
    #minichatMessages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 99px; }

    /* Mini message bubbles */
    .mini-msg-user .mini-bubble {
        background: linear-gradient(135deg, rgba(19,164,236,0.2), rgba(139,92,246,0.12));
        border: 1px solid rgba(19,164,236,0.2);
        border-radius: 0.875rem 0.875rem 0.25rem 0.875rem;
        margin-left: auto;
        max-width: 85%;
    }
    .mini-msg-ai .mini-bubble {
        background: rgba(30,36,46,0.9);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 0.875rem 0.875rem 0.875rem 0.25rem;
        max-width: 85%;
    }
    .mini-img-thumb {
        width: 100%;
        border-radius: 0.625rem;
        object-fit: cover;
        max-height: 160px;
        cursor: pointer;
        display: block;
        transition: opacity 0.15s;
    }
    .mini-img-thumb:hover { opacity: 0.88; }

    /* Minichat input */
    #minichatInput {
        flex-shrink: 0;
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 0.625rem 0.75rem;
        background: rgba(12,14,20,0.6);
    }
    #miniPromptInput {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.75rem;
        color: #e2e8f0;
        font-size: 0.8125rem;
        resize: none;
        outline: none;
        transition: border-color 0.15s;
        min-height: 40px;
        max-height: 100px;
        line-height: 1.5;
        padding: 0.5rem 0.75rem;
    }
    #miniPromptInput:focus { border-color: rgba(19,164,236,0.4); }
    #miniPromptInput::placeholder { color: rgba(100,116,139,0.6); }

    /* ── Typing dots ─────────────────────────────────── */
    .mini-dot {
        width: 5px; height: 5px; border-radius: 50%;
        background: #13a4ec;
        animation: miniPulse 1.2s infinite;
    }
    .mini-dot:nth-child(2) { animation-delay: 0.2s; }
    .mini-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes miniPulse {
        0%,80%,100% { opacity:0.2; transform:scale(0.8); }
        40%          { opacity:1;   transform:scale(1);   }
    }

    /* ── Sessions modal ──────────────────────────────── */
    /* NOTE: no display property on #sessionsModal ID — JS sets display:flex to open */
    .sess-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.65);
        backdrop-filter: blur(6px);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .sess-modal-inner {
        background: rgba(18, 22, 30, 0.98);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 1.5rem;
        width: 100%;
        max-width: 620px;
        max-height: 78vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 32px 80px rgba(0,0,0,0.7);
    }
    #sessionCards {
        flex: 1;
        overflow-y: auto;
        padding: 0.625rem;
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        min-height: 140px;
    }
    #sessionCards::-webkit-scrollbar { width: 3px; }
    #sessionCards::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 99px; }
    .sess-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 0.875rem;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s;
    }
    .sess-card:hover {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.07);
    }
    .sess-card.is-current {
        border-color: rgba(19,164,236,0.25);
        background: rgba(19,164,236,0.04);
    }
    .sess-card-thumb {
        width: 52px;
        height: 52px;
        border-radius: 0.625rem;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid rgba(255,255,255,0.07);
    }
    .sess-card-no-thumb {
        width: 52px;
        height: 52px;
        border-radius: 0.625rem;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgba(19,164,236,0.1), rgba(139,92,246,0.1));
        border: 1px solid rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ── Lightbox ───────────────────────────────────── */
    /* No display property on #lightbox ID — JS sets display:flex to open */
    .lightbox-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        backdrop-filter: blur(8px);
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    /* ── Drag-over glow on canvas ─────────────────────*/
    #canvasStage.drag-over {
        outline: 2px dashed rgba(19,164,236,0.5);
        outline-offset: -2px;
    }

    /* ── Canvas toolbar group (toolbar + quick chips stacked) ── */
    #canvasToolbarGroup {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    /* Remove individual positioning — parent handles it */
    #canvasToolbarGroup #canvasToolbar {
        position: static;
        top: auto; left: auto; z-index: auto;
    }

    /* ── Canvas quick action chips (above image, left-aligned) ─ */
    #canvasQuickActions {
        display: none;
        flex-wrap: wrap;
        gap: 0.375rem;
        max-width: 100%;
        margin-bottom: 0.5rem;
        align-self: flex-start;
    }
    .canvas-quick-chip {
        font-size: 0.6875rem;
        padding: 0.25rem 0.625rem;
        border-radius: 99px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(18,22,30,0.82);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
        line-height: 1.4;
    }
    .canvas-quick-chip:hover {
        border-color: rgba(19,164,236,0.35);
        background: rgba(19,164,236,0.1);
        color: #e2e8f0;
    }

    /* ── Touch devices: always show image action buttons ────── */
    @media (hover: none) {
        #canvasImageActions { opacity: 1 !important; }
    }

    /* ── Topbar is h-16 (4rem) on mobile, h-20 (5rem) on lg+ ── */
    @media (max-width: 1023px) {
        #studioWrap { height: 100%; }
    }

    /* ── Mobile sticky mini dock ───────────────────────────── */
    #mobileMiniDock {
        display: none;
        position: fixed;
        left: 0.75rem;
        right: 0.75rem;
        bottom: calc(env(safe-area-inset-bottom, 0px) + 0.5rem);
        height: var(--mobile-mini-dock-height);
        z-index: 70;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        background: rgba(248, 250, 252, 0.97);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(148,163,184,0.22);
        border-radius: 1rem;
        box-shadow: 0 10px 26px rgba(2, 6, 23, 0.22);
        padding: 0.25rem;
    }
    .mobile-mini-link {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.14rem;
        font-size: 0.62rem;
        font-weight: 600;
        color: #94a3b8;
        text-decoration: none;
        border-radius: 0.75rem;
        line-height: 1.1;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .mobile-mini-link .mini-icon {
        font-size: 1rem;
        line-height: 1;
    }
    .mobile-mini-link:hover {
        color: #0ea5e9;
        background: rgba(56, 189, 248, 0.09);
    }
    .mobile-mini-link.active {
        color: #13a4ec;
        background: rgba(19,164,236,0.12);
    }

    /* ════════════════════════════════════════════════════════
       MOBILE  ≤ 639 px
       ════════════════════════════════════════════════════════ */
    @media (max-width: 639px) {
        #mobileMiniDock {
            display: grid;
        }

        /* ── Hide flow strip – reclaim 72 px of canvas width ── */
        #flowStrip { display: none !important; }

        /* ── Canvas info badge: drop the flowStrip offset ──── */
        #canvasInfo {
            right: 0.75rem;
            gap: 0.5rem;
            padding: 0.375rem 0.625rem;
            font-size: 0.65rem;
        }
        /* Hide secondary info to save space */
        #canvasInfo #sessionLabel,
        #canvasInfo span.text-slate-700 { display: none; }

        /* ── Canvas image: use more screen ──────────────────── */
        #canvasImageContainer {
            inset: 0 0 55vh 0; /* reserve space for bottom minichat sheet */
            padding: 3.5rem 0.5rem 0.5rem;
        }
        #canvasMainImage {
            max-width: min(92vw, 640px);
            max-height: 34vh;
        }

        /* Keep empty-state content inside visible canvas area (above minichat) */
        #canvasEmptyHint {
            inset: 0 0 55vh 0;
            padding: 0.5rem;
            justify-content: center;
        }

        /* ── Quick-action chips: constrain width ─────────────── */
        #canvasQuickActions { max-width: calc(100vw - 2rem); }

        /* ── Toolbar: tighter padding on small screens ──────── */
        #canvasToolbar { padding: 0.375rem 0.5rem; gap: 0.375rem; }
        #canvasToolbar span.text-xs { font-size: 0.7rem; }

        /* ── Example cards: smaller + wrapped (no horizontal scroll) ── */
        .canvas-example-card { width: 82px; height: 82px; }
        #exampleCardsRow {
            overflow-x: hidden;
            max-width: calc(100vw - 1.25rem);
            padding-bottom: 6px;
            scrollbar-width: none;
            flex-wrap: wrap;
            justify-content: center;
            row-gap: 0.5rem;
        }
        #exampleCardsRow::-webkit-scrollbar { display: none; }

        /* ── Minichat: full-width bottom sheet ──────────────── */
        #minichat {
            width: calc(100vw - 1.5rem) !important;
            right: 0.75rem !important;
            left: auto !important;
            bottom: calc(var(--mobile-mini-dock-height) + env(safe-area-inset-bottom, 0px) + 0.75rem) !important;
            height: 52vh;
            max-height: 52vh;
        }
        #minichatMessages { max-height: none; }
        #minichatFab {
            right: 0.75rem !important;
            bottom: calc(var(--mobile-mini-dock-height) + env(safe-area-inset-bottom, 0px) + 0.75rem) !important;
        }

        /* When chat is collapsed on mobile, reclaim canvas height and center image naturally */
        body.mobile-canvas-chat-collapsed #canvasImageContainer {
            inset: 0 0 calc(var(--mobile-mini-dock-height) + env(safe-area-inset-bottom, 0px) + 0.75rem) 0;
            padding-bottom: 0.75rem;
        }
        body.mobile-canvas-chat-collapsed #canvasEmptyHint {
            inset: 0 0 calc(var(--mobile-mini-dock-height) + env(safe-area-inset-bottom, 0px) + 0.75rem) 0;
        }
        body.mobile-canvas-chat-collapsed #canvasMainImage {
            max-height: min(68vh, calc(100dvh - 11rem));
        }

        /* ── Sessions modal: full screen with small margin ───── */
        .sess-modal-backdrop { padding: 0.75rem; }
        .sess-modal-inner { border-radius: 1rem; max-height: 88vh; }
        /* Search input: narrower to fit flex row */
        #sessionSearch { width: 6rem; }
    }

    /* ════════════════════════════════════════════════════════
       TABLET  640 px – 1023 px
       ════════════════════════════════════════════════════════ */
    @media (min-width: 640px) and (max-width: 1023px) {

        /* ── Narrower minichat so it doesn't crowd the canvas ── */
        #minichat {
            width: 320px !important;
            right: calc(72px + 1rem) !important;
            max-height: 50vh;
        }
        #minichatFab { right: calc(72px + 1rem) !important; }

        /* ── Slightly smaller example cards ─────────────────── */
        .canvas-example-card { width: 118px; height: 118px; }

        /* ── Canvas image: allow a bit more width ────────────── */
        #canvasMainImage { max-width: min(80%, 640px); }
    }

    /* Mobile/tablet generation mode: collapse chat drawer + reclaim stage space */
    @media (max-width: 1023px) {
        body.mobile-canvas-generating #minichat {
            max-height: 52px !important;
        }
        body.mobile-canvas-generating #canvasImageContainer,
        body.mobile-canvas-generating #canvasEmptyHint {
            inset: 0 !important;
        }
        body.mobile-canvas-generating #canvasStage {
            border-bottom: none !important;
        }
    }
</style>
@endpush

@section('content')

<div id="studioWrap">

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- CANVAS STAGE                                        --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div id="canvasStage">

        {{-- Top-left toolbar group --}}
        <div id="canvasToolbarGroup">

            <div id="canvasToolbar">
                <div class="flex items-center gap-2 pr-3 border-r border-white/10">
                    <div class="size-6 rounded-md bg-gradient-to-br from-primary/30 to-secondary/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-sm">draw</span>
                    </div>
                    <span class="text-xs font-semibold text-white">Canvas Studio</span>
                </div>
                <button onclick="clearCanvas()" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-300 hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-sm">clear_all</span>
                    <span>Clear</span>
                </button>
            </div>

        </div>

        {{-- Top-right info --}}
        <div id="canvasInfo">
            <span class="material-symbols-outlined text-xs text-primary">electric_bolt</span>
            <span id="genCount">0 generations</span>
            <span class="text-slate-700">·</span>
            <span id="sessionLabel" class="text-slate-600 truncate max-w-28">New session</span>
        </div>

        {{-- Empty hint --}}
        <div id="canvasEmptyHint">
            @if(count($examples) > 0)
                {{-- Shuffled example images (2-3 shown, tooltips on hover) --}}
                <p class="text-xs text-slate-700 mb-3 tracking-wide uppercase" style="pointer-events:none; font-size:10px; letter-spacing:.08em;">Try an example</p>
                <div id="exampleCardsRow" class="flex gap-3 mb-4">
                    @foreach($examples as $ex)
                    <button class="canvas-example-card" onclick="useExamplePrompt({{ json_encode($ex['prompt']) }})">
                        <img src="{{ $ex['image_url'] }}"
                             alt="Example"
                             class="canvas-example-img">
                        <div class="canvas-example-tooltip">{{ $ex['prompt'] }}</div>
                        <span class="canvas-example-use-label">Use prompt</span>
                    </button>
                    @endforeach
                </div>
                <p class="text-xs text-slate-800" style="pointer-events:none;">
                    or describe your own in the chat&nbsp;
                    <span class="material-symbols-outlined text-xs align-middle" style="font-size:12px;">south_east</span>
                </p>
            @else
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/10 to-secondary/10 border border-white/5 flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl text-slate-700">image_search</span>
                </div>
                <p class="text-sm font-medium text-slate-700">Use the minichat to generate images</p>
                <p class="text-xs text-slate-800 mt-1">They'll appear here on the canvas</p>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-700">
                    <span class="material-symbols-outlined text-sm">south_east</span>
                    <span>Chat panel is bottom-right</span>
                </div>
            @endif
        </div>

        {{-- Centered generating state (mobile/tablet drawer mode friendly) --}}
        <div id="canvasGeneratingOverlay">
            <div class="canvas-gen-ring"></div>
            <p class="text-xs text-slate-400">Generating image...</p>
        </div>

        {{-- Main canvas image container --}}
        <div id="canvasImageContainer" style="display:none;">
            <div class="flex flex-col items-start">
                {{-- Quick action chips — above the image, left-aligned --}}
                <div id="canvasQuickActions"></div>
                <div class="relative">
                <img id="canvasMainImage" src="" alt="Generated" onclick="openLightbox(this.src)">
                <div id="canvasImageActions">
                    <button onclick="downloadCanvas()"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-black/70 hover:bg-black/90 text-white text-xs font-medium transition-colors backdrop-blur-sm border border-white/10">
                        <span class="material-symbols-outlined text-sm">download</span>
                    </button>
                    <button onclick="openLightbox(document.getElementById('canvasMainImage').src)"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-black/70 hover:bg-black/90 text-white text-xs font-medium transition-colors backdrop-blur-sm border border-white/10">
                        <span class="material-symbols-outlined text-sm">open_in_full</span>
                    </button>
                </div>
                </div>{{-- /.relative --}}
            </div>{{-- /.flex.flex-col --}}
        </div>{{-- /#canvasImageContainer --}}

    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- FLOW STRIP (right, vertical)                        --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div id="flowStrip">
        <div id="flowStripEmpty" class="flex flex-col items-center justify-center h-full gap-2 py-8">
            <span class="material-symbols-outlined text-slate-800 text-2xl">account_tree</span>
        </div>
        <div id="flowStripItems" class="w-full flex flex-col items-center" style="display:none !important;">
            {{-- thumbnails injected by JS --}}
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MINICHAT (floating, position: absolute inside studioWrap)  --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="minichat" style="position:fixed; bottom:1.25rem; right:calc(72px + 84px + 1.25rem);">

    {{-- Header --}}
    <div id="minichatHeader" onclick="toggleMinichat()">
        <div class="size-6 rounded-md bg-gradient-to-br from-primary/30 to-secondary/30 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary text-sm">smart_toy</span>
        </div>
        <span class="text-xs font-semibold text-white flex-1">AI Studio Chat</span>

        {{-- Sessions button --}}
        <button onclick="event.stopPropagation(); openSessionsModal()"
            id="sessionsToggleBtn"
            title="Past sessions"
            class="size-7 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors flex-shrink-0">
            <span class="material-symbols-outlined text-sm">history</span>
        </button>

        {{-- New chat button --}}
        <button onclick="event.stopPropagation(); newChat()"
            title="New chat"
            class="size-7 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors flex-shrink-0">
            <span class="material-symbols-outlined text-sm">add</span>
        </button>

        {{-- Collapse toggle --}}
        <button id="minichatCollapseBtn"
            title="Collapse / expand"
            class="size-7 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors flex-shrink-0">
            <span class="material-symbols-outlined text-sm" id="minichatCollapseIcon">expand_more</span>
        </button>
    </div>

    {{-- Messages --}}
    <div id="minichatMessages">
        <div id="minichatEmpty" class="flex flex-col items-center justify-center py-8 gap-2 text-center">
            <span class="material-symbols-outlined text-2xl text-slate-700">chat_bubble_outline</span>
            <p class="text-[11px] text-slate-600">Describe an image to generate</p>
        </div>
    </div>

    {{-- Input zone --}}
    <div id="minichatInput">
        {{-- Image preview --}}
        <div id="miniRefPreview" class="hidden mb-2">
            <div class="relative inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-primary/10 border border-primary/20">
                <img id="miniRefThumb"
                     src=""
                     class="w-7 h-7 rounded object-cover cursor-zoom-in"
                     title="Open image"
                     onclick="if (this.src) openLightbox(this.src)">
                <span class="text-[10px] text-primary font-medium">Reference image</span>
                <button onclick="removeRef()" class="size-4 rounded-full bg-white/10 hover:bg-red-500/50 text-white flex items-center justify-center ml-1">
                    <span class="material-symbols-outlined text-[10px]">close</span>
                </button>
            </div>
        </div>

        <div class="flex gap-2 items-end">
            <button onclick="document.getElementById('miniFileInput').click()"
                title="Upload reference image"
                class="flex-shrink-0 size-8 rounded-xl bg-white/5 hover:bg-white/10 border border-white/07 text-slate-500 hover:text-primary flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-base">image</span>
            </button>
            <input type="file" id="miniFileInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden" onchange="handleRefImage(this)">

            <textarea id="miniPromptInput"
                placeholder="Describe your image…"
                rows="1"
                class="flex-1 w-full"
                onkeydown="handleMiniKey(event)"
                oninput="miniResize(this)"></textarea>

            <button id="miniSendBtn" onclick="sendMiniChat()"
                class="flex-shrink-0 size-8 rounded-xl bg-primary hover:bg-primary/85 text-white flex items-center justify-center transition-all shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-base" id="miniSendIcon">arrow_upward</span>
            </button>
        </div>
        <p class="text-[9px] text-slate-700 text-center mt-1.5">5 credits per generation · Shift+Enter for new line</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- SESSIONS MODAL                                             --}}
{{-- style="display:none" here so JS sets display:flex — avoids --}}
{{-- CSS ID-selector specificity overriding hidden state        --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="sessionsModal" class="sess-modal-backdrop" style="display:none;" onclick="closeSessionsModal()">
    <div class="sess-modal-inner" onclick="event.stopPropagation()">

        {{-- Modal header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/07 flex-shrink-0">
            <div class="size-7 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-sm">history</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white">Past Sessions</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Click any session to load &amp; continue it</p>
            </div>
            <input id="sessionSearch" type="text" placeholder="Search sessions…"
                oninput="filterSessions(this.value)"
                class="text-xs bg-white/05 border border-white/07 rounded-xl px-3 py-1.5 text-slate-300 placeholder-slate-600 outline-none w-36 focus:border-primary/40 flex-shrink-0">
            <button onclick="closeSessionsModal()"
                class="size-7 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors flex-shrink-0">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>

        {{-- Session cards (JS-populated) --}}
        <div id="sessionCards">
            <div class="flex flex-col items-center justify-center py-10 gap-2">
                <div class="size-5 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                <p class="text-xs text-slate-600">Loading…</p>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- LIGHTBOX                                                   --}}
{{-- style="display:none" — JS sets display:flex to open       --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="lightbox" class="lightbox-backdrop" style="display:none;" onclick="closeLightbox()">
    <div class="relative flex items-center justify-center" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt=""
            class="max-w-full max-h-[88vh] rounded-2xl shadow-2xl object-contain"
            style="max-width: min(90vw, 960px);">
        <div class="absolute top-3 right-3 flex gap-2">
            <a id="lightboxDownload" href="#" download="generated.jpg"
                class="size-9 rounded-xl bg-black/60 hover:bg-black text-white flex items-center justify-center transition-colors border border-white/10"
                onclick="event.stopPropagation()">
                <span class="material-symbols-outlined text-sm">download</span>
            </a>
            <button onclick="closeLightbox()"
                class="size-9 rounded-xl bg-black/60 hover:bg-black text-white flex items-center justify-center transition-colors border border-white/10">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    </div>
</div>

<nav id="mobileMiniDock" aria-label="Image tools mini menu">
    <a href="{{ route('image-generator.index') }}" class="mobile-mini-link {{ request()->routeIs('image-generator.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined mini-icon">image</span>
        <span>Generator</span>
    </a>
    <a href="{{ route('playground.canvas') }}" class="mobile-mini-link {{ request()->routeIs('playground.canvas') ? 'active' : '' }}">
        <span class="material-symbols-outlined mini-icon">draw</span>
        <span>Canvas</span>
    </a>
    <a href="{{ route('nano.visual.tools') }}" class="mobile-mini-link {{ request()->routeIs('nano.visual.tools') ? 'active' : '' }}">
        <span class="material-symbols-outlined mini-icon">tune</span>
        <span>Tools</span>
    </a>
    <a href="{{ route('gallery') }}" class="mobile-mini-link {{ request()->routeIs('gallery') ? 'active' : '' }}">
        <span class="material-symbols-outlined mini-icon">photo_library</span>
        <span>Gallery</span>
    </a>
</nav>

@endsection

@push('scripts')
<script>
// ── State ─────────────────────────────────────────────────────────────────────
const S = {
    sessionId:      null,
    conversationId: null,
    messages:       [],   // {role, prompt, imageUrl, textResponse, ts}
    refFile:        null,
    isGenerating:   false,
    minichatOpen:   true,
    activeFlowIdx:  -1,
    preGenMiniOpen: true,
};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Auto-collapse sidebar for full-screen canvas experience (desktop only)
    const sidebar = document.getElementById('appSidebar');
    const main    = document.getElementById('appMain');
    const icon    = document.getElementById('sidebarToggleIcon');
    if (sidebar && !sidebar.classList.contains('sidebar-collapsed')) {
        sidebar.classList.add('sidebar-collapsed');
        // On mobile the sidebar slides in via -translate-x-full, not margin — skip margin
        if (main && window.innerWidth >= 1024) main.style.marginLeft = '4.5rem';
        if (icon) icon.textContent = 'chevron_right';
    }

    newChat();
    setupDragDrop();
    syncMobileCanvasLayoutState();
});

// ── Session management ────────────────────────────────────────────────────────
function newChat() {
    S.sessionId      = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2,8);
    S.conversationId = null;
    S.messages       = [];
    S.refFile        = null;
    S.activeFlowIdx  = -1;
    removeRef();
    renderMiniMessages();
    renderFlowStrip();
    renderCanvasImage(null);
    renderQuickActions({});
    document.getElementById('sessionLabel').textContent = 'New session';
    document.getElementById('genCount').textContent     = '0 generations';
}

async function loadSession(sessionId) {
    closeSessionsModal();

    // Immediately show loading in messages area; dim input so user knows to wait
    const msgEl   = document.getElementById('minichatMessages');
    const inputEl = document.getElementById('minichatInput');
    msgEl.innerHTML = `
        <div class="flex flex-col items-center justify-center py-10 gap-2">
            <div class="size-6 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
            <p class="text-xs text-slate-500">Loading session…</p>
        </div>`;
    inputEl.style.opacity        = '0.4';
    inputEl.style.pointerEvents  = 'none';

    const restore = () => {
        inputEl.style.opacity       = '';
        inputEl.style.pointerEvents = '';
    };

    try {
        const r    = await fetch(`/playground/api/session/${sessionId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }
        });
        const data = await r.json();

        if (!data.success) {
            renderMiniMessages(); // revert to whatever was there
            restore();
            return;
        }

        const sess = data.session;
        S.sessionId      = sess.session_id;
        S.conversationId = sess.conversation_id;
        S.messages       = sess.messages || [];
        S.refFile        = null;
        removeRef();

        // Set activeFlowIdx to last generated image
        const imgs = S.messages.filter(m => m.imageUrl);
        S.activeFlowIdx = imgs.length - 1;

        renderMiniMessages();
        renderFlowStrip();

        // Show last generated image on canvas (or clear if none)
        const lastImg = [...S.messages].reverse().find(m => m.imageUrl);
        renderCanvasImage(lastImg ? lastImg.imageUrl : null);
        renderQuickActions(lastImg ? (lastImg.quickActions || {}) : {});

        document.getElementById('genCount').textContent     = `${imgs.length} generation${imgs.length !== 1 ? 's' : ''}`;
        document.getElementById('sessionLabel').textContent = truncate(S.messages.find(m => m.role === 'user')?.prompt || 'Session', 20);

        restore();

        // Focus input so user can continue chatting immediately
        document.getElementById('miniPromptInput').focus();

    } catch (e) {
        console.error('Load session error', e);
        renderMiniMessages();
        restore();
    }
}

async function persistSession() {
    if (!S.messages.length) return;
    try {
        await fetch('{{ route("playground.api.session.save") }}', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
            body:    JSON.stringify({
                session_id:      S.sessionId,
                conversation_id: S.conversationId,
                messages:        S.messages,
                results:         S.messages.filter(m => m.imageUrl).map(m => ({ imageUrl: m.imageUrl, prompt: m.prompt })),
            }),
        });
    } catch (e) { console.error('Persist error', e); }
}

// ── Sending ────────────────────────────────────────────────────────────────────
async function sendMiniChat() {
    if (S.isGenerating) return;
    const input  = document.getElementById('miniPromptInput');
    const prompt = input.value.trim();
    if (!prompt) return;

    S.isGenerating = true;
    setMiniGenerating(true);
    input.value = '';
    miniResize(input);

    // Append user message
    const userMsg = { role: 'user', prompt, thumb: S.refFile ? URL.createObjectURL(S.refFile) : null, ts: Date.now() };
    S.messages.push(userMsg);
    appendMiniMessage(userMsg);

    // Hide empty hint (may not exist in DOM when a session was loaded)
    const emptyHint = document.getElementById('minichatEmpty');
    if (emptyHint) emptyHint.style.display = 'none';

    showMiniTyping();

    // FormData
    const fd = new FormData();
    fd.append('prompt', prompt);
    fd.append('_token', CSRF);
    if (S.conversationId)  fd.append('conversation_id', S.conversationId);
    if (S.refFile)         fd.append('canvas_image', S.refFile);

    const chatAc = new AbortController();
    const chatTimer = setTimeout(() => chatAc.abort(), 180000);

    try {
        const r    = await fetch('{{ route("playground.api.chat") }}', { method: 'POST', signal: chatAc.signal, body: fd });
        clearTimeout(chatTimer);
        const data = await r.json();

        hideMiniTyping();

        if (data.success) {
            S.conversationId = data.conversation_id || S.conversationId;

            const aiMsg = {
                role:         'ai',
                imageUrl:     data.image_url    || null,
                textResponse: data.text_response || null,
                quickActions: data.quick_actions || {},
                prompt,
                ts:           Date.now(),
            };
            S.messages.push(aiMsg);
            appendMiniMessage(aiMsg);

            // Update canvas with new image
            if (aiMsg.imageUrl) {
                renderCanvasImage(aiMsg.imageUrl);
                renderFlowStrip();
                renderQuickActions(data.quick_actions || {});
                const imgs = S.messages.filter(m => m.imageUrl);
                document.getElementById('genCount').textContent = `${imgs.length} generation${imgs.length !== 1 ? 's' : ''}`;
                document.getElementById('sessionLabel').textContent = truncate(prompt, 20);
                S.activeFlowIdx = imgs.length - 1;
                updateFlowActiveState();
            }

            persistSession();
        } else {
            appendMiniError(data.error || data.message || 'Generation failed.');
        }
    } catch (e) {
        clearTimeout(chatTimer);
        hideMiniTyping();
        appendMiniError(e.name === 'AbortError' ? 'Request timed out — please try again.' : 'Network error — could not reach the server.');
    }

    S.refFile = null;
    removeRef();
    S.isGenerating = false;
    setMiniGenerating(false);
}

// ── Render: mini messages ─────────────────────────────────────────────────────
function getOrCreateEmpty() {
    let empty = document.getElementById('minichatEmpty');
    if (!empty) {
        // Recreate if it was destroyed by the loading-state innerHTML replacement
        empty = document.createElement('div');
        empty.id = 'minichatEmpty';
        empty.className = 'flex flex-col items-center justify-center py-8 gap-2 text-center';
        empty.innerHTML =
            '<span class="material-symbols-outlined text-2xl text-slate-700">chat_bubble_outline</span>' +
            '<p class="text-[11px] text-slate-600">Describe an image to generate</p>';
    }
    return empty;
}

function renderMiniMessages() {
    const el    = document.getElementById('minichatMessages');
    const empty = getOrCreateEmpty();

    if (!S.messages.length) {
        el.innerHTML = '';
        el.appendChild(empty);
        empty.style.display = '';
        return;
    }

    empty.style.display = 'none';
    if (el.contains(empty)) el.removeChild(empty);
    el.innerHTML = S.messages.map(buildMiniMsgHTML).join('');
    el.scrollTop = el.scrollHeight;
}

function buildMiniMsgHTML(msg) {
    if (msg.role === 'user') {
        const thumbHTML = msg.thumb
            ? `<img src="${esc(msg.thumb)}"
                    data-full="${esc(msg.thumb)}"
                    onclick="openLightbox(this.dataset.full)"
                    class="w-10 h-10 rounded-lg object-cover mb-1.5 border border-white/10 cursor-zoom-in"
                    title="Open image">`
            : '';
        return `
        <div class="mini-msg-user flex justify-end">
            <div class="mini-bubble px-3 py-2">
                ${thumbHTML}
                <p class="text-xs text-slate-200">${esc(msg.prompt)}</p>
            </div>
        </div>`;
    }
    const imgHTML = msg.imageUrl ? `
        <img src="${esc(msg.imageUrl)}" class="mini-img-thumb mb-1.5"
             onclick="openLightbox('${esc(msg.imageUrl)}')">` : '';
    const textHTML = msg.textResponse
        ? `<p class="text-[11px] text-slate-400 leading-relaxed">${esc(truncate(msg.textResponse, 120))}</p>` : '';
    return `
    <div class="mini-msg-ai flex">
        <div class="mini-bubble px-3 py-2 overflow-hidden">
            ${imgHTML}
            ${textHTML}
        </div>
    </div>`;
}

function appendMiniMessage(msg) {
    const el    = document.getElementById('minichatMessages');
    const empty = document.getElementById('minichatEmpty');
    if (empty && el.contains(empty)) { empty.style.display = 'none'; }
    const div = document.createElement('div');
    div.innerHTML = buildMiniMsgHTML(msg);
    el.appendChild(div.firstElementChild);
    el.scrollTop = el.scrollHeight;
}

function appendMiniError(msg) {
    if (window.showApiErrorToast) {
        window.showApiErrorToast({ message: msg });
    } else if (window.appToast) {
        window.appToast(msg, 'error');
    }
}

// Typing indicator
let typingEl = null;
function showMiniTyping() {
    hideMiniTyping();
    const el = document.getElementById('minichatMessages');
    typingEl = document.createElement('div');
    typingEl.className = 'mini-msg-ai flex';
    typingEl.innerHTML = `
        <div class="mini-bubble px-3 py-2.5 flex gap-1 items-center">
            <div class="mini-dot"></div><div class="mini-dot"></div><div class="mini-dot"></div>
        </div>`;
    el.appendChild(typingEl);
    el.scrollTop = el.scrollHeight;
}
function hideMiniTyping() { if (typingEl) { typingEl.remove(); typingEl = null; } }

// ── Render: canvas ────────────────────────────────────────────────────────────
function renderCanvasImage(url) {
    const container = document.getElementById('canvasImageContainer');
    const img       = document.getElementById('canvasMainImage');
    const hint      = document.getElementById('canvasEmptyHint');

    if (!url) {
        container.style.display = 'none';
        hint.style.display = '';
        return;
    }

    hint.style.display = 'none';
    img.src = url;
    img.style.animation = 'none';
    void img.offsetWidth; // reflow
    img.style.animation = '';
    container.style.display = 'flex';
}

// ── Render: flow strip ────────────────────────────────────────────────────────
function renderFlowStrip() {
    const items   = document.getElementById('flowStripItems');
    const empty   = document.getElementById('flowStripEmpty');
    const images  = S.messages.filter(m => m.imageUrl);

    if (!images.length) {
        items.style.setProperty('display', 'none', 'important');
        empty.style.display = '';
        return;
    }

    empty.style.display = 'none';
    items.style.removeProperty('display');
    items.style.display = 'flex';
    items.style.flexDirection = 'column';
    items.style.alignItems = 'center';
    items.style.width = '100%';

    items.innerHTML = images.map((msg, i) => `
        <div class="flow-thumb-wrap">
            ${i > 0 ? '<div class="flow-connector-line"></div>' : '<div style="height:6px"></div>'}
            <img src="${esc(msg.imageUrl)}"
                 class="flow-thumb ${i === S.activeFlowIdx ? 'active' : ''}"
                 title="${esc(truncate(msg.prompt||'',60))}"
                 onclick="setCanvasFromFlow(${i},'${esc(msg.imageUrl)}')">
            <span class="flow-thumb-label">${i+1}</span>
        </div>
    `).join('');
}

function setCanvasFromFlow(idx, url) {
    S.activeFlowIdx = idx;
    renderCanvasImage(url);
    updateFlowActiveState();
    // Restore quick actions for the selected image
    const imgs = S.messages.filter(m => m.imageUrl);
    const msg  = imgs[idx];
    renderQuickActions(msg ? (msg.quickActions || {}) : {});
}

function updateFlowActiveState() {
    document.querySelectorAll('#flowStripItems .flow-thumb').forEach((el, i) => {
        el.classList.toggle('active', i === S.activeFlowIdx);
    });
}

// ── Minichat toggle ───────────────────────────────────────────────────────────
function toggleMinichat() {
    const panel = document.getElementById('minichat');
    const icon  = document.getElementById('minichatCollapseIcon');
    S.minichatOpen = !S.minichatOpen;
    panel.classList.toggle('collapsed', !S.minichatOpen);
    icon.textContent = S.minichatOpen ? 'expand_more' : 'expand_less';
    syncMobileCanvasLayoutState();
}

// ── Sessions modal ────────────────────────────────────────────────────────────
let allSessions = [];

function openSessionsModal() {
    const btn = document.getElementById('sessionsToggleBtn');
    document.getElementById('sessionsModal').style.display = 'flex';
    document.getElementById('sessionSearch').value = '';
    btn.classList.add('text-primary');
    loadSessions();
}

function closeSessionsModal() {
    document.getElementById('sessionsModal').style.display = 'none';
    document.getElementById('sessionsToggleBtn').classList.remove('text-primary');
}

async function loadSessions() {
    document.getElementById('sessionCards').innerHTML = `
        <div class="flex flex-col items-center justify-center py-10 gap-2">
            <div class="size-5 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
            <p class="text-xs text-slate-600">Loading…</p>
        </div>`;
    try {
        const r    = await fetch('{{ route("playground.api.sessions") }}', {
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }
        });
        const data = await r.json();
        allSessions = (data.success && data.sessions) ? data.sessions : [];
        renderSessionCards(allSessions);
    } catch {
        document.getElementById('sessionCards').innerHTML =
            '<p class="text-xs text-slate-600 text-center py-8">Could not load sessions</p>';
    }
}

function renderSessionCards(sessions) {
    const el = document.getElementById('sessionCards');
    if (!sessions.length) {
        el.innerHTML = `
            <div class="flex flex-col items-center justify-center py-10 gap-2">
                <span class="material-symbols-outlined text-3xl text-slate-700">history</span>
                <p class="text-xs text-slate-600">No past sessions found</p>
            </div>`;
        return;
    }
    el.innerHTML = sessions.map(s => {
        const msgs     = Array.isArray(s.messages) ? s.messages : [];
        const preview  = msgs.find(m => m.role === 'user')?.prompt || 'Empty session';
        const imgCount = msgs.filter(m => m.imageUrl).length;
        const thumb    = msgs.find(m => m.imageUrl)?.imageUrl || null;
        const date     = formatDate(s.last_updated_at || s.updated_at);
        const isCurrent = s.session_id === S.sessionId;

        const thumbHTML = thumb
            ? `<img src="${esc(thumb)}" class="sess-card-thumb" alt="">`
            : `<div class="sess-card-no-thumb"><span class="material-symbols-outlined text-slate-600 text-lg">image</span></div>`;

        return `
        <div class="sess-card${isCurrent ? ' is-current' : ''}" onclick="loadSession('${esc(s.session_id)}')">
            ${thumbHTML}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                    <p class="text-xs font-medium text-slate-200 truncate">${esc(truncate(preview, 44))}</p>
                    ${isCurrent ? '<span class="text-[9px] px-1.5 py-0.5 rounded-full bg-primary/15 text-primary font-medium flex-shrink-0">active</span>' : ''}
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] text-slate-500">${date}</span>
                    <span class="text-slate-700 text-[10px]">·</span>
                    <span class="text-[10px] text-slate-500">${imgCount} image${imgCount !== 1 ? 's' : ''}</span>
                </div>
            </div>
            <span class="material-symbols-outlined text-slate-600 flex-shrink-0">chevron_right</span>
        </div>`;
    }).join('');
}

function filterSessions(q) {
    const filtered = q.trim()
        ? allSessions.filter(s => {
            const msgs = Array.isArray(s.messages) ? s.messages : [];
            return msgs.map(m => m.prompt || '').join(' ').toLowerCase().includes(q.toLowerCase());
          })
        : allSessions;
    renderSessionCards(filtered);
}

// ── Lightbox ──────────────────────────────────────────────────────────────────
function openLightbox(url) {
    document.getElementById('lightboxImg').src       = url;
    document.getElementById('lightboxDownload').href = url;
    document.getElementById('lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.getElementById('lightboxImg').src = '';
}

// Close modals on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeLightbox(); closeSessionsModal(); }
});

// ── Canvas actions ─────────────────────────────────────────────────────────────
function clearCanvas() {
    if (S.messages.length && !confirm('Clear canvas and start a new session?')) return;
    persistSession();
    newChat();
}

function downloadCanvas() {
    const url = document.getElementById('canvasMainImage').src;
    if (!url) return;
    const a   = document.createElement('a');
    a.href    = url;
    a.download = 'generated.jpg';
    a.click();
}

// ── Reference image ───────────────────────────────────────────────────────────
function handleRefImage(input) {
    const file = input.files[0];
    if (!file) return;
    S.refFile = file;
    document.getElementById('miniRefThumb').src = URL.createObjectURL(file);
    document.getElementById('miniRefPreview').classList.remove('hidden');
    input.value = '';
}
function removeRef() {
    S.refFile = null;
    document.getElementById('miniRefPreview').classList.add('hidden');
    document.getElementById('miniRefThumb').src = '';
}

// ── Drag and drop into stage ───────────────────────────────────────────────────
function setupDragDrop() {
    const stage = document.getElementById('canvasStage');
    stage.addEventListener('dragover', e => { e.preventDefault(); stage.classList.add('drag-over'); });
    stage.addEventListener('dragleave', e => { if (!stage.contains(e.relatedTarget)) stage.classList.remove('drag-over'); });
    stage.addEventListener('drop', e => {
        e.preventDefault();
        stage.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            S.refFile = file;
            document.getElementById('miniRefThumb').src = URL.createObjectURL(file);
            document.getElementById('miniRefPreview').classList.remove('hidden');
            if (!S.minichatOpen) toggleMinichat();
            document.getElementById('miniPromptInput').focus();
        }
    });
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function setMiniGenerating(gen) {
    applyMobileGeneratingDrawer(gen);
    document.getElementById('miniSendBtn').disabled      = gen;
    document.getElementById('miniSendIcon').textContent  = gen ? 'hourglass_top' : 'arrow_upward';
    if (gen) document.getElementById('miniSendIcon').classList.add('animate-spin');
    else     document.getElementById('miniSendIcon').classList.remove('animate-spin');
    const overlay = document.getElementById('canvasGeneratingOverlay');
    if (overlay) overlay.style.display = gen ? 'flex' : 'none';
}

function isMobileTabletViewport() {
    return window.matchMedia('(max-width: 1023px)').matches;
}

function applyMobileGeneratingDrawer(isGenerating) {
    const panel = document.getElementById('minichat');
    const icon = document.getElementById('minichatCollapseIcon');
    if (!panel || !icon) return;

    if (!isMobileTabletViewport()) {
        document.body.classList.remove('mobile-canvas-generating');
        panel.classList.remove('collapsed');
        S.minichatOpen = true;
        icon.textContent = 'expand_more';
        syncMobileCanvasLayoutState();
        return;
    }

    if (!isGenerating) {
        document.body.classList.remove('mobile-canvas-generating');
        // Keep it closed after generation; user can reopen manually via header toggle.
        S.minichatOpen = false;
        panel.classList.add('collapsed');
        icon.textContent = 'expand_less';
        syncMobileCanvasLayoutState();
        return;
    }

    S.preGenMiniOpen = S.minichatOpen;
    document.body.classList.add('mobile-canvas-generating');
    S.minichatOpen = false;
    panel.classList.add('collapsed');
    icon.textContent = 'expand_less';
    syncMobileCanvasLayoutState();
}

function syncMobileCanvasLayoutState() {
    const collapsed = isMobileTabletViewport() && !S.minichatOpen;
    document.body.classList.toggle('mobile-canvas-chat-collapsed', collapsed);
}

function miniResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

function handleMiniKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMiniChat(); }
}

window.addEventListener('resize', () => {
    if (!isMobileTabletViewport()) applyMobileGeneratingDrawer(false);
    syncMobileCanvasLayoutState();
});

// ── Canvas example prompts ────────────────────────────────────────────────────
function useExamplePrompt(prompt) {
    const input = document.getElementById('miniPromptInput');
    if (!input) return;
    input.value = prompt;
    miniResize(input);
    input.focus();
}

// ── Quick actions ─────────────────────────────────────────────────────────────
function sendQuickAction(prompt) {
    if (S.isGenerating) return;
    const input = document.getElementById('miniPromptInput');
    input.value = prompt;
    miniResize(input);
    sendMiniChat();
}

/**
 * Render API-returned quick actions as chips under the canvas toolbar.
 * @param {Object} actions  e.g. { "Night scene": "Transform the car to a rainy night..." }
 */
function renderQuickActions(actions) {
    const el = document.getElementById('canvasQuickActions');

    // Guard: API should always return a plain object {label: promptString}
    // but defensively skip any entry whose value isn't a string (e.g. nested object)
    const raw     = (actions && typeof actions === 'object' && !Array.isArray(actions)) ? actions : {};
    const entries = Object.entries(raw).filter(([, v]) => typeof v === 'string' && v.length > 0);

    if (!entries.length) {
        el.style.display = 'none';
        el.innerHTML     = '';
        return;
    }
    el.innerHTML = entries.map(([label, prompt]) =>
        `<button class="canvas-quick-chip" onclick="sendQuickAction('${esc(prompt)}')">${esc(label)}</button>`
    ).join('');
    el.style.display = 'flex';
}

// ── Utils ─────────────────────────────────────────────────────────────────────
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function truncate(s, n) { return s.length > n ? s.slice(0,n) + '…' : s; }
function formatDate(d) {
    if (!d) return '';
    const dt  = new Date(d);
    const now = new Date();
    if (dt.toDateString() === now.toDateString()) return dt.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    return dt.toLocaleDateString([],{month:'short',day:'numeric'});
}

</script>
@endpush
