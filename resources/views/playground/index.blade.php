@extends('layouts.app')

@section('title', 'AI Playground — Clever Creator')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────────── */
    #playgroundWrap {
        display: flex;
        height: calc(100vh - 5rem);   /* topbar is h-20 = 5rem */
        gap: 0;
        overflow: hidden;
    }

    /* ── Session Sidebar ────────────────────────────────── */
    #sessionSidebar {
        width: 280px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: rgba(22, 27, 34, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.05);
        transition: width 0.25s ease;
        overflow: hidden;
    }
    #sessionSidebar.collapsed { width: 0; }

    /* ── Chat Area ──────────────────────────────────────── */
    #chatArea {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        background: rgba(10, 10, 12, 0.5);
    }

    /* ── Flow Timeline Panel ────────────────────────────── */
    #flowPanel {
        width: 300px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: rgba(22, 27, 34, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-left: 1px solid rgba(255,255,255,0.05);
        overflow: hidden;
        transition: width 0.25s ease;
    }
    #flowPanel.collapsed { width: 0; }

    /* ── Messages ───────────────────────────────────────── */
    #messagesContainer {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        scroll-behavior: smooth;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .msg-user .bubble {
        background: linear-gradient(135deg, rgba(19,164,236,0.18) 0%, rgba(139,92,246,0.12) 100%);
        border: 1px solid rgba(19,164,236,0.25);
        border-radius: 1rem 1rem 0.25rem 1rem;
        margin-left: auto;
        max-width: 72%;
    }
    .msg-ai .bubble {
        background: rgba(22,27,34,0.75);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 1rem 1rem 1rem 0.25rem;
        max-width: 72%;
    }

    /* ── Generated image in bubble ──────────────────────── */
    .gen-image-wrap {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
        background: rgba(0,0,0,0.3);
    }
    .gen-image-wrap img {
        width: 100%;
        display: block;
        border-radius: 0.75rem;
        object-fit: cover;
        max-height: 400px;
    }
    .gen-image-actions {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
        display: flex;
        gap: 0.35rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .gen-image-wrap:hover .gen-image-actions { opacity: 1; }

    /* ── Input zone ─────────────────────────────────────── */
    #inputZone {
        border-top: 1px solid rgba(255,255,255,0.06);
        padding: 1rem 1.25rem;
        background: rgba(15,17,22,0.8);
        backdrop-filter: blur(12px);
    }

    #promptInput {
        background: rgba(22,27,34,0.9);
        border: 1px solid rgba(255,255,255,0.08);
        color: #e2e8f0;
        resize: none;
        border-radius: 0.875rem;
        outline: none;
        transition: border-color 0.2s;
        line-height: 1.5;
        min-height: 52px;
        max-height: 160px;
        overflow-y: auto;
    }
    #promptInput:focus { border-color: rgba(19,164,236,0.5); }
    #promptInput::placeholder { color: rgba(148,163,184,0.5); }

    /* ── Image preview in input ─────────────────────────── */
    #imagePreviewZone {
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid rgba(19,164,236,0.3);
        background: rgba(0,0,0,0.2);
        position: relative;
        display: inline-block;
    }
    #imagePreviewZone img {
        display: block;
        max-height: 120px;
        border-radius: 0.75rem;
    }

    /* ── Session list items ─────────────────────────────── */
    .session-item {
        padding: 0.75rem 1rem;
        border-radius: 0.625rem;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s;
    }
    .session-item:hover {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.06);
    }
    .session-item.active {
        background: rgba(19,164,236,0.1);
        border-color: rgba(19,164,236,0.25);
    }

    /* ── Flow timeline nodes ─────────────────────────────── */
    .flow-node {
        background: rgba(22,27,34,0.7);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.75rem;
        padding: 0.625rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .flow-node:hover {
        border-color: rgba(19,164,236,0.35);
        background: rgba(19,164,236,0.07);
    }
    .flow-connector {
        width: 2px;
        height: 1.5rem;
        background: linear-gradient(to bottom, rgba(19,164,236,0.5), rgba(139,92,246,0.5));
        margin: 0 auto;
    }

    /* ── Typing indicator ───────────────────────────────── */
    .typing-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #13a4ec;
        animation: typingPulse 1.4s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingPulse {
        0%, 80%, 100% { opacity: 0.2; transform: scale(0.85); }
        40%            { opacity: 1;   transform: scale(1.1); }
    }

    /* ── Empty state ─────────────────────────────────────── */
    #emptyState { animation: fadeUp 0.4s ease; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Scrollbar ──────────────────────────────────────── */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 99px; }

    /* ── Drop zone ──────────────────────────────────────── */
    #dropOverlay {
        position: absolute; inset: 0;
        background: rgba(19,164,236,0.08);
        border: 2px dashed rgba(19,164,236,0.5);
        border-radius: 0.875rem;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        pointer-events: none;
    }
    #chatArea.dragging #dropOverlay { display: flex; }

    /* Remove default p-10 spacing so playground fills space */
    #appMain > div.p-10 { padding: 0 !important; }
</style>
@endpush

@section('content')

<div id="playgroundWrap">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- SESSION SIDEBAR                                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div id="sessionSidebar">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-4 flex-shrink-0 border-b border-white/5">
            <div>
                <h2 class="text-sm font-semibold text-white">Conversations</h2>
                <p class="text-[10px] text-slate-500 mt-0.5">Your past sessions</p>
            </div>
            <button onclick="newSession()"
                class="size-8 rounded-lg bg-primary/15 hover:bg-primary/25 text-primary flex items-center justify-center transition-colors"
                title="New session">
                <span class="material-symbols-outlined text-base">add</span>
            </button>
        </div>

        {{-- Search --}}
        <div class="px-3 py-2 flex-shrink-0">
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 border border-white/5">
                <span class="material-symbols-outlined text-slate-500 text-base">search</span>
                <input id="sessionSearch" type="text" placeholder="Search…"
                    class="bg-transparent text-xs text-slate-300 placeholder-slate-600 outline-none flex-1 w-full"
                    oninput="filterSessions(this.value)">
            </div>
        </div>

        {{-- Session list --}}
        <div id="sessionList" class="flex-1 overflow-y-auto px-2 py-1 space-y-1">
            <div class="text-center py-8">
                <div class="size-8 border-2 border-primary/30 border-t-primary rounded-full animate-spin mx-auto mb-2"></div>
                <p class="text-xs text-slate-500">Loading sessions…</p>
            </div>
        </div>

        {{-- Footer toggle --}}
        <div class="p-3 flex-shrink-0 border-t border-white/5">
            <p class="text-[10px] text-slate-600 text-center">Sessions auto-save</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- CHAT AREA                                                --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div id="chatArea" class="relative">

        {{-- Drop overlay --}}
        <div id="dropOverlay">
            <div class="text-center pointer-events-none">
                <span class="material-symbols-outlined text-4xl text-primary">image</span>
                <p class="text-sm font-medium text-primary mt-1">Drop image here</p>
            </div>
        </div>

        {{-- Topbar --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-white/5 flex-shrink-0 bg-[rgba(10,10,12,0.7)]">
            <div class="flex items-center gap-3">
                {{-- Toggle session sidebar --}}
                <button onclick="toggleSessionSidebar()" title="Toggle sessions"
                    class="size-8 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-lg">menu_open</span>
                </button>
                <div>
                    <h1 id="chatTitle" class="text-sm font-semibold text-white">New Playground</h1>
                    <p class="text-[10px] text-slate-500" id="chatSubtitle">Start a conversation to generate images</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="clearChat()"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-base">restart_alt</span>
                    <span>New chat</span>
                </button>
                <button onclick="toggleFlowPanel()" title="Toggle image flow"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-base">account_tree</span>
                    <span>Flow</span>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="messagesContainer">
            {{-- Empty state --}}
            <div id="emptyState" class="flex flex-col items-center justify-center h-full text-center py-16">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary/20 to-secondary/20 border border-white/10 flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-3xl text-primary">smart_toy</span>
                </div>
                <h2 class="text-xl font-semibold text-white mb-2">AI Image Playground</h2>
                <p class="text-sm text-slate-400 max-w-sm mb-6">Describe what you want to create or upload a reference image and tell the AI how to transform it.</p>
                <div class="grid grid-cols-2 gap-2 max-w-sm w-full">
                    <button onclick="setPromptSuggestion('A futuristic city at sunset with neon lights reflecting in rain puddles')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-primary text-sm block mb-1">city</span>
                        Futuristic city at sunset
                    </button>
                    <button onclick="setPromptSuggestion('A dreamy forest with bioluminescent mushrooms and fireflies in the night')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-secondary text-sm block mb-1">forest</span>
                        Bioluminescent forest night
                    </button>
                    <button onclick="setPromptSuggestion('An astronaut floating in space surrounded by colorful nebulas in oil painting style')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-primary text-sm block mb-1">rocket_launch</span>
                        Astronaut in nebula space
                    </button>
                    <button onclick="setPromptSuggestion('A cozy cafe interior with warm lighting, steam rising from coffee cups')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-secondary text-sm block mb-1">local_cafe</span>
                        Cozy cafe with warm light
                    </button>
                </div>
            </div>
        </div>

        {{-- Input Zone --}}
        <div id="inputZone">
            {{-- Image preview --}}
            <div id="imagePreviewContainer" class="mb-2 hidden">
                <div id="imagePreviewZone" class="relative inline-block">
                    <img id="imagePreviewEl" src="" alt="Reference" class="max-h-24 rounded-xl">
                    <button onclick="removeImage()"
                        class="absolute -top-2 -right-2 size-5 bg-slate-700 hover:bg-red-500 border border-white/10 text-white rounded-full flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                    <div class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/60 text-[9px] text-slate-300 font-medium">
                        Reference image
                    </div>
                </div>
            </div>

            {{-- Textarea row --}}
            <div class="flex gap-3 items-end">
                {{-- Image upload button --}}
                <button onclick="document.getElementById('imageFileInput').click()"
                    title="Upload reference image"
                    class="flex-shrink-0 size-11 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-slate-400 hover:text-primary flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-xl">add_photo_alternate</span>
                </button>
                <input type="file" id="imageFileInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden" onchange="handleImageSelect(this)">

                {{-- Textarea --}}
                <div class="flex-1 relative">
                    <textarea id="promptInput"
                        placeholder="Describe what you want to create…"
                        rows="1"
                        class="w-full px-4 py-3 pr-12 text-sm"
                        onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"></textarea>
                </div>

                {{-- Send button --}}
                <button id="sendBtn" onclick="sendMessage()"
                    class="flex-shrink-0 size-11 rounded-xl bg-primary hover:bg-primary/85 text-white flex items-center justify-center transition-all shadow-lg shadow-primary/20 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-xl" id="sendIcon">arrow_upward</span>
                </button>
            </div>

            <p class="text-[10px] text-slate-600 mt-2 text-center">
                Each generation uses <span class="text-primary font-medium">5 credits</span> · Upload a reference image to guide the AI
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- FLOW PANEL                                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div id="flowPanel">
        <div class="flex items-center justify-between px-4 py-4 flex-shrink-0 border-b border-white/5">
            <div>
                <h2 class="text-sm font-semibold text-white">Image Flow</h2>
                <p class="text-[10px] text-slate-500 mt-0.5">Generation chain</p>
            </div>
            <button onclick="toggleFlowPanel()" class="size-7 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>

        <div id="flowTimeline" class="flex-1 overflow-y-auto p-3 space-y-0">
            <div id="flowEmptyMsg" class="text-center py-10">
                <span class="material-symbols-outlined text-3xl text-slate-700 block mb-2">account_tree</span>
                <p class="text-xs text-slate-600">Generate images to see the flow</p>
            </div>
        </div>
    </div>

</div>

{{-- ── Modal: Full-screen image view ──────────────────────────────── --}}
<div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-6"
    onclick="closeImageModal()">
    <div class="relative max-w-3xl max-h-full" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain">
        <div class="absolute top-3 right-3 flex gap-2">
            <a id="modalDownload" href="#" download="generated-image.jpg"
                class="size-9 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-base">download</span>
            </a>
            <button onclick="closeImageModal()"
                class="size-9 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <div id="modalCaption" class="mt-3 text-center text-sm text-slate-300 max-w-lg mx-auto"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── State ─────────────────────────────────────────────────────────────────────
const state = {
    sessionId:      null,
    conversationId: null,
    messages:       [],      // [{role,prompt,imageUrl,textResponse,thumb,ts}]
    isGenerating:   false,
    imageFile:      null,
    allSessions:    [],
};

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    newSession();
    loadSessions();
    setupDragDrop();
});

// ── Session helpers ────────────────────────────────────────────────────────────
function newSession() {
    state.sessionId      = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2,8);
    state.conversationId = null;
    state.messages       = [];
    state.imageFile      = null;
    renderMessages();
    renderFlow();
    updateChatTitle('New Playground', 'Start a conversation to generate images');
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('promptInput').value = '';
    document.querySelectorAll('.session-item').forEach(el => el.classList.remove('active'));
}

async function loadSessions() {
    try {
        const res  = await fetch('{{ route("playground.api.sessions") }}', {
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }
        });
        const data = await res.json();
        state.allSessions = (data.success && data.sessions) ? data.sessions : [];
        renderSessionList(state.allSessions);
    } catch {
        document.getElementById('sessionList').innerHTML =
            '<p class="text-xs text-slate-600 text-center py-6">Could not load sessions</p>';
    }
}

async function persistSession() {
    if (!state.messages.length) return;
    try {
        await fetch('{{ route("playground.api.session.save") }}', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
            body:    JSON.stringify({
                session_id:      state.sessionId,
                conversation_id: state.conversationId,
                messages:        state.messages,
                results:         state.messages.filter(m => m.imageUrl).map(m => ({ imageUrl: m.imageUrl, prompt: m.prompt })),
            }),
        });
        loadSessions(); // Refresh list
    } catch (e) { console.error('Session save failed', e); }
}

async function loadSession(sessionId) {
    try {
        const res  = await fetch(`/playground/api/session/${sessionId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }
        });
        const data = await res.json();
        if (!data.success) return;
        const s = data.session;
        state.sessionId      = s.session_id;
        state.conversationId = s.conversation_id;
        state.messages       = s.messages || [];
        renderMessages();
        renderFlow();
        const lastPrompt = state.messages.filter(m => m.role === 'user').slice(-1)[0]?.prompt || 'Session';
        updateChatTitle(truncate(lastPrompt, 32), `${state.messages.length} messages`);
        highlightSession(sessionId);
    } catch (e) { console.error('Load session failed', e); }
}

async function deleteSession(sessionId, ev) {
    ev.stopPropagation();
    if (!confirm('Delete this session?')) return;
    try {
        await fetch(`/playground/api/session/${sessionId}`, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }
        });
        if (state.sessionId === sessionId) newSession();
        loadSessions();
    } catch (e) { console.error('Delete failed', e); }
}

// ── Rendering ─────────────────────────────────────────────────────────────────
function renderSessionList(sessions) {
    const el = document.getElementById('sessionList');
    if (!sessions.length) {
        el.innerHTML = `
            <div class="text-center py-10">
                <span class="material-symbols-outlined text-3xl text-slate-700 block mb-2">chat_bubble_outline</span>
                <p class="text-xs text-slate-600">No past sessions yet</p>
            </div>`;
        return;
    }

    el.innerHTML = sessions.map(s => {
        const msgs     = Array.isArray(s.messages) ? s.messages : [];
        const preview  = msgs.find(m => m.role === 'user')?.prompt || 'Empty session';
        const imgCount = msgs.filter(m => m.imageUrl).length;
        const date     = formatDate(s.last_updated_at || s.updated_at);
        const thumb    = msgs.find(m => m.imageUrl)?.imageUrl || null;
        const active   = state.sessionId === s.session_id ? 'active' : '';

        return `
        <div class="session-item ${active}" onclick="loadSession('${esc(s.session_id)}')">
            <div class="flex items-start gap-2.5">
                ${thumb
                    ? `<img src="${esc(thumb)}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">`
                    : `<div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-primary text-base">smart_toy</span>
                       </div>`}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-200 truncate">${esc(truncate(preview, 36))}</p>
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="text-[10px] text-slate-500">${date}</span>
                        <span class="text-[10px] text-slate-600">${imgCount} img${imgCount !== 1 ? 's' : ''}</span>
                    </div>
                </div>
                <button onclick="deleteSession('${esc(s.session_id)}', event)"
                    class="size-5 rounded text-slate-600 hover:text-red-400 flex items-center justify-center flex-shrink-0 transition-colors">
                    <span class="material-symbols-outlined text-xs">delete</span>
                </button>
            </div>
        </div>`;
    }).join('');
}

function filterSessions(q) {
    const filtered = q.trim()
        ? state.allSessions.filter(s => {
            const msgs = Array.isArray(s.messages) ? s.messages : [];
            const text = msgs.map(m => m.prompt || '').join(' ').toLowerCase();
            return text.includes(q.toLowerCase());
          })
        : state.allSessions;
    renderSessionList(filtered);
}

function renderMessages() {
    const container = document.getElementById('messagesContainer');
    const empty     = document.getElementById('emptyState');

    if (!state.messages.length) {
        container.innerHTML = '';
        container.appendChild(empty);
        empty.style.display = 'flex';
        empty.style.flexDirection = 'column';
        return;
    }

    empty.style.display = 'none';
    // Remove empty state from container if present
    if (container.contains(empty)) container.removeChild(empty);

    // Rebuild from scratch
    container.innerHTML = state.messages.map((msg, i) => buildMessageHTML(msg, i)).join('');
    scrollToBottom();
}

function buildMessageHTML(msg, idx) {
    const ts = msg.ts ? formatTime(msg.ts) : '';

    if (msg.role === 'user') {
        const imgHTML = msg.thumb
            ? `<img src="${esc(msg.thumb)}" class="w-16 h-16 rounded-lg object-cover border border-white/10" alt="Reference">`
            : '';
        return `
        <div class="msg-user flex flex-col items-end gap-1">
            <div class="flex items-end gap-2 flex-row-reverse">
                <div class="size-7 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white text-xs">person</span>
                </div>
                <div class="bubble px-4 py-3">
                    ${imgHTML ? `<div class="mb-2">${imgHTML}</div>` : ''}
                    <p class="text-sm text-slate-200">${esc(msg.prompt)}</p>
                </div>
            </div>
            <span class="text-[10px] text-slate-600 mr-9">${ts}</span>
        </div>`;
    }

    // AI message
    const imageHTML = msg.imageUrl ? `
        <div class="gen-image-wrap mb-2">
            <img src="${esc(msg.imageUrl)}" alt="Generated" onclick="openImageModal('${esc(msg.imageUrl)}','${esc(msg.prompt||'')}')"
                 class="cursor-pointer">
            <div class="gen-image-actions">
                <button onclick="openImageModal('${esc(msg.imageUrl)}','${esc(msg.prompt||'')}')"
                    class="size-7 rounded-lg bg-black/70 text-white flex items-center justify-center hover:bg-black/90 transition-colors">
                    <span class="material-symbols-outlined text-sm">open_in_full</span>
                </button>
                <a href="${esc(msg.imageUrl)}" download="generated.jpg"
                    class="size-7 rounded-lg bg-black/70 text-white flex items-center justify-center hover:bg-black/90 transition-colors">
                    <span class="material-symbols-outlined text-sm">download</span>
                </a>
                <button onclick="useAsReference('${esc(msg.imageUrl)}')"
                    class="size-7 rounded-lg bg-primary/80 text-white flex items-center justify-center hover:bg-primary transition-colors" title="Use as reference">
                    <span class="material-symbols-outlined text-sm">recycling</span>
                </button>
            </div>
        </div>` : '';

    const textHTML = msg.textResponse
        ? `<p class="text-sm text-slate-300 leading-relaxed">${esc(msg.textResponse)}</p>`
        : '';

    return `
    <div class="msg-ai flex flex-col items-start gap-1">
        <div class="flex items-end gap-2">
            <div class="size-7 rounded-full bg-gradient-to-br from-primary/30 to-secondary/30 border border-white/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-xs">smart_toy</span>
            </div>
            <div class="bubble px-4 py-3 min-w-0">
                ${imageHTML}
                ${textHTML}
            </div>
        </div>
        <span class="text-[10px] text-slate-600 ml-9">${ts}</span>
    </div>`;
}

function appendMessage(msg) {
    const container = document.getElementById('messagesContainer');
    const empty     = document.getElementById('emptyState');
    if (container.contains(empty)) container.removeChild(empty);

    const div = document.createElement('div');
    div.innerHTML = buildMessageHTML(msg, state.messages.length - 1);
    container.appendChild(div.firstElementChild);
    scrollToBottom();
}

function renderFlow() {
    const timeline = document.getElementById('flowTimeline');
    const emptyMsg = document.getElementById('flowEmptyMsg');
    const images   = state.messages.filter(m => m.imageUrl);

    if (!images.length) {
        timeline.innerHTML = '';
        timeline.appendChild(emptyMsg);
        return;
    }

    if (timeline.contains(emptyMsg)) timeline.removeChild(emptyMsg);

    timeline.innerHTML = images.map((msg, i) => `
        <div>
            ${i > 0 ? '<div class="flow-connector"></div>' : ''}
            <div class="flow-node" onclick="openImageModal('${esc(msg.imageUrl)}','${esc(msg.prompt||'')}')">
                <img src="${esc(msg.imageUrl)}" alt="Step ${i+1}" class="w-full aspect-square object-cover rounded-lg mb-1.5">
                <div class="flex items-center gap-1 mb-0.5">
                    <span class="text-[10px] font-bold text-primary">Step ${i+1}</span>
                    <span class="text-[10px] text-slate-600">· ${formatTime(msg.ts)}</span>
                </div>
                <p class="text-[10px] text-slate-400 line-clamp-2">${esc(truncate(msg.prompt||'',60))}</p>
            </div>
        </div>
    `).join('');
}

// ── Sending ────────────────────────────────────────────────────────────────────
async function sendMessage() {
    if (state.isGenerating) return;

    const promptEl = document.getElementById('promptInput');
    const prompt   = promptEl.value.trim();
    if (!prompt) return;

    state.isGenerating = true;
    setGeneratingUI(true);

    const ts = Date.now();

    // Push user message
    const userMsg = {
        role:   'user',
        prompt,
        thumb:  state.imageFile ? URL.createObjectURL(state.imageFile) : null,
        ts,
    };
    state.messages.push(userMsg);
    appendMessage(userMsg);
    promptEl.value = '';
    autoResize(promptEl);

    // Show typing indicator
    showTyping();

    // Build FormData
    const fd = new FormData();
    fd.append('prompt', prompt);
    fd.append('_token', CSRF);
    if (state.conversationId) fd.append('conversation_id', state.conversationId);
    if (state.imageFile)      fd.append('canvas_image', state.imageFile);

    try {
        const res  = await fetch('{{ route("playground.api.chat") }}', { method: 'POST', body: fd });
        const data = await res.json();

        hideTyping();

        if (data.success) {
            state.conversationId = data.conversation_id || state.conversationId;

            const aiMsg = {
                role:         'ai',
                imageUrl:     data.image_url   || null,
                textResponse: data.text_response || null,
                prompt,
                ts:           Date.now(),
            };
            state.messages.push(aiMsg);
            appendMessage(aiMsg);
            renderFlow();

            // Update session title
            updateChatTitle(truncate(prompt, 32), `${Math.ceil(state.messages.length/2)} generation(s)`);

            // Persist
            persistSession();
        } else {
            showError(data.error || data.message || 'Generation failed. Please try again.');
        }
    } catch (e) {
        hideTyping();
        showError('Network error — could not reach the server.');
        console.error(e);
    }

    // Reset image
    state.imageFile = null;
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('imagePreviewEl').src = '';

    state.isGenerating = false;
    setGeneratingUI(false);
}

// ── Typing indicator ────────────────────────────────────────────────────────────
let typingEl = null;
function showTyping() {
    hideTyping();
    const container = document.getElementById('messagesContainer');
    typingEl = document.createElement('div');
    typingEl.id = 'typingIndicator';
    typingEl.className = 'msg-ai flex items-end gap-2';
    typingEl.innerHTML = `
        <div class="size-7 rounded-full bg-gradient-to-br from-primary/30 to-secondary/30 border border-white/10 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary text-xs">smart_toy</span>
        </div>
        <div class="bubble px-4 py-3 flex gap-1.5 items-center">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>`;
    container.appendChild(typingEl);
    scrollToBottom();
}
function hideTyping() {
    if (typingEl) { typingEl.remove(); typingEl = null; }
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function setGeneratingUI(generating) {
    const btn  = document.getElementById('sendBtn');
    const icon = document.getElementById('sendIcon');
    btn.disabled = generating;
    icon.textContent = generating ? 'hourglass_top' : 'arrow_upward';
    if (generating) {
        icon.classList.add('animate-spin');
    } else {
        icon.classList.remove('animate-spin');
    }
}

function showError(msg) {
    const container = document.getElementById('messagesContainer');
    const div = document.createElement('div');
    div.className = 'flex justify-center';
    div.innerHTML = `
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs max-w-md">
            <span class="material-symbols-outlined text-sm">error</span>
            ${esc(msg)}
        </div>`;
    container.appendChild(div);
    scrollToBottom();
}

function scrollToBottom() {
    const c = document.getElementById('messagesContainer');
    c.scrollTop = c.scrollHeight;
}

function updateChatTitle(title, sub) {
    document.getElementById('chatTitle').textContent    = title;
    document.getElementById('chatSubtitle').textContent = sub;
}

function highlightSession(sessionId) {
    document.querySelectorAll('.session-item').forEach(el => el.classList.remove('active'));
    // Re-render to apply active class properly
    renderSessionList(state.allSessions);
}

function clearChat() {
    if (state.messages.length && !confirm('Start a new session? Current chat will be saved.')) return;
    persistSession();
    newSession();
}

// ── Toggle panels ─────────────────────────────────────────────────────────────
function toggleSessionSidebar() {
    document.getElementById('sessionSidebar').classList.toggle('collapsed');
}
function toggleFlowPanel() {
    document.getElementById('flowPanel').classList.toggle('collapsed');
}

// ── Image upload ───────────────────────────────────────────────────────────────
function handleImageSelect(input) {
    const file = input.files[0];
    if (!file) return;
    state.imageFile = file;
    const url = URL.createObjectURL(file);
    document.getElementById('imagePreviewEl').src = url;
    document.getElementById('imagePreviewContainer').classList.remove('hidden');
    input.value = '';
}

function removeImage() {
    state.imageFile = null;
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    document.getElementById('imagePreviewEl').src = '';
}

async function useAsReference(imageUrl) {
    try {
        const res   = await fetch(imageUrl);
        const blob  = await res.blob();
        const ext   = blob.type.includes('png') ? 'png' : 'jpg';
        state.imageFile = new File([blob], `reference.${ext}`, { type: blob.type });
        document.getElementById('imagePreviewEl').src = imageUrl;
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
        document.getElementById('promptInput').focus();
    } catch {
        // Fallback: set URL as image URL manually won't work cross-origin, just focus
        document.getElementById('promptInput').focus();
    }
}

// ── Drag & Drop ────────────────────────────────────────────────────────────────
function setupDragDrop() {
    const area = document.getElementById('chatArea');
    area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragging'); });
    area.addEventListener('dragleave', e => { if (!area.contains(e.relatedTarget)) area.classList.remove('dragging'); });
    area.addEventListener('drop', e => {
        e.preventDefault();
        area.classList.remove('dragging');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            state.imageFile = file;
            const url = URL.createObjectURL(file);
            document.getElementById('imagePreviewEl').src = url;
            document.getElementById('imagePreviewContainer').classList.remove('hidden');
        }
    });
}

// ── Modal ──────────────────────────────────────────────────────────────────────
function openImageModal(url, caption) {
    document.getElementById('modalImage').src        = url;
    document.getElementById('modalDownload').href    = url;
    document.getElementById('modalCaption').textContent = caption || '';
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
}
function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
}

// ── Suggestions ────────────────────────────────────────────────────────────────
function setPromptSuggestion(text) {
    const el = document.getElementById('promptInput');
    el.value = text;
    autoResize(el);
    el.focus();
}

// ── Textarea auto-resize ───────────────────────────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
}

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

// ── Utils ─────────────────────────────────────────────────────────────────────
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function truncate(str, len) { return str.length > len ? str.slice(0, len) + '…' : str; }
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    }
    return d.toLocaleDateString([], {month:'short', day:'numeric'});
}
function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(typeof ts === 'number' ? ts : ts);
    return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
}
</script>
@endpush
