@extends('layouts.app')

@section('title', 'Chat Playground — Clever Creator')

@push('styles')
<style>
    /* ── Kill default padding ────────────────────────── */
    #appMain > div.p-10 { padding: 0 !important; }

    /* ── Root layout ─────────────────────────────────── */
    #playgroundWrap {
        display: flex;
        height: calc(100vh - 5rem);
        overflow: hidden;
    }

    /* ── Session sidebar ─────────────────────────────── */
    #sessionSidebar {
        width: 268px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: rgba(22,27,34,0.6);
        backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.05);
        transition: width 0.25s ease;
        overflow: hidden;
    }
    #sessionSidebar.collapsed { width: 0; }

    /* ── Chat area ───────────────────────────────────── */
    #chatArea {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        background: rgba(10,10,12,0.5);
        position: relative;
    }

    /* ── Flow panel ──────────────────────────────────── */
    #flowPanel {
        width: 292px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        background: rgba(22,27,34,0.6);
        backdrop-filter: blur(16px);
        border-left: 1px solid rgba(255,255,255,0.05);
        overflow: hidden;
        transition: width 0.25s ease;
    }
    #flowPanel.collapsed { width: 0; }

    /* ── Messages ────────────────────────────────────── */
    #messagesContainer {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        scroll-behavior: smooth;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    #messagesContainer::-webkit-scrollbar { width: 4px; }
    #messagesContainer::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 99px; }

    .msg-user .bubble {
        background: linear-gradient(135deg,rgba(19,164,236,0.18),rgba(139,92,246,0.12));
        border: 1px solid rgba(19,164,236,0.22);
        border-radius: 1rem 1rem 0.25rem 1rem;
        margin-left: auto;
        max-width: 72%;
    }
    .msg-ai .bubble {
        background: rgba(22,27,34,0.8);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 1rem 1rem 1rem 0.25rem;
        max-width: 72%;
    }

    /* ── Gen image in bubble ─────────────────────────── */
    .gen-img-wrap { position: relative; border-radius: 0.75rem; overflow: hidden; }
    .gen-img-wrap img { width: 100%; display: block; border-radius: 0.75rem; max-height: 380px; object-fit: cover; }
    .gen-img-actions {
        position: absolute; bottom: 0.5rem; right: 0.5rem;
        display: flex; gap: 0.35rem;
        opacity: 0; transition: opacity 0.2s;
    }
    .gen-img-wrap:hover .gen-img-actions { opacity: 1; }

    /* ── Input zone ──────────────────────────────────── */
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
        min-height: 52px;
        max-height: 160px;
        overflow-y: auto;
    }
    #promptInput:focus { border-color: rgba(19,164,236,0.5); }
    #promptInput::placeholder { color: rgba(148,163,184,0.45); }

    /* ── Session items ───────────────────────────────── */
    .session-item {
        padding: 0.7rem 0.875rem;
        border-radius: 0.625rem;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s;
    }
    .session-item:hover { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.06); }
    .session-item.active { background: rgba(19,164,236,0.1); border-color: rgba(19,164,236,0.25); }

    /* ── Flow nodes ──────────────────────────────────── */
    .flow-node {
        background: rgba(22,27,34,0.7);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 0.75rem;
        padding: 0.625rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .flow-node:hover { border-color: rgba(19,164,236,0.35); background: rgba(19,164,236,0.07); }
    .flow-connector { width: 2px; height: 1.5rem; background: linear-gradient(to bottom,rgba(19,164,236,0.5),rgba(139,92,246,0.5)); margin: 0 auto; }

    /* ── Typing dots ─────────────────────────────────── */
    .typing-dot { width: 7px; height: 7px; border-radius: 50%; background: #13a4ec; animation: typingPulse 1.4s infinite; }
    .typing-dot:nth-child(2) { animation-delay: .2s; }
    .typing-dot:nth-child(3) { animation-delay: .4s; }
    @keyframes typingPulse {
        0%,80%,100% { opacity:.2; transform:scale(.85); }
        40%          { opacity:1;  transform:scale(1.1); }
    }

    /* ── Drop zone ───────────────────────────────────── */
    #dropOverlay {
        position: absolute; inset: 0;
        background: rgba(19,164,236,0.08);
        border: 2px dashed rgba(19,164,236,0.5);
        display: none; align-items: center; justify-content: center;
        z-index: 10; pointer-events: none;
    }
    #chatArea.dragging #dropOverlay { display: flex; }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.09); border-radius: 99px; }
</style>
@endpush

@section('content')
<div id="playgroundWrap">

    {{-- ════════════════════ SESSION SIDEBAR ════════════════════ --}}
    <div id="sessionSidebar">
        <div class="flex items-center justify-between px-4 py-4 border-b border-white/5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-white">Conversations</h2>
                <p class="text-[10px] text-slate-500 mt-0.5">Your past sessions</p>
            </div>
            <button onclick="newSession()" class="size-8 rounded-lg bg-primary/15 hover:bg-primary/25 text-primary flex items-center justify-center transition-colors" title="New">
                <span class="material-symbols-outlined text-base">add</span>
            </button>
        </div>
        <div class="px-3 py-2 flex-shrink-0">
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 border border-white/5">
                <span class="material-symbols-outlined text-slate-500 text-base">search</span>
                <input id="sessionSearch" type="text" placeholder="Search…"
                    class="bg-transparent text-xs text-slate-300 placeholder-slate-600 outline-none flex-1 w-full"
                    oninput="filterSessions(this.value)">
            </div>
        </div>
        <div id="sessionList" class="flex-1 overflow-y-auto px-2 py-1 space-y-1">
            <div class="text-center py-8">
                <div class="size-7 border-2 border-primary/30 border-t-primary rounded-full animate-spin mx-auto mb-2"></div>
                <p class="text-xs text-slate-500">Loading…</p>
            </div>
        </div>
        <div class="p-3 flex-shrink-0 border-t border-white/5">
            <p class="text-[10px] text-slate-700 text-center">Sessions auto-save</p>
        </div>
    </div>

    {{-- ════════════════════ CHAT AREA ════════════════════ --}}
    <div id="chatArea">
        <div id="dropOverlay">
            <div class="text-center pointer-events-none">
                <span class="material-symbols-outlined text-4xl text-primary">image</span>
                <p class="text-sm font-medium text-primary mt-1">Drop image here</p>
            </div>
        </div>

        {{-- Topbar --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-white/5 flex-shrink-0 bg-[rgba(10,10,12,0.7)]">
            <div class="flex items-center gap-3">
                <button onclick="toggleSessionSidebar()" class="size-8 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-lg">menu_open</span>
                </button>
                <div>
                    <h1 id="chatTitle" class="text-sm font-semibold text-white">New Playground</h1>
                    <p class="text-[10px] text-slate-500" id="chatSubtitle">Start a conversation to generate images</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('playground.canvas') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-base">draw</span>
                    <span>Canvas mode</span>
                </a>
                <button onclick="clearChat()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-base">restart_alt</span>
                    <span>New chat</span>
                </button>
                <button onclick="toggleFlowPanel()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 text-xs transition-colors">
                    <span class="material-symbols-outlined text-base">account_tree</span>
                    <span>Flow</span>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="messagesContainer">
            <div id="emptyState" class="flex flex-col items-center justify-center h-full text-center py-16">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary/20 to-secondary/20 border border-white/10 flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-3xl text-primary">smart_toy</span>
                </div>
                <h2 class="text-xl font-semibold text-white mb-2">AI Image Playground</h2>
                <p class="text-sm text-slate-400 max-w-sm mb-6">Describe what you want or upload a reference image and let the AI transform it.</p>
                <div class="grid grid-cols-2 gap-2 max-w-sm w-full">
                    <button onclick="setSuggestion('A futuristic city at sunset with neon lights reflecting in rain puddles')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-primary text-sm block mb-1">city</span>Futuristic city at sunset
                    </button>
                    <button onclick="setSuggestion('A dreamy forest with bioluminescent mushrooms and fireflies at night')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-secondary text-sm block mb-1">forest</span>Bioluminescent forest night
                    </button>
                    <button onclick="setSuggestion('An astronaut floating in space surrounded by colorful nebulas, oil painting style')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-primary text-sm block mb-1">rocket_launch</span>Astronaut in nebula space
                    </button>
                    <button onclick="setSuggestion('A cozy cafe interior with warm lighting, steam rising from coffee cups')"
                        class="px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-xs text-slate-300 text-left transition-colors">
                        <span class="material-symbols-outlined text-secondary text-sm block mb-1">local_cafe</span>Cozy cafe with warm light
                    </button>
                </div>
            </div>
        </div>

        {{-- Input Zone --}}
        <div id="inputZone">
            <div id="imgPreviewContainer" class="mb-2 hidden">
                <div class="relative inline-block">
                    <img id="imgPreviewEl" src="" alt="Ref" class="max-h-20 rounded-xl border border-primary/30">
                    <button onclick="removeImage()"
                        class="absolute -top-1.5 -right-1.5 size-5 bg-slate-700 hover:bg-red-500 border border-white/10 text-white rounded-full flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                    <div class="absolute bottom-1 left-1.5 px-1.5 py-0.5 rounded bg-black/60 text-[9px] text-slate-300">Reference</div>
                </div>
            </div>
            <div class="flex gap-3 items-end">
                <button onclick="document.getElementById('imgFileInput').click()" title="Upload reference"
                    class="flex-shrink-0 size-11 rounded-xl bg-white/5 hover:bg-white/8 border border-white/8 text-slate-400 hover:text-primary flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-xl">add_photo_alternate</span>
                </button>
                <input type="file" id="imgFileInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden" onchange="handleImg(this)">
                <div class="flex-1 relative">
                    <textarea id="promptInput" placeholder="Describe what you want to create…" rows="1"
                        class="w-full px-4 py-3 pr-12 text-sm"
                        onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                </div>
                <button id="sendBtn" onclick="send()"
                    class="flex-shrink-0 size-11 rounded-xl bg-primary hover:bg-primary/85 text-white flex items-center justify-center transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-xl" id="sendIcon">arrow_upward</span>
                </button>
            </div>
            <p class="text-[10px] text-slate-600 mt-2 text-center">5 credits per generation · Shift+Enter for new line</p>
        </div>
    </div>

    {{-- ════════════════════ FLOW PANEL ════════════════════ --}}
    <div id="flowPanel">
        <div class="flex items-center justify-between px-4 py-4 border-b border-white/5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-white">Image Flow</h2>
                <p class="text-[10px] text-slate-500 mt-0.5">Generation chain</p>
            </div>
            <button onclick="toggleFlowPanel()" class="size-7 rounded-lg text-slate-500 hover:text-white hover:bg-white/5 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div id="flowTimeline" class="flex-1 overflow-y-auto p-3 space-y-0">
            <div id="flowEmpty" class="text-center py-10">
                <span class="material-symbols-outlined text-3xl text-slate-700 block mb-2">account_tree</span>
                <p class="text-xs text-slate-600">Generate images to see the flow</p>
            </div>
        </div>
    </div>
</div>

{{-- Image fullscreen modal --}}
<div id="imgModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-6" onclick="closeModal()">
    <div class="relative max-w-3xl max-h-full" onclick="event.stopPropagation()">
        <img id="modalImg" src="" alt="" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain">
        <div class="absolute top-3 right-3 flex gap-2">
            <a id="modalDl" href="#" download class="size-9 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-base">download</span>
            </a>
            <button onclick="closeModal()" class="size-9 rounded-xl bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <p id="modalCaption" class="mt-3 text-center text-sm text-slate-300 max-w-lg mx-auto"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ST = {
    sessionId: null, conversationId: null,
    messages: [], isGenerating: false, imageFile: null, allSessions: [],
};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.addEventListener('DOMContentLoaded', () => { newSession(); loadSessions(); setupDrag(); });

// ── Sessions ──────────────────────────────────────────────────────────────────
function newSession() {
    ST.sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2,8);
    ST.conversationId = null; ST.messages = []; ST.imageFile = null;
    renderMessages(); renderFlow();
    setTitle('New Playground', 'Start a conversation to generate images');
    document.getElementById('imgPreviewContainer').classList.add('hidden');
    document.getElementById('promptInput').value = '';
    document.querySelectorAll('.session-item').forEach(e => e.classList.remove('active'));
}

async function loadSessions() {
    try {
        const r = await fetch('{{ route("playground.api.sessions") }}', { headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' } });
        const d = await r.json();
        ST.allSessions = d.success ? (d.sessions||[]) : [];
        renderSessionList(ST.allSessions);
    } catch { document.getElementById('sessionList').innerHTML = '<p class="text-xs text-slate-600 text-center py-6">Could not load</p>'; }
}

async function loadSession(id) {
    try {
        const r = await fetch(`/playground/api/session/${id}`, { headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' } });
        const d = await r.json();
        if (!d.success) return;
        ST.sessionId = d.session.session_id; ST.conversationId = d.session.conversation_id; ST.messages = d.session.messages || [];
        renderMessages(); renderFlow();
        const p = ST.messages.find(m=>m.role==='user')?.prompt || 'Session';
        setTitle(trunc(p,32), `${ST.messages.length} messages`);
        renderSessionList(ST.allSessions);
    } catch (e) { console.error(e); }
}

async function deleteSession(id, ev) {
    ev.stopPropagation();
    if (!confirm('Delete this session?')) return;
    await fetch(`/playground/api/session/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
    if (ST.sessionId === id) newSession();
    loadSessions();
}

async function persist() {
    if (!ST.messages.length) return;
    await fetch('{{ route("playground.api.session.save") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ session_id: ST.sessionId, conversation_id: ST.conversationId, messages: ST.messages, results: ST.messages.filter(m=>m.imageUrl).map(m=>({imageUrl:m.imageUrl,prompt:m.prompt})) }),
    });
    loadSessions();
}

// ── Rendering ─────────────────────────────────────────────────────────────────
function renderSessionList(sessions) {
    const el = document.getElementById('sessionList');
    if (!sessions.length) { el.innerHTML = '<div class="text-center py-10"><span class="material-symbols-outlined text-3xl text-slate-700 block mb-2">chat_bubble_outline</span><p class="text-xs text-slate-600">No past sessions</p></div>'; return; }
    el.innerHTML = sessions.map(s => {
        const msgs = Array.isArray(s.messages) ? s.messages : [];
        const preview = msgs.find(m=>m.role==='user')?.prompt || 'Empty';
        const imgCount = msgs.filter(m=>m.imageUrl).length;
        const date = fmtDate(s.last_updated_at || s.updated_at);
        const thumb = msgs.find(m=>m.imageUrl)?.imageUrl || null;
        const active = ST.sessionId === s.session_id ? 'active' : '';
        return `<div class="session-item ${active}" onclick="loadSession('${esc(s.session_id)}')">
            <div class="flex items-start gap-2.5">
                ${thumb ? `<img src="${esc(thumb)}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">` : `<div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-primary text-base">smart_toy</span></div>`}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-200 truncate">${esc(trunc(preview,36))}</p>
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="text-[10px] text-slate-500">${date}</span>
                        <span class="text-[10px] text-slate-600">${imgCount} img${imgCount!==1?'s':''}</span>
                    </div>
                </div>
                <button onclick="deleteSession('${esc(s.session_id)}',event)" class="size-5 rounded text-slate-600 hover:text-red-400 flex items-center justify-center flex-shrink-0 transition-colors"><span class="material-symbols-outlined text-xs">delete</span></button>
            </div>
        </div>`;
    }).join('');
}

function filterSessions(q) {
    const f = q.trim() ? ST.allSessions.filter(s => (Array.isArray(s.messages)?s.messages:[]).map(m=>m.prompt||'').join(' ').toLowerCase().includes(q.toLowerCase())) : ST.allSessions;
    renderSessionList(f);
}

function renderMessages() {
    const c = document.getElementById('messagesContainer');
    const e = document.getElementById('emptyState');
    if (!ST.messages.length) { c.innerHTML=''; c.appendChild(e); e.style.display='flex'; e.style.flexDirection='column'; return; }
    e.style.display='none'; if (c.contains(e)) c.removeChild(e);
    c.innerHTML = ST.messages.map((m,i)=>buildMsgHTML(m,i)).join('');
    c.scrollTop = c.scrollHeight;
}

function buildMsgHTML(msg, idx) {
    const ts = msg.ts ? fmtTime(msg.ts) : '';
    if (msg.role === 'user') {
        const ih = msg.thumb ? `<img src="${esc(msg.thumb)}" class="w-14 h-14 rounded-lg object-cover border border-white/10 mb-2">` : '';
        return `<div class="msg-user flex flex-col items-end gap-1">
            <div class="flex items-end gap-2 flex-row-reverse">
                <div class="size-7 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-white text-xs">person</span></div>
                <div class="bubble px-4 py-3">${ih}<p class="text-sm text-slate-200">${esc(msg.prompt)}</p></div>
            </div>
            <span class="text-[10px] text-slate-600 mr-9">${ts}</span>
        </div>`;
    }
    const imgH = msg.imageUrl ? `<div class="gen-img-wrap mb-2">
        <img src="${esc(msg.imageUrl)}" alt="Generated" onclick="openModal('${esc(msg.imageUrl)}','${esc(msg.prompt||'')}')">
        <div class="gen-img-actions">
            <button onclick="openModal('${esc(msg.imageUrl)}','${esc(msg.prompt||'')}')" class="size-7 rounded-lg bg-black/70 text-white flex items-center justify-center hover:bg-black/90 transition-colors"><span class="material-symbols-outlined text-sm">open_in_full</span></button>
            <a href="${esc(msg.imageUrl)}" download class="size-7 rounded-lg bg-black/70 text-white flex items-center justify-center hover:bg-black/90 transition-colors"><span class="material-symbols-outlined text-sm">download</span></a>
            <button onclick="useAsRef('${esc(msg.imageUrl)}')" class="size-7 rounded-lg bg-primary/80 text-white flex items-center justify-center hover:bg-primary transition-colors" title="Use as reference"><span class="material-symbols-outlined text-sm">recycling</span></button>
        </div>
    </div>` : '';
    const txtH = msg.textResponse ? `<p class="text-sm text-slate-300 leading-relaxed">${esc(msg.textResponse)}</p>` : '';
    return `<div class="msg-ai flex flex-col items-start gap-1">
        <div class="flex items-end gap-2">
            <div class="size-7 rounded-full bg-gradient-to-br from-primary/30 to-secondary/30 border border-white/10 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-primary text-xs">smart_toy</span></div>
            <div class="bubble px-4 py-3 min-w-0">${imgH}${txtH}</div>
        </div>
        <span class="text-[10px] text-slate-600 ml-9">${ts}</span>
    </div>`;
}

function appendMsg(msg) {
    const c = document.getElementById('messagesContainer'), e = document.getElementById('emptyState');
    if (c.contains(e)) c.removeChild(e);
    const d = document.createElement('div'); d.innerHTML = buildMsgHTML(msg, ST.messages.length-1);
    c.appendChild(d.firstElementChild); c.scrollTop = c.scrollHeight;
}

function renderFlow() {
    const tl = document.getElementById('flowTimeline'), em = document.getElementById('flowEmpty');
    const imgs = ST.messages.filter(m=>m.imageUrl);
    if (!imgs.length) { tl.innerHTML=''; tl.appendChild(em); return; }
    if (tl.contains(em)) tl.removeChild(em);
    tl.innerHTML = imgs.map((m,i) => `<div>${i>0?'<div class="flow-connector"></div>':''}<div class="flow-node" onclick="openModal('${esc(m.imageUrl)}','${esc(m.prompt||'')}')">
        <img src="${esc(m.imageUrl)}" class="w-full aspect-square object-cover rounded-lg mb-1.5">
        <div class="flex items-center gap-1 mb-0.5"><span class="text-[10px] font-bold text-primary">Step ${i+1}</span><span class="text-[10px] text-slate-600">· ${fmtTime(m.ts)}</span></div>
        <p class="text-[10px] text-slate-400 line-clamp-2">${esc(trunc(m.prompt||'',60))}</p>
    </div></div>`).join('');
}

// ── Send ──────────────────────────────────────────────────────────────────────
async function send() {
    if (ST.isGenerating) return;
    const el = document.getElementById('promptInput'), prompt = el.value.trim();
    if (!prompt) return;
    ST.isGenerating = true; setSending(true);
    const userMsg = { role:'user', prompt, thumb: ST.imageFile ? URL.createObjectURL(ST.imageFile) : null, ts: Date.now() };
    ST.messages.push(userMsg); appendMsg(userMsg);
    el.value=''; autoResize(el); showTyping();
    const fd = new FormData();
    fd.append('prompt', prompt); fd.append('_token', CSRF);
    if (ST.conversationId) fd.append('conversation_id', ST.conversationId);
    if (ST.imageFile) fd.append('canvas_image', ST.imageFile);
    try {
        const r = await fetch('{{ route("playground.api.chat") }}', { method:'POST', body:fd });
        const d = await r.json();
        hideTyping();
        if (d.success) {
            ST.conversationId = d.conversation_id || ST.conversationId;
            const aiMsg = { role:'ai', imageUrl:d.image_url||null, textResponse:d.text_response||null, prompt, ts:Date.now() };
            ST.messages.push(aiMsg); appendMsg(aiMsg); renderFlow();
            setTitle(trunc(prompt,32), `${Math.ceil(ST.messages.length/2)} generation(s)`);
            persist();
        } else { appendError(d.error||d.message||'Generation failed.'); }
    } catch (e) { hideTyping(); appendError('Network error.'); }
    ST.imageFile=null; document.getElementById('imgPreviewContainer').classList.add('hidden');
    ST.isGenerating=false; setSending(false);
}

let typEl=null;
function showTyping() {
    hideTyping();
    const c=document.getElementById('messagesContainer'); typEl=document.createElement('div');
    typEl.className='msg-ai flex items-end gap-2';
    typEl.innerHTML=`<div class="size-7 rounded-full bg-gradient-to-br from-primary/30 to-secondary/30 border border-white/10 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-primary text-xs">smart_toy</span></div>
        <div class="bubble px-4 py-3 flex gap-1.5 items-center"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>`;
    c.appendChild(typEl); c.scrollTop=c.scrollHeight;
}
function hideTyping() { if(typEl){typEl.remove();typEl=null;} }

function appendError(msg) {
    const c=document.getElementById('messagesContainer'); const d=document.createElement('div');
    d.className='flex justify-center';
    d.innerHTML=`<div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs max-w-md"><span class="material-symbols-outlined text-sm">error</span>${esc(msg)}</div>`;
    c.appendChild(d); c.scrollTop=c.scrollHeight;
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function setSending(gen) {
    document.getElementById('sendBtn').disabled=gen;
    document.getElementById('sendIcon').textContent=gen?'hourglass_top':'arrow_upward';
    gen ? document.getElementById('sendIcon').classList.add('animate-spin') : document.getElementById('sendIcon').classList.remove('animate-spin');
}
function setTitle(t,s) { document.getElementById('chatTitle').textContent=t; document.getElementById('chatSubtitle').textContent=s; }
function toggleSessionSidebar() { document.getElementById('sessionSidebar').classList.toggle('collapsed'); }
function toggleFlowPanel() { document.getElementById('flowPanel').classList.toggle('collapsed'); }
function clearChat() { if(ST.messages.length&&!confirm('Start new session?'))return; persist(); newSession(); }
function setSuggestion(t) { const el=document.getElementById('promptInput'); el.value=t; autoResize(el); el.focus(); }

function handleImg(input) {
    const file=input.files[0]; if(!file)return; ST.imageFile=file;
    document.getElementById('imgPreviewEl').src=URL.createObjectURL(file);
    document.getElementById('imgPreviewContainer').classList.remove('hidden');
    input.value='';
}
function removeImage() { ST.imageFile=null; document.getElementById('imgPreviewContainer').classList.add('hidden'); document.getElementById('imgPreviewEl').src=''; }
async function useAsRef(url) {
    try { const r=await fetch(url); const b=await r.blob(); const ext=b.type.includes('png')?'png':'jpg'; ST.imageFile=new File([b],`ref.${ext}`,{type:b.type}); document.getElementById('imgPreviewEl').src=url; document.getElementById('imgPreviewContainer').classList.remove('hidden'); } catch {}
    document.getElementById('promptInput').focus();
}

function setupDrag() {
    const a=document.getElementById('chatArea');
    a.addEventListener('dragover',e=>{e.preventDefault();a.classList.add('dragging');});
    a.addEventListener('dragleave',e=>{if(!a.contains(e.relatedTarget))a.classList.remove('dragging');});
    a.addEventListener('drop',e=>{e.preventDefault();a.classList.remove('dragging');const f=e.dataTransfer.files[0];if(f&&f.type.startsWith('image/')){ST.imageFile=f;document.getElementById('imgPreviewEl').src=URL.createObjectURL(f);document.getElementById('imgPreviewContainer').classList.remove('hidden');}});
}

function openModal(url,cap) { document.getElementById('modalImg').src=url; document.getElementById('modalDl').href=url; document.getElementById('modalCaption').textContent=cap||''; document.getElementById('imgModal').classList.remove('hidden'); document.getElementById('imgModal').classList.add('flex'); }
function closeModal() { document.getElementById('imgModal').classList.add('hidden'); document.getElementById('imgModal').classList.remove('flex'); }

function autoResize(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,160)+'px'; }
function handleKey(e) { if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();send();} }

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function trunc(s,n){return s.length>n?s.slice(0,n)+'…':s;}
function fmtDate(d){if(!d)return'';const dt=new Date(d),now=new Date();if(dt.toDateString()===now.toDateString())return dt.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});return dt.toLocaleDateString([],{month:'short',day:'numeric'});}
function fmtTime(ts){if(!ts)return'';return new Date(typeof ts==='number'?ts:ts).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
</script>
@endpush
