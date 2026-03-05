@extends('layouts.app')

@section('title', 'Image Stats — Clever Creator')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<style>
    /* Prevent post-load chart/table content from widening cards on small screens */
    .stats-panel {
        min-width: 0;
    }
    .stats-root {
        width: 100%;
        max-width: 100%;
        overflow-x: clip;
    }
    .stats-chart-wrap {
        overflow: hidden;
    }
    @media (max-width: 1023px) {
        /* If desktop sidebar margin-left was left inline, force reset on mobile/tablet */
        #appMain {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
    }
</style>
@endpush

@section('content')

<div class="stats-root space-y-6 sm:space-y-8 overflow-x-hidden">

    {{-- Page header --}}
    <div class="flex flex-wrap items-start sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-white">Image Statistics</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Your image generation activity, model usage, and credit history.</p>
        </div>
        <button id="refreshStatsBtn"
            data-tooltip="Refresh stats" data-tooltip-pos="left"
            class="size-10 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-colors flex items-center justify-center">
            <span id="refreshIcon" class="material-symbols-outlined text-lg">refresh</span>
        </button>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass rounded-2xl border border-white/10 p-4 sm:p-5 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Total Images</p>
            <p id="statTotalImages" class="text-xl sm:text-2xl font-bold text-white">—</p>
        </div>
        <div class="glass rounded-2xl border border-white/10 p-4 sm:p-5 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Credits Used</p>
            <p id="statCreditsUsed" class="text-xl sm:text-2xl font-bold text-primary">—</p>
        </div>
        <div class="glass rounded-2xl border border-white/10 p-4 sm:p-5 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Credits Left</p>
            <p id="statCreditsLeft" class="text-xl sm:text-2xl font-bold text-white">—</p>
        </div>
        <div class="glass rounded-2xl border border-white/10 p-4 sm:p-5 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Models Used</p>
            <p id="statModelsUsed" class="text-xl sm:text-2xl font-bold text-secondary">—</p>
        </div>
    </div>

    {{-- ── Charts row ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Images over time --}}
        <div class="stats-panel glass rounded-2xl border border-white/10 p-4 sm:p-6">
            <h2 class="text-sm font-bold text-white mb-4">Images Generated Over Time</h2>
            <div id="timelineChartWrap" class="stats-chart-wrap relative h-44 sm:h-52 flex items-center justify-center">
                <span id="timelineLoading" class="text-slate-500 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-base" style="animation:spin 1s linear infinite">progress_activity</span>
                    Loading…
                </span>
                <canvas id="timelineChart" class="hidden w-full h-full"></canvas>
            </div>
        </div>

        {{-- Model distribution --}}
        <div class="stats-panel glass rounded-2xl border border-white/10 p-4 sm:p-6">
            <h2 class="text-sm font-bold text-white mb-4">Images by Model</h2>
            <div id="modelChartWrap" class="stats-chart-wrap relative h-44 sm:h-52 flex items-center justify-center">
                <span id="modelLoading" class="text-slate-500 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-base" style="animation:spin 1s linear infinite">progress_activity</span>
                    Loading…
                </span>
                <canvas id="modelChart" class="hidden w-full h-full"></canvas>
            </div>
        </div>

    </div>

    {{-- ── Credit usage bar ── --}}
    <div class="stats-panel glass rounded-2xl border border-white/10 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-white">Credit Usage</h2>
            <span id="creditPct" class="text-xs font-bold text-primary">—%</span>
        </div>
        <div class="w-full bg-white/5 rounded-full h-3 overflow-hidden">
            <div id="creditBar" class="h-full rounded-full bg-gradient-to-r from-primary to-secondary transition-all duration-700" style="width:0%"></div>
        </div>
        <div class="flex justify-between mt-2 text-[11px] text-slate-500">
            <span id="creditBarUsed">0 used</span>
            <span id="creditBarLeft">0 left</span>
        </div>
    </div>

    {{-- ── Recent activity table ── --}}
    <div class="stats-panel glass rounded-2xl border border-white/10 p-4 sm:p-6">
        <h2 class="text-sm font-bold text-white mb-4">Recent Image Activity</h2>
        <div id="recentTableWrap">
            <div id="recentLoading" class="flex items-center justify-center py-8 text-slate-500 text-sm gap-2">
                <span class="material-symbols-outlined text-base" style="animation:spin 1s linear infinite">progress_activity</span>
                Loading…
            </div>
            <div id="recentTableInner" class="hidden overflow-x-auto max-w-full">
                <table class="w-full text-xs sm:text-sm min-w-[520px]">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-white/5">
                            <th class="text-left pb-3 pr-4">Image</th>
                            <th class="text-left pb-3 pr-4">Model</th>
                            <th class="text-right pb-3 pr-4">Downloads</th>
                            <th class="text-right pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody id="recentTableBody" class="divide-y divide-white/5">
                    </tbody>
                </table>
                <p id="recentEmpty" class="hidden text-center text-slate-500 text-sm py-8">No image activity found.</p>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
@php
    $chartPrimary   = '#13a4ec';
    $chartSecondary = '#8b5cf6';
@endphp

document.addEventListener('DOMContentLoaded', function () {
    const isMobile = window.matchMedia('(max-width: 639px)').matches;

    const CHART_COLORS = [
        '#13a4ec','#8b5cf6','#10b981','#f59e0b','#ef4444',
        '#06b6d4','#ec4899','#84cc16','#f97316','#a855f7',
    ];

    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';

    let timelineChart = null;
    let modelChart    = null;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function setCard(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '—';
    }

    function fmt(n) {
        return Number(n ?? 0).toLocaleString();
    }

    function showError(msg) {
        if (window.showApiErrorToast) {
            window.showApiErrorToast({ message: msg });
        } else if (window.appToast) {
            window.appToast(msg, 'error');
        }
    }

    // ── Credit bar ───────────────────────────────────────────────────────────

    function renderCreditBar(used, left) {
        const total = (used || 0) + (left || 0);
        const pct   = total > 0 ? Math.round((used / total) * 100) : 0;
        document.getElementById('creditBar').style.width  = pct + '%';
        document.getElementById('creditPct').textContent  = pct + '%';
        document.getElementById('creditBarUsed').textContent = fmt(used) + ' used';
        document.getElementById('creditBarLeft').textContent = fmt(left) + ' left';
    }

    // ── Timeline chart ───────────────────────────────────────────────────────

    function renderTimelineChart(daily) {
        const wrap = document.getElementById('timelineChartWrap');
        document.getElementById('timelineLoading').remove();
        const canvas = document.getElementById('timelineChart');

        if (!daily || daily.length === 0) {
            wrap.innerHTML = '<p class="text-slate-500 text-sm">No timeline data available.</p>';
            return;
        }

        canvas.classList.remove('hidden');

        if (timelineChart) timelineChart.destroy();

        timelineChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: daily.map(d => d.date),
                datasets: [{
                    label: 'Images',
                    data:  daily.map(d => d.count),
                    borderColor:     '{{ $chartPrimary }}',
                    backgroundColor: 'rgba(19,164,236,0.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 7 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    // ── Model chart ──────────────────────────────────────────────────────────

    function renderModelChart(models) {
        const wrap = document.getElementById('modelChartWrap');
        document.getElementById('modelLoading').remove();
        const canvas = document.getElementById('modelChart');

        if (!models || models.length === 0) {
            wrap.innerHTML = '<p class="text-slate-500 text-sm">No model data available.</p>';
            return;
        }

        canvas.classList.remove('hidden');

        if (modelChart) modelChart.destroy();

        modelChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels:   models.map(m => m.model || m.name),
                datasets: [{
                    data:            models.map(m => m.count),
                    backgroundColor: CHART_COLORS.slice(0, models.length),
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: isMobile ? 'bottom' : 'right',
                        labels: { boxWidth: 10, padding: isMobile ? 10 : 12, font: { size: isMobile ? 10 : 11 } },
                    },
                },
            },
        });
    }

    // ── Recent activity table ────────────────────────────────────────────────

    function renderRecentTable(recent) {
        document.getElementById('recentLoading').remove();
        const inner = document.getElementById('recentTableInner');
        inner.classList.remove('hidden');

        if (!recent || recent.length === 0) {
            document.getElementById('recentEmpty').classList.remove('hidden');
            inner.querySelector('table').classList.add('hidden');
            return;
        }

        const tbody = document.getElementById('recentTableBody');
        tbody.innerHTML = recent.map(row => `
            <tr class="text-slate-300">
                <td class="py-2 pr-4">
                    ${row.image_url
                        ? `<img src="${escHtml(row.image_url)}" alt="Generated image" class="size-12 rounded-lg object-cover bg-white/5"/>`
                        : `<div class="size-12 rounded-lg bg-white/5 flex items-center justify-center text-slate-600"><span class="material-symbols-outlined text-lg">image</span></div>`
                    }
                </td>
                <td class="py-2 pr-4 font-mono text-xs text-white truncate max-w-[200px]">${escHtml(row.model || '—')}</td>
                <td class="py-2 pr-4 text-xs text-right text-slate-300 font-semibold">${fmt(row.downloads ?? 0)}</td>
                <td class="py-2 text-xs text-right text-slate-500 whitespace-nowrap">${escHtml(row.date || '—')}</td>
            </tr>
        `).join('');
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    // ── Load stats ───────────────────────────────────────────────────────────

    async function loadStats() {
        const refreshIcon = document.getElementById('refreshIcon');
        refreshIcon.style.animation = 'spin 1s linear infinite';

        try {
            const res  = await fetch('/api/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                showError(data.message || 'Failed to load stats. The stats API may not be available yet.');
                // Still attempt to render partial data if available
            }

            const s = data.data ?? data;

            // Summary cards
            setCard('statTotalImages', fmt(s.total_images ?? s.images_total ?? s.images_count));
            setCard('statCreditsUsed', fmt(s.credits_used));
            setCard('statCreditsLeft', fmt(s.credits_left ?? s.credits_remaining));
            setCard('statModelsUsed',  fmt(s.models_count ?? (s.models_breakdown ?? s.by_model ?? []).length));

            // Credit bar
            renderCreditBar(s.credits_used, s.credits_left ?? s.credits_remaining);

            // Charts
            renderTimelineChart(s.daily ?? s.images_by_date ?? s.timeline ?? []);
            renderModelChart(s.models_breakdown ?? s.by_model ?? s.models ?? []);

            // Recent table
            renderRecentTable(s.recent ?? s.recent_images ?? s.activity ?? []);

        } catch (err) {
            showError('Network error while loading stats. Please try again.');
            // Clean up spinners
            ['timelineLoading','modelLoading','recentLoading'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<span class="text-slate-500 text-sm">Failed to load.</span>';
            });
        } finally {
            refreshIcon.style.animation = '';
        }
    }

    // Refresh button
    document.getElementById('refreshStatsBtn').addEventListener('click', function () {
        location.reload();
    });

    loadStats();
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush
