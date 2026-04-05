import '../css/dashboard-desk.css';
import Chart from 'chart.js/auto';

function initDeskChart(labels, before, after) {
    const canvas = document.getElementById('desk-energy-chart');
    if (!canvas || !labels.length) {
        return;
    }
    const t = window.deskI18n || {};
    const ctx = canvas.getContext('2d');
    const glowBlue = 'rgba(56, 189, 248, 0.35)';
    const glowPink = 'rgba(244, 114, 182, 0.35)';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: t.chartLegendBefore || 'Before',
                    data: before,
                    borderColor: '#38bdf8',
                    backgroundColor: glowBlue,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#38bdf8',
                    pointBorderColor: 'rgba(255,255,255,0.35)',
                    pointBorderWidth: 1,
                    borderWidth: 2.5,
                },
                {
                    label: t.chartLegendAfter || 'After',
                    data: after,
                    borderColor: '#f472b6',
                    backgroundColor: glowPink,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#f472b6',
                    pointBorderColor: 'rgba(255,255,255,0.35)',
                    pointBorderWidth: 1,
                    borderWidth: 2.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: 'rgba(233, 213, 255, 0.9)',
                        font: { size: 11, family: 'Figtree, system-ui, sans-serif' },
                        boxWidth: 10,
                        padding: 14,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 10, 24, 0.95)',
                    titleColor: '#e9d5ff',
                    bodyColor: '#f5f3ff',
                    borderColor: 'rgba(168, 85, 247, 0.35)',
                    borderWidth: 1,
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.06)' },
                    ticks: { color: '#c4b5fd', maxRotation: 0 },
                    title: {
                        display: true,
                        text: t.chartX || '',
                        color: '#a78bfa',
                        font: { size: 11 },
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.06)' },
                    ticks: { color: '#c4b5fd' },
                    title: {
                        display: true,
                        text: t.chartY || '',
                        color: '#a78bfa',
                        font: { size: 11 },
                    },
                },
            },
        },
    });
}

function recalcQuick() {
    const mA = parseFloat(document.getElementById('desk-m-a')?.value || '0');
    const vA = parseFloat(document.getElementById('desk-v-a')?.value || '0');
    const mB = parseFloat(document.getElementById('desk-m-b')?.value || '0');
    const vB = parseFloat(document.getElementById('desk-v-b')?.value || '0');

    const pBefore = mA * vA + mB * vB;
    const M = mA + mB;
    let pAfter = pBefore;
    if (M > 1e-9) {
        const v1f = ((mA - mB) * vA + 2 * mB * vB) / M;
        const v2f = ((mB - mA) * vB + 2 * mA * vA) / M;
        pAfter = mA * v1f + mB * v2f;
    }

    const elP = document.getElementById('desk-p-before');
    const elA = document.getElementById('desk-p-after');
    if (elP) {
        elP.textContent = String(Math.round(pBefore));
    }
    if (elA) {
        elA.textContent = String(Math.round(pAfter));
    }

    const conserved = Math.abs(pAfter - pBefore) < 0.51 + 1e-6 * Math.max(1, Math.abs(pBefore));
    const lawEl = document.getElementById('desk-momentum-law');
    const t = window.deskI18n || {};
    if (lawEl) {
        lawEl.classList.toggle('text-emerald-400', conserved);
        lawEl.classList.toggle('text-amber-300', !conserved);
        lawEl.textContent = conserved ? t.momentumOk || '' : t.momentumApprox || '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('aicoll-desk');
    if (!root) {
        return;
    }
    const i18nEl = document.getElementById('desk-i18n-data');
    if (i18nEl?.textContent?.trim()) {
        try {
            window.deskI18n = JSON.parse(i18nEl.textContent);
        } catch {
            /* ignore */
        }
    }
    let labels = [];
    let before = [];
    let after = [];
    try {
        labels = JSON.parse(root.dataset.chartLabels || '[]');
        before = JSON.parse(root.dataset.chartBefore || '[]');
        after = JSON.parse(root.dataset.chartAfter || '[]');
    } catch {
        /* ignore */
    }
    initDeskChart(labels, before, after);

    ['desk-m-a', 'desk-v-a', 'desk-m-b', 'desk-v-b'].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', recalcQuick);
    });
    recalcQuick();
});
