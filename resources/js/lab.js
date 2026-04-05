const axios = window.axios;
if (!axios) {
    console.warn('AIColl lab: axios missing; load app.js first.');
}

/** px per 1 m: lower = smaller balls on screen + wider world fits on canvas (motion visible longer). */
const LAB_VIEW_SCALE = 40;

/** Запасные подписи блока «Решение», если JSON со страницы не распарсился (совпадает с ru.json). */
const LAB_SIM_I18N_DEFAULTS = {
    title: 'Решение (расчёт на странице)',
    given: 'Исходные данные',
    body1: 'Тело 1',
    body2: 'Тело 2',
    mass: 'Масса',
    velocity: 'Скорость v',
    vector: 'Вектор',
    magnitude: 'Модуль',
    momentumSection: 'Импульс p = m·v',
    energySection: 'Кинетическая энергия E = mv²/2',
    before: 'до столкновения',
    after: 'после столкновения',
    delta: 'изменение Δ',
    systemSum: 'Сумма (тела 1 + 2)',
    collisionHappened: 'Столкновение было',
    collisionNo: 'Столкновения не было (тела не соударились)',
    downloadPdf: 'Скачать PDF',
    serviceUnavailable:
        'Сервер физики недоступен. Решение посчитано на странице; сохранение в базу пропущено.',
    savedExperiment: 'Эксперимент сохранён (№ :id).',
    energyUnit: 'Дж',
    momentumUnit: 'кг·м/с',
    collisionType: 'Тип столкновения',
    elastic: 'Упругий импульс',
    inelastic: 'Неупругий импульс',
    unitKg: 'кг',
    running: 'Симуляция…',
    historyDefaultNamePattern: '2D симулятор · эксперимент №:id',
    experimentAndHistorySaved:
        'Эксперимент № :id сохранён, запись добавлена в историю.',
    saveHistoryFailed: 'Не удалось добавить в историю.',
    summaryTableTitle: 'Сводка: система (импульс и энергия)',
    summaryColMetric: 'Показатель',
    summaryColBefore: 'До столкновения',
    summaryColAfter: 'После столкновения',
    summaryColDelta: 'Изменение (Δ)',
    summaryRowMomentum: 'Полный импульс',
    summaryRowEnergy: 'Полная энергия',
};

function mergeLabSimI18n(t) {
    return {
        ...LAB_SIM_I18N_DEFAULTS,
        ...(t && typeof t === 'object' ? t : {}),
    };
}

function readLabSimI18nFromPage() {
    const el = document.getElementById('lab-sim-i18n-data');
    if (!el?.textContent?.trim()) {
        return {};
    }
    try {
        return JSON.parse(el.textContent);
    } catch (e) {
        console.warn('AIColl: не удалось разобрать lab-sim-i18n-data', e);
        return {};
    }
}

/** Same formula as App\Http\Controllers\Api\ExperimentController::defaultBodyRadius */
function bodyRadius(m, vx, vy) {
    const ref = 2;
    const base = 0.2;
    const rm = base * Math.cbrt(Math.max(0.01, m) / ref);
    const speed = Math.hypot(vx, vy);
    const boost = 1 + 0.05 * Math.min(speed / 3, 2.5);
    return Math.min(0.45, Math.max(0.06, rm * boost));
}

function readFormPayload(form) {
    const fd = new FormData(form);
    const get = (k) => fd.get(k);
    const material = get('material') || 'steel';
    const m1 = parseFloat(get('m1'));
    const m2 = parseFloat(get('m2'));
    const v1 = { x: parseFloat(get('v1_x')), y: parseFloat(get('v1_y')) };
    const v2 = { x: parseFloat(get('v2_x')), y: parseFloat(get('v2_y')) };
    const payload = {
        m1,
        m2,
        r1: bodyRadius(m1, v1.x, v1.y),
        r2: bodyRadius(m2, v2.x, v2.y),
        p1: { x: -1, y: 0 },
        p2: { x: 0.5, y: 0 },
        v1,
        v2,
        collision_type: get('collision_type') || 'elastic',
        material,
        mode: 'manual',
    };
    if (material === 'custom') {
        payload.restitution = parseFloat(get('restitution') || '0.8');
    }
    return payload;
}

function worldToScreen(px, py, ox, oy, scale) {
    return { x: ox + px * scale, y: oy - py * scale };
}

/**
 * Ось x: подписи в «условных единицах» ×10 к метру (10, 15, 20 …; 10 = 1 м).
 */
function drawXAxisScale(ctx, w, h, ox, scale, axisLabel) {
    const K = 10;
    const padding = 10;
    const axisY = h - 28;
    const xMin = (-ox + padding) / scale;
    const xMax = (w - ox - padding) / scale;
    const dMin = xMin * K;
    const dMax = xMax * K;
    const spanDisp = dMax - dMin;
    let stepDisp = 5;
    if (spanDisp > 150) stepDisp = 10;
    else if (spanDisp < 40) stepDisp = 5;

    ctx.save();
    ctx.strokeStyle = '#94a3b8';
    ctx.fillStyle = '#475569';
    ctx.lineWidth = 1;
    ctx.font = 'normal 11px system-ui, "Segoe UI", sans-serif';

    ctx.beginPath();
    ctx.moveTo(padding, axisY);
    ctx.lineTo(w - padding, axisY);
    ctx.stroke();

    const startDisp = Math.ceil(dMin / stepDisp - 1e-9) * stepDisp;
    for (let d = startDisp; d <= dMax + 1e-6; d += stepDisp) {
        const xw = d / K;
        const sx = ox + xw * scale;
        if (sx < padding || sx > w - padding) continue;
        ctx.beginPath();
        ctx.moveTo(sx, axisY);
        ctx.lineTo(sx, axisY + 8);
        ctx.stroke();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        ctx.fillText(String(Math.round(d)), sx, axisY + 10);
    }

    if (axisLabel) {
        ctx.textAlign = 'left';
        ctx.textBaseline = 'bottom';
        ctx.fillStyle = '#64748b';
        ctx.font = 'normal 10px system-ui, "Segoe UI", sans-serif';
        ctx.fillText(axisLabel, padding, axisY - 2);
    }
    ctx.restore();
}

function drawArrowOnCanvas(ctx, x0, y0, x1, y1, color, lineW) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.fillStyle = color;
    ctx.lineWidth = lineW;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(x0, y0);
    ctx.lineTo(x1, y1);
    ctx.stroke();
    const ang = Math.atan2(y1 - y0, x1 - x0);
    const head = lineW * 3.2;
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(
        x1 - head * Math.cos(ang - Math.PI / 7),
        y1 - head * Math.sin(ang - Math.PI / 7),
    );
    ctx.lineTo(
        x1 - head * Math.cos(ang + Math.PI / 7),
        y1 - head * Math.sin(ang + Math.PI / 7),
    );
    ctx.closePath();
    ctx.fill();
    ctx.restore();
}

/** Стрелка скорости внутри шара (мир: y вверх → на холсте −y). */
function drawVelocityArrowInBall(ctx, cx, cy, rpx, vx, vy, color) {
    const spd = Math.hypot(vx, vy);
    if (spd < 1e-9) return;
    const maxL = Math.min(rpx * 0.65, 52);
    const minL = 9;
    const len = Math.max(minL, Math.min(maxL, spd * (rpx / 5.5)));
    const ex = (vx / spd) * len;
    const ey = (-vy / spd) * len;
    drawArrowOnCanvas(ctx, cx, cy, cx + ex, cy + ey, color, 2);
}

/**
 * @param {'above'|'below'} outward — первый шар: снизу; второй: сверху.
 */
function drawBodyLabels(ctx, cx, cy, rpx, mass, vx, vy, opts = {}) {
    const kg = opts.unitKg || 'кг';
    const ms = opts.unitMs || 'м/с';
    const outward = opts.outward || 'below';
    const fmt = (x) => formatLabNumber(x, 2);
    const vySmall = !Number.isFinite(vy) || Math.abs(vy) < 1e-6;
    const vLine = vySmall ? `v = ${fmt(vx)} ${ms}` : `v = ${fmt(vx)}, ${fmt(vy)} ${ms}`;
    const lines = [`m = ${fmt(mass)} ${kg}`, vLine];
    const fontPx = Math.max(11, Math.min(16, rpx * 0.34 + 2));
    const lineH = fontPx + 3;

    const drawLineStrokeFill = (line, tx, ty, fillStyle, strokeLight) => {
        ctx.strokeStyle = strokeLight ? 'rgba(248,250,252,0.9)' : 'rgba(15,23,42,0.55)';
        ctx.lineWidth = 2;
        ctx.lineJoin = 'round';
        ctx.strokeText(line, tx, ty);
        ctx.fillStyle = fillStyle;
        ctx.fillText(line, tx, ty);
    };

    ctx.save();
    ctx.font = `normal ${fontPx}px system-ui, "Segoe UI", sans-serif`;
    ctx.textAlign = 'center';

    if (outward === 'above') {
        ctx.textBaseline = 'top';
        const totalH = lines.length * lineH;
        let ty = cy - rpx - 8 - totalH;
        for (const line of lines) {
            drawLineStrokeFill(line, cx, ty, 'rgb(30,41,59)', true);
            ty += lineH;
        }
    } else {
        ctx.textBaseline = 'top';
        let ty = cy + rpx + 8;
        for (const line of lines) {
            drawLineStrokeFill(line, cx, ty, 'rgb(30,41,59)', true);
            ty += lineH;
        }
    }
    ctx.restore();
}

function drawLabCanvas(canvas, input, drawOpts = {}) {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const {
        physicsV1,
        physicsV2,
        p1 = input.p1,
        p2 = input.p2,
        v1 = input.v1,
        v2 = input.v2,
        collisionFlash = 0,
        collisionPoint = null,
    } = drawOpts;

    const w = canvas.width;
    const h = canvas.height;
    ctx.clearRect(0, 0, w, h);
    const scale = LAB_VIEW_SCALE;
    const ox = w / 2;
    const oy = h / 2;

    const c1 = worldToScreen(p1.x, p1.y, ox, oy, scale);
    const c2 = worldToScreen(p2.x, p2.y, ox, oy, scale);
    const r1px = Math.max(4, input.r1 * scale);
    const r2px = Math.max(4, input.r2 * scale);

    const unitKg = canvas.dataset.unitKg || 'кг';
    const unitMs = canvas.dataset.unitMs || 'м/с';
    const axisXLabel = canvas.dataset.axisXLabel || '';

    ctx.strokeStyle = '#cbd5e1';
    ctx.strokeRect(0.5, 0.5, w - 1, h - 1);

    ctx.fillStyle = '#6366f1';
    ctx.beginPath();
    ctx.arc(c1.x, c1.y, r1px, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#f97316';
    ctx.beginPath();
    ctx.arc(c2.x, c2.y, r2px, 0, Math.PI * 2);
    ctx.fill();

    const dispV1 = physicsV1 ? { x: physicsV1.x, y: physicsV1.y } : v1;
    const dispV2 = physicsV2 ? { x: physicsV2.x, y: physicsV2.y } : v2;

    drawVelocityArrowInBall(ctx, c1.x, c1.y, r1px, dispV1.x, dispV1.y, '#fef08a');
    drawVelocityArrowInBall(ctx, c2.x, c2.y, r2px, dispV2.x, dispV2.y, '#0f172a');

    drawBodyLabels(ctx, c1.x, c1.y, r1px, input.m1, dispV1.x, dispV1.y, {
        unitKg,
        unitMs,
        outward: 'below',
    });
    drawBodyLabels(ctx, c2.x, c2.y, r2px, input.m2, dispV2.x, dispV2.y, {
        unitKg,
        unitMs,
        outward: 'above',
    });

    const flashMax = 48;
    if (collisionFlash > 0 && collisionPoint) {
        const cp = worldToScreen(collisionPoint.x, collisionPoint.y, ox, oy, scale);
        const pulse = collisionFlash / flashMax;
        ctx.save();
        ctx.globalAlpha = 0.2 + pulse * 0.55;
        ctx.strokeStyle = '#ca8a04';
        ctx.lineWidth = 4;
        ctx.beginPath();
        ctx.arc(cp.x, cp.y, 10 + (flashMax - collisionFlash) * 2.2, 0, Math.PI * 2);
        ctx.stroke();
        ctx.globalAlpha = 0.85;
        ctx.fillStyle = '#a16207';
        ctx.font = 'normal 14px system-ui, "Segoe UI", sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        const label = canvas.dataset.collisionLabel || 'Collision!';
        ctx.fillText(label, cp.x, cp.y - 10);
        ctx.restore();
    }

    drawXAxisScale(ctx, w, h, ox, scale, axisXLabel);
}

/** Абсолютно неупругий: одна общая скорость (импульс сохраняется), векторы v совпадают. */
function resolvePerfectlyInelastic(b1, b2) {
    const dx = b2.p.x - b1.p.x;
    const dy = b2.p.y - b1.p.y;
    const dist = Math.hypot(dx, dy) || 1e-9;
    const nx = dx / dist;
    const ny = dy / dist;
    const minDist = b1.r + b2.r;
    const overlap = minDist - dist;
    if (overlap > 0) {
        const sep = overlap * 0.51;
        const inv = 1 / (b1.m + b2.m);
        b1.p.x -= nx * sep * (b2.m * inv);
        b1.p.y -= ny * sep * (b2.m * inv);
        b2.p.x += nx * sep * (b1.m * inv);
        b2.p.y += ny * sep * (b1.m * inv);
    }
    const vrel = (b2.v.x - b1.v.x) * nx + (b2.v.y - b1.v.y) * ny;
    if (vrel >= 0) return false;
    const M = b1.m + b2.m;
    const vx = (b1.m * b1.v.x + b2.m * b2.v.x) / M;
    const vy = (b1.m * b1.v.y + b2.m * b2.v.y) / M;
    b1.v.x = vx;
    b1.v.y = vy;
    b2.v.x = vx;
    b2.v.y = vy;
    return true;
}

function resolveCircleCollision(b1, b2, restitution) {
    const dx = b2.p.x - b1.p.x;
    const dy = b2.p.y - b1.p.y;
    const dist = Math.hypot(dx, dy) || 1e-9;
    const nx = dx / dist;
    const ny = dy / dist;
    const minDist = b1.r + b2.r;
    const overlap = minDist - dist;
    if (overlap > 0) {
        const sep = overlap * 0.51;
        const inv = 1 / (b1.m + b2.m);
        b1.p.x -= nx * sep * (b2.m * inv);
        b1.p.y -= ny * sep * (b2.m * inv);
        b2.p.x += nx * sep * (b1.m * inv);
        b2.p.y += ny * sep * (b1.m * inv);
    }
    const vrel = (b2.v.x - b1.v.x) * nx + (b2.v.y - b1.v.y) * ny;
    if (vrel >= 0) return false;
    const invM = 1 / b1.m + 1 / b2.m;
    const j = -(1 + restitution) * vrel / invM;
    b1.v.x -= (j / b1.m) * nx;
    b1.v.y -= (j / b1.m) * ny;
    b2.v.x += (j / b2.m) * nx;
    b2.v.y += (j / b2.m) * ny;
    return true;
}

function vecMag(v) {
    return Math.hypot(v.x, v.y);
}

/**
 * Қысқа көрініс: бүтін сан болса бөлшек бөлігі жоқ; бөлшек болса үтірден кейін ең көбі 2 таңба (артық нөлдер жоқ).
 */
function formatLabNumber(x, maxDecimals = 2) {
    if (!Number.isFinite(x)) return '—';
    const factor = 10 ** maxDecimals;
    let rounded = Math.round(x * factor) / factor;
    if (Object.is(rounded, -0)) {
        rounded = 0;
    }
    const nearInt = Math.round(rounded);
    if (Math.abs(rounded - nearInt) < 1e-9) {
        return String(nearInt);
    }
    let s = rounded.toFixed(maxDecimals);
    s = s.replace(/(\.\d*?)0+$/, '$1');
    s = s.replace(/\.$/, '');
    return s;
}

function fmtVec(v, maxDecimals = 2) {
    return `(${formatLabNumber(v.x, maxDecimals)}, ${formatLabNumber(v.y, maxDecimals)})`;
}

function buildLabPhysicsReport(input, anim) {
    const m1 = input.m1;
    const m2 = input.m2;
    const v1i = { ...input.v1 };
    const v2i = { ...input.v2 };
    const v1f = { ...anim.b1.v };
    const v2f = { ...anim.b2.v };

    const p1i = { x: m1 * v1i.x, y: m1 * v1i.y };
    const p2i = { x: m2 * v2i.x, y: m2 * v2i.y };
    const p1f = { x: m1 * v1f.x, y: m1 * v1f.y };
    const p2f = { x: m2 * v2f.x, y: m2 * v2f.y };

    const dp1 = { x: p1f.x - p1i.x, y: p1f.y - p1i.y };
    const dp2 = { x: p2f.x - p2i.x, y: p2f.y - p2i.y };

    const E1i = 0.5 * m1 * (v1i.x * v1i.x + v1i.y * v1i.y);
    const E2i = 0.5 * m2 * (v2i.x * v2i.x + v2i.y * v2i.y);
    const E1f = 0.5 * m1 * (v1f.x * v1f.x + v1f.y * v1f.y);
    const E2f = 0.5 * m2 * (v2f.x * v2f.x + v2f.y * v2f.y);

    const pSysI = { x: p1i.x + p2i.x, y: p1i.y + p2i.y };
    const pSysF = { x: p1f.x + p2f.x, y: p1f.y + p2f.y };

    return {
        collisionType: input.collision_type,
        collided: anim.collided,
        m1,
        m2,
        v1i,
        v2i,
        v1f,
        v2f,
        p1i,
        p2i,
        p1f,
        p2f,
        dp1,
        dp2,
        E1i,
        E2i,
        E1f,
        E2f,
        dE1: E1f - E1i,
        dE2: E2f - E2i,
        EtotI: E1i + E2i,
        EtotF: E1f + E2f,
        pSysI,
        pSysF,
        dPsys: { x: pSysF.x - pSysI.x, y: pSysF.y - pSysI.y },
    };
}

function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function clearLabSummaryAboveCharts() {
    const el = document.getElementById('lab-summary-above-charts');
    if (!el) return;
    el.innerHTML = '';
    el.classList.add('hidden');
    document.getElementById('lab-summary-placeholder')?.classList.remove('hidden');
}

function renderLabSolutionPanel(report, tRaw) {
    const t = mergeLabSimI18n(tRaw);
    const body = document.getElementById('lab-results-body');
    const heading = document.getElementById('lab-results-heading');
    const pdfBtn = document.getElementById('lab-download-pdf');
    if (!body || !heading || !pdfBtn) return;

    heading.textContent = t.title;
    pdfBtn.textContent = t.downloadPdf;

    const uP = t.momentumUnit;
    const uE = t.energyUnit;
    const ct =
        report.collisionType === 'elastic' ? t.elastic : t.inelastic;

    const row = (a, b) =>
        `<tr class="border-b border-violet-100/80"><td class="py-1.5 pr-4 text-violet-700/85">${escHtml(a)}</td><td class="py-1.5 font-mono text-violet-950">${escHtml(String(b))}</td></tr>`;

    const subTable = (rows) =>
        `<table class="w-full text-left border-collapse">${rows.join('')}</table>`;

    const momBlock = (label, pi, pf, dp) => {
        const rowsInner = [
            row(`${t.before} (${label})`, `${t.vector}: ${fmtVec(pi)}; ${t.magnitude}: ${formatLabNumber(vecMag(pi))} ${uP}`),
            row(`${t.after} (${label})`, `${t.vector}: ${fmtVec(pf)}; ${t.magnitude}: ${formatLabNumber(vecMag(pf))} ${uP}`),
            row(`${t.delta} (${label})`, `${t.vector}: ${fmtVec(dp)}; ${t.magnitude}: ${formatLabNumber(vecMag(dp))} ${uP}`),
        ];
        return subTable(rowsInner);
    };

    const eneBlock = (label, Ei, Ef, dE) =>
        subTable([
            row(`${t.before} (${label})`, `${formatLabNumber(Ei)} ${uE}`),
            row(`${t.after} (${label})`, `${formatLabNumber(Ef)} ${uE}`),
            row(`${t.delta} (${label})`, `${dE >= 0 ? '+' : ''}${formatLabNumber(dE)} ${uE}`),
        ]);

    const dEtot = report.EtotF - report.EtotI;
    const dPmag = vecMag(report.dPsys);
    const summaryTable = `
        <div class="mb-1">
            <div class="overflow-x-auto rounded-xl border border-violet-200/90 bg-white/70 shadow-sm shadow-violet-500/[0.04]">
                <table class="w-full min-w-[18rem] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-violet-200 bg-violet-50/90 text-left">
                            <th scope="col" class="py-2.5 px-3 font-semibold text-violet-950">${escHtml(t.summaryColMetric)}</th>
                            <th scope="col" class="py-2.5 px-3 font-semibold text-violet-950">${escHtml(t.summaryColBefore)}</th>
                            <th scope="col" class="py-2.5 px-3 font-semibold text-violet-950">${escHtml(t.summaryColAfter)}</th>
                            <th scope="col" class="py-2.5 px-3 font-semibold text-violet-950">${escHtml(t.summaryColDelta)}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-violet-100">
                            <th scope="row" class="py-2.5 px-3 text-left font-semibold text-violet-900 align-top">${escHtml(t.summaryRowMomentum)}</th>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${formatLabNumber(vecMag(report.pSysI))} ${uP}`)}</td>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${formatLabNumber(vecMag(report.pSysF))} ${uP}`)}</td>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${formatLabNumber(dPmag)} ${uP}`)}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 px-3 text-left font-semibold text-violet-900 align-top">${escHtml(t.summaryRowEnergy)}</th>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${formatLabNumber(report.EtotI)} ${uE}`)}</td>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${formatLabNumber(report.EtotF)} ${uE}`)}</td>
                            <td class="py-2.5 px-3 font-mono tabular-nums text-violet-950">${escHtml(`${dEtot > 0 ? '+' : ''}${formatLabNumber(dEtot)} ${uE}`)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>`;

    const summarySlot = document.getElementById('lab-summary-above-charts');
    if (summarySlot) {
        summarySlot.innerHTML = summaryTable;
        summarySlot.classList.remove('hidden');
    }
    document.getElementById('lab-summary-placeholder')?.classList.add('hidden');

    body.innerHTML = `
        <p class="text-violet-800/90">${escHtml(report.collided ? t.collisionHappened : t.collisionNo)} · ${escHtml(t.collisionType)}: ${escHtml(ct)}</p>
        <div>
            <h4 class="font-semibold text-violet-950 mb-2">${escHtml(t.given)}</h4>
            ${subTable([
                row(`${t.body1}: ${t.mass}`, `${formatLabNumber(report.m1)} ${t.unitKg || 'кг'}`),
                row(`${t.body1}: ${t.velocity}`, fmtVec(report.v1i)),
                row(`${t.body2}: ${t.mass}`, `${formatLabNumber(report.m2)} ${t.unitKg || 'кг'}`),
                row(`${t.body2}: ${t.velocity}`, fmtVec(report.v2i)),
            ])}
        </div>
        <div>
            <h4 class="font-semibold text-violet-950 mb-2">${escHtml(t.momentumSection)}</h4>
            ${momBlock(t.body1, report.p1i, report.p1f, report.dp1)}
            <div class="h-3"></div>
            ${momBlock(t.body2, report.p2i, report.p2f, report.dp2)}
        </div>
        <div>
            <h4 class="font-semibold text-violet-950 mb-2">${escHtml(t.energySection)}</h4>
            ${eneBlock(t.body1, report.E1i, report.E1f, report.dE1)}
            <div class="h-3"></div>
            ${eneBlock(t.body2, report.E2i, report.E2f, report.dE2)}
        </div>
    `;
}

function friendlyApiErrorMessage(err, tRaw) {
    const t = mergeLabSimI18n(tRaw);
    const msg = (err.response?.data?.message || err.message || '').toString();
    if (
        /cURL|Failed to connect|ECONNREFUSED|Could not connect|Network Error|8001|service unavailable/i.test(
            msg,
        )
    ) {
        return t.serviceUnavailable;
    }
    if (msg.length > 180) return `${msg.slice(0, 177)}…`;
    return msg || t.serviceUnavailable;
}

async function downloadLabSolutionPdf() {
    const card = document.querySelector('#lab-results-wrap .admin-surface');
    if (!card) return;
    const html2canvas = (await import('html2canvas')).default;
    const { jsPDF } = await import('jspdf');
    const snap = await html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
    });
    const imgData = snap.toDataURL('image/png');
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const margin = 36;
    const maxW = pageW - 2 * margin;
    const maxH = pageH - 2 * margin;
    const rw = snap.width;
    const rh = snap.height;
    const scale = Math.min(maxW / rw, maxH / rh);
    const w = rw * scale;
    const h = rh * scale;
    pdf.addImage(imgData, 'PNG', margin, margin, w, h);
    pdf.save('simulation-report.pdf');
}

let labAnimToken = 0;
/** Set while симулятор кідіруде; «Дальше» басқанда шақырылады. */
let labSimResume = null;

/**
 * Integrate motion and resolve collision on the canvas; stop after a short post-collision run.
 * High speeds use a slower visual time scale so bodies stay on the canvas longer.
 * After the first impulse, optionally freeze until #lab-continue is clicked (for class discussion).
 */
function runLabCollisionAnim(canvas, input, continueEl, onDone) {
    const myToken = ++labAnimToken;
    const elastic = input.collision_type === 'elastic';

    const speed0 = Math.max(
        Math.hypot(input.v1.x, input.v1.y),
        Math.hypot(input.v2.x, input.v2.y),
        0.35,
    );
    const slowFactor = Math.min(1, 3.2 / speed0);

    const b1 = {
        m: input.m1,
        r: input.r1,
        p: { ...input.p1 },
        v: { ...input.v1 },
    };
    const b2 = {
        m: input.m2,
        r: input.r2,
        p: { ...input.p2 },
        v: { ...input.v2 },
    };
    let collided = false;
    let afterCollideFrames = 0;
    let frameCount = 0;
    let collisionFlash = 0;
    let collisionPoint = null;
    let frozen = false;
    let hasPausedForCollision = false;
    const maxFramesNoCollision = Math.min(8000, Math.ceil(900 / Math.max(slowFactor, 0.06)));
    const maxFramesAfterCollision = Math.min(480, Math.ceil(180 / Math.max(slowFactor, 0.06)));

    const step = () => {
        if (myToken !== labAnimToken) return;
        if (frozen) return;

        frameCount++;
        if (collisionFlash > 0) collisionFlash--;

        const dt = (1 / 120) * slowFactor;
        let hitThisStep = false;
        for (let s = 0; s < 2; s++) {
            b1.p.x += b1.v.x * dt;
            b1.p.y += b1.v.y * dt;
            b2.p.x += b2.v.x * dt;
            b2.p.y += b2.v.y * dt;
            const d = Math.hypot(b2.p.x - b1.p.x, b2.p.y - b1.p.y);
            if (d < b1.r + b2.r - 1e-6) {
                const hit = elastic
                    ? resolveCircleCollision(b1, b2, 1)
                    : resolvePerfectlyInelastic(b1, b2);
                if (hit) {
                    collided = true;
                    hitThisStep = true;
                    collisionFlash = 48;
                    collisionPoint = {
                        x: (b1.p.x + b2.p.x) / 2,
                        y: (b1.p.y + b2.p.y) / 2,
                    };
                }
            }
        }

        drawLabCanvas(canvas, input, {
            p1: b1.p,
            p2: b2.p,
            v1: b1.v,
            v2: b2.v,
            collisionFlash,
            collisionPoint,
        });

        if (hitThisStep && continueEl && !hasPausedForCollision) {
            hasPausedForCollision = true;
            frozen = true;
            labSimResume = () => {
                if (myToken !== labAnimToken) return;
                labSimResume = null;
                continueEl.classList.add('hidden');
                frozen = false;
                requestAnimationFrame(step);
            };
            continueEl.classList.remove('hidden');
            return;
        }

        if (collided) afterCollideFrames++;

        const finishAnim = () => {
            onDone?.({
                b1: {
                    m: b1.m,
                    r: b1.r,
                    p: { ...b1.p },
                    v: { ...b1.v },
                },
                b2: {
                    m: b2.m,
                    r: b2.r,
                    p: { ...b2.p },
                    v: { ...b2.v },
                },
                collided,
            });
        };

        if (collided && afterCollideFrames >= maxFramesAfterCollision) {
            finishAnim();
            return;
        }
        if (!collided && frameCount >= maxFramesNoCollision) {
            finishAnim();
            return;
        }
        requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

function initLabPage() {
    const form = document.getElementById('lab-form');
    const canvas = document.getElementById('lab-canvas');
    const statusEl = document.getElementById('lab-status');
    const jsonEl = document.getElementById('lab-json');
    const continueBtn = document.getElementById('lab-continue');
    const resetBtn = document.getElementById('lab-reset');
    const resultsWrap = document.getElementById('lab-results-wrap');
    const pdfBtn = document.getElementById('lab-download-pdf');
    const labPageI18n = mergeLabSimI18n(readLabSimI18nFromPage());

    if (!form || !canvas) return;

    continueBtn?.addEventListener('click', () => {
        const fn = labSimResume;
        if (fn) fn();
    });

    const refreshPreview = () => {
        try {
            const input = readFormPayload(form);
            drawLabCanvas(canvas, input);
        } catch {
            /* ignore */
        }
    };

    resetBtn?.addEventListener('click', () => {
        labAnimToken++;
        labSimResume = null;
        continueBtn?.classList.add('hidden');
        jsonEl?.classList.add('hidden');
        resultsWrap?.classList.add('hidden');
        statusEl.textContent = '';
        clearLabSummaryAboveCharts();
        refreshPreview();
    });

    pdfBtn?.addEventListener('click', () => {
        downloadLabSolutionPdf().catch(console.error);
    });

    form.addEventListener('input', refreshPreview);
    form.addEventListener('change', refreshPreview);
    refreshPreview();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        labSimResume = null;
        continueBtn?.classList.add('hidden');
        resultsWrap?.classList.add('hidden');
        clearLabSummaryAboveCharts();
        statusEl.textContent = labPageI18n.running || '…';
        jsonEl?.classList.add('hidden');
        const payload = readFormPayload(form);
        const apiPromise = axios.post('/api/lab/experiments', payload).then((r) => r.data);

        runLabCollisionAnim(canvas, payload, continueBtn, async (animResult) => {
            labSimResume = null;
            continueBtn?.classList.add('hidden');

            const report = buildLabPhysicsReport(payload, animResult);
            renderLabSolutionPanel(report, labPageI18n);
            resultsWrap?.classList.remove('hidden');

            drawLabCanvas(canvas, payload, {
                p1: animResult.b1.p,
                p2: animResult.b2.p,
                v1: animResult.b1.v,
                v2: animResult.b2.v,
            });

            try {
                const data = await apiPromise;
                try {
                    await post2dLabHistoryRecord(data, labPageI18n);
                    statusEl.textContent = (
                        labPageI18n.experimentAndHistorySaved || ''
                    ).replace(':id', String(data.id));
                } catch (histErr) {
                    const base = (labPageI18n.savedExperiment || '').replace(
                        ':id',
                        String(data.id),
                    );
                    const extra =
                        friendlyApiErrorMessage(histErr, labPageI18n) ||
                        labPageI18n.saveHistoryFailed ||
                        '';
                    statusEl.textContent = `${base} ${extra}`.trim();
                }
            } catch (err) {
                statusEl.textContent = friendlyApiErrorMessage(err, labPageI18n);
            }
        });
    });
}

function initAnalysisPage() {
    const root = document.getElementById('aicoll-analysis');
    const canvas = document.getElementById('analysis-chart');
    if (!root || !canvas) return;
    const raw = root.getAttribute('data-experiment');
    if (!raw) return;
    let exp;
    try {
        exp = JSON.parse(raw);
    } catch {
        return;
    }
    import('chart.js/auto').then(({ default: Chart }) => {
        const p = exp.physics_result || {};
        const a = exp.ai_prediction || {};
        const labels = ['v1x', 'v1y', 'v2x', 'v2y'];
        const phys = [p.v1?.x, p.v1?.y, p.v2?.x, p.v2?.y];
        const ai = [a.v1?.x, a.v1?.y, a.v2?.x, a.v2?.y];
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Physics', data: phys, backgroundColor: '#6366f1' },
                    { label: 'AI', data: ai, backgroundColor: '#f97316' },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: false } },
            },
        });
    });
}

function readSim3dI18n() {
    const el = document.getElementById('sim3d-i18n-data');
    if (!el?.textContent?.trim()) {
        return {};
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return {};
    }
}

function sim3dFmtInterval(template, low, high) {
    const t = template || ':low — :high';
    return t.replace(':low', String(low)).replace(':high', String(high));
}

function renderCollisionProtocol(proto) {
    const wrap = document.getElementById('sim3d-protocol-wrap');
    const root = document.getElementById('sim3d-protocol-root');
    if (!wrap || !root) return;
    if (!proto?.blocks?.length) {
        root.innerHTML = '';
        wrap.classList.add('hidden');
        return;
    }
    let html = `<h4 class="text-sm font-bold uppercase tracking-wide text-violet-800 mb-4 lg:text-base lg:mb-5">${escHtml(proto.title || '')}</h4>`;
    for (const block of proto.blocks) {
        html += '<section class="space-y-4 border-t border-violet-100/90 pt-6 first:border-0 first:pt-0">';
        html += `<h5 class="text-base font-semibold text-violet-950 lg:text-lg">${escHtml(block.heading || '')}</h5>`;
        if (block.type === 'physics') {
            html += '<div class="mt-2 grid gap-4 sm:gap-5 lg:grid-cols-2 xl:grid-cols-3">';
            for (const step of block.steps || []) {
                html += '<div class="rounded-xl border border-violet-100 bg-violet-50/70 p-4 space-y-2 shadow-sm shadow-violet-500/[0.04]">';
                html += `<p class="text-xs font-semibold text-violet-800 sm:text-sm">${escHtml(step.title || '')}</p>`;
                html += `<p class="font-mono text-xs text-violet-900 break-words sm:text-sm">${escHtml(step.formula || '')}</p>`;
                html += `<p class="text-sm text-violet-800/95 leading-relaxed sm:text-[15px]">${escHtml(step.detail || '')}</p>`;
                html += '</div>';
            }
            html += '</div>';
        } else {
            for (const p of block.paragraphs || []) {
                html += `<p class="text-sm text-violet-800/95 leading-relaxed sm:text-base max-w-5xl">${escHtml(p)}</p>`;
            }
        }
        html += '</section>';
    }
    root.innerHTML = html;
    wrap.classList.remove('hidden');
}

/**
 * Сохраняет запись 2D-симулятора в общую историю (/lab/history).
 */
function post2dLabHistoryRecord(data, tRaw) {
    const t = mergeLabSimI18n(tRaw);
    const p = t.historyDefaultNamePattern || '2D #:id';
    const name = p.replace(':id', String(data.id)).slice(0, 255);
    return axios.post('/api/lab/records', {
        name,
        source: 'simulator_2d',
        payload: {
            experiment_id: data.id,
            mode: data.mode ?? null,
        },
    });
}

/**
 * Сохраняет результат 3D-анализа в общую историю (/lab/history).
 */
function postVideoHistoryRecord(data, file) {
    const raw = String(file?.name || 'video')
        .replace(/\.[^/.]+$/, '')
        .trim();
    const name = (raw.length ? raw : 'Video').slice(0, 255);
    return axios.post('/api/lab/records', {
        name,
        source: 'simulator_3d',
        payload: {
            trace_id: data.trace_id,
            mode: data.mode,
            file: data.file,
            collision_scenario: data.collision_scenario,
            physics_analysis: data.physics_analysis,
            collision_protocol: data.collision_protocol,
            inference: data.inference,
            confidence: data.confidence,
            training_hint: data.training_hint,
            pipeline: data.pipeline,
        },
    });
}

function initVideoPage() {
    const dropzone = document.getElementById('sim3d-dropzone');
    const fileInput = document.getElementById('sim3d-file');
    const analyzeBtn = document.getElementById('sim3d-analyze');
    const clearBtn = document.getElementById('sim3d-clear');
    const filenameEl = document.getElementById('sim3d-filename');
    const emptyWrap = document.getElementById('sim3d-empty');
    const resultsWrap = document.getElementById('sim3d-results');
    const errorEl = document.getElementById('sim3d-error');
    const badge = document.getElementById('sim3d-badge');
    const confidenceBar = document.getElementById('sim3d-confidence-bar');
    const confidenceVal = document.getElementById('sim3d-confidence-val');
    const confidenceLabel = document.getElementById('sim3d-confidence-label');

    if (!dropzone || !fileInput || !analyzeBtn || !clearBtn) {
        return;
    }

    const t = readSim3dI18n();

    const titleEl = document.getElementById('sim3d-drop-title');
    const introEl = document.getElementById('sim3d-intro');
    const hintEl = document.getElementById('sim3d-drop-hint');
    const formatsEl = document.getElementById('sim3d-formats');
    const emptyText = document.getElementById('sim3d-empty-text');

    if (titleEl) titleEl.textContent = t.dropTitle || '';
    if (introEl) introEl.textContent = t.intro || '';
    if (hintEl) hintEl.textContent = t.dropHint || '';
    if (formatsEl) formatsEl.textContent = t.formats || '';
    analyzeBtn.textContent = t.analyze || 'Analyze';
    clearBtn.textContent = t.clear || 'Clear';
    if (emptyText) emptyText.textContent = t.emptyRight || '';
    if (confidenceLabel) confidenceLabel.textContent = t.confidence || '';

    const setMetricLabel = (name, text) => {
        const el = document.querySelector(`[data-metric-label="${name}"]`);
        if (el) el.textContent = text || '';
    };
    setMetricLabel('speed', t.metricSpeed);
    setMetricLabel('mass', t.metricMass);
    setMetricLabel('model', t.metricModel);
    setMetricLabel('cost', t.metricCost);

    const elScenarioTitle = document.getElementById('sim3d-scenario-title');
    if (elScenarioTitle) elScenarioTitle.textContent = t.scenarioTitle || '';

    const uKmh = t.unitKmh || 'km/h';
    const uKg = t.unitKg || 'kg';
    const fmtInt = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });

    let selectedFile = null;

    const showError = (msg) => {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    };

    const hideError = () => errorEl?.classList.add('hidden');

    const resetView = () => {
        resultsWrap?.classList.add('hidden');
        emptyWrap?.classList.remove('hidden');
        badge?.classList.add('hidden');
        hideError();
        if (confidenceBar) confidenceBar.style.width = '0%';
        renderCollisionProtocol(null);
    };

    const setFile = (file) => {
        selectedFile = file || null;
        if (selectedFile && filenameEl) {
            filenameEl.textContent = selectedFile.name;
            filenameEl.classList.remove('hidden');
            analyzeBtn.disabled = false;
        } else {
            filenameEl?.classList.add('hidden');
            fileInput.value = '';
            analyzeBtn.disabled = true;
        }
    };

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-fuchsia-400', 'bg-fuchsia-50/90');
    });
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-fuchsia-400', 'bg-fuchsia-50/90');
    });
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-fuchsia-400', 'bg-fuchsia-50/90');
        const f = e.dataTransfer?.files?.[0];
        if (f) setFile(f);
    });
    fileInput.addEventListener('change', () => {
        const f = fileInput.files?.[0];
        if (f) setFile(f);
    });

    clearBtn.addEventListener('click', () => {
        setFile(null);
        resetView();
    });

    analyzeBtn.addEventListener('click', async () => {
        hideError();
        if (!selectedFile) {
            showError(t.errorNoFile || 'Select a file.');
            return;
        }
        const fd = new FormData();
        fd.append('clip', selectedFile);
        const labelAnalyze = t.analyze || 'Analyze';
        analyzeBtn.disabled = true;
        analyzeBtn.textContent = t.analyzing || '…';
        try {
            const { data } = await axios.post('/api/lab/video/analyze', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            const inf = data.inference;
            if (!inf) {
                throw new Error('Invalid response');
            }

            const scen = data.collision_scenario;
            const labelScenario = document.getElementById('sim3d-scenario-label');
            const confScenario = document.getElementById('sim3d-scenario-conf');
            if (labelScenario) {
                labelScenario.textContent = scen?.label || '—';
            }
            if (confScenario && scen) {
                const pct = Math.round((scen.confidence || 0) * 100);
                confScenario.textContent = (t.scenarioConfLabel || ':pct%').replace(':pct', String(pct));
            }

            const elSpeed = document.getElementById('sim3d-val-speed');
            const elMass = document.getElementById('sim3d-val-mass');
            const elModel = document.getElementById('sim3d-val-model');
            const elCost = document.getElementById('sim3d-val-cost');

            if (elSpeed) {
                elSpeed.textContent = `${inf.relative_speed_kmh.point} ${uKmh}`;
            }
            document.getElementById('sim3d-range-speed').textContent = `${sim3dFmtInterval(t.interval, inf.relative_speed_kmh.low, inf.relative_speed_kmh.high)} ${uKmh}`;

            if (elMass) {
                elMass.textContent = `${inf.vehicle_mass_kg.point} ${uKg}`;
            }
            document.getElementById('sim3d-range-mass').textContent = `${sim3dFmtInterval(t.interval, inf.vehicle_mass_kg.low, inf.vehicle_mass_kg.high)} ${uKg}`;

            if (elModel) {
                elModel.textContent = inf.vehicle_model || '—';
            }

            const fmtMoney = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
            if (elCost) {
                elCost.textContent = `${fmtMoney.format(inf.repair_cost_kzt.point)} ₸`;
            }
            document.getElementById('sim3d-range-cost').textContent = `${fmtMoney.format(inf.repair_cost_kzt.low)} — ${fmtMoney.format(inf.repair_cost_kzt.high)} ₸`;

            const conf = typeof data.confidence === 'number' ? data.confidence : 0;
            if (confidenceBar) {
                confidenceBar.style.width = `${Math.min(100, Math.max(0, conf * 100))}%`;
            }
            if (confidenceVal) {
                confidenceVal.textContent = `${Math.round(conf * 100)}%`;
            }

            renderCollisionProtocol(data.collision_protocol);

            postVideoHistoryRecord(data, selectedFile).catch(() => {});

            emptyWrap?.classList.add('hidden');
            resultsWrap?.classList.remove('hidden');
            badge?.classList.remove('hidden');
        } catch (err) {
            const msg =
                err.response?.data?.message ||
                (typeof err.response?.data === 'string' ? err.response.data : null) ||
                t.errorGeneric ||
                err.message;
            showError(msg);
        } finally {
            analyzeBtn.disabled = !selectedFile;
            analyzeBtn.textContent = labelAnalyze;
        }
    });
}

async function loadLeaderboard() {
    const body = document.getElementById('lb-body');
    const tagInput = document.getElementById('lb-tag');
    if (!body) return;
    const params = {};
    if (tagInput?.value) params.tag = tagInput.value;
    const { data } = await axios.get('/api/lab/leaderboard', { params });
    body.innerHTML = data
        .map(
            (row, i) => `
        <tr class="border-t border-violet-100/80 hover:bg-violet-50/40 transition-colors">
            <td class="p-3">${i + 1}</td>
            <td class="p-3">${row.user?.name || '—'}</td>
            <td class="p-3">${row.score}</td>
            <td class="p-3">${row.tag}</td>
            <td class="p-3">${row.created_at || ''}</td>
        </tr>`,
        )
        .join('');
}

function initLeaderboardPage() {
    const refresh = document.getElementById('lb-refresh');
    if (!document.getElementById('lb-body')) return;
    refresh?.addEventListener('click', () => loadLeaderboard().catch(console.error));
    loadLeaderboard().catch(console.error);
}

function initTrainingPage() {
    const form = document.getElementById('training-form');
    const out = document.getElementById('training-out');
    if (!form || !out) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const { data } = await axios.post('/api/lab/training/submit', {
            epochs: parseInt(fd.get('epochs'), 10),
            hidden_size: parseInt(fd.get('hidden_size'), 10),
            lr: parseFloat(fd.get('lr')),
        });
        out.textContent = JSON.stringify(data, null, 2);
        out.classList.remove('hidden');
    });
}

let gameAnim = null;
let gameBodies = null;

function initGamePage() {
    const canvas = document.getElementById('game-canvas');
    const spawn = document.getElementById('game-spawn');
    const freeze = document.getElementById('game-freeze');
    const status = document.getElementById('game-status');
    if (!canvas || !spawn || !freeze) return;
    const ctx = canvas.getContext('2d');

    spawn.addEventListener('click', () => {
        if (gameAnim) cancelAnimationFrame(gameAnim);
        gameBodies = {
            m1: 1,
            m2: 1.5,
            r1: 0.15,
            r2: 0.15,
            p1: { x: -0.8, y: 0.1 },
            p2: { x: 0.6, y: -0.05 },
            v1: { x: 2 + Math.random(), y: (Math.random() - 0.5) * 0.5 },
            v2: { x: -1.5 - Math.random(), y: (Math.random() - 0.5) * 0.5 },
            collision_type: 'elastic',
            material: 'steel',
        };
        let t = 0;
        const loop = () => {
            t += 0.016;
            const scale = 90;
            const ox = canvas.width / 2;
            const oy = canvas.height / 2;
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            const p1 = {
                x: gameBodies.p1.x + gameBodies.v1.x * t * 0.15,
                y: gameBodies.p1.y + gameBodies.v1.y * t * 0.15,
            };
            const p2 = {
                x: gameBodies.p2.x + gameBodies.v2.x * t * 0.15,
                y: gameBodies.p2.y + gameBodies.v2.y * t * 0.15,
            };
            const c1 = { x: ox + p1.x * scale, y: oy - p1.y * scale };
            const c2 = { x: ox + p2.x * scale, y: oy - p2.y * scale };
            ctx.fillStyle = '#a5b4fc';
            ctx.beginPath();
            ctx.arc(c1.x, c1.y, gameBodies.r1 * scale, 0, Math.PI * 2);
            ctx.fill();
            ctx.fillStyle = '#fdba74';
            ctx.beginPath();
            ctx.arc(c2.x, c2.y, gameBodies.r2 * scale, 0, Math.PI * 2);
            ctx.fill();
            gameAnim = requestAnimationFrame(loop);
        };
        loop();
        status.textContent = 'Animating… click Freeze to snapshot & POST.';
    });

    freeze.addEventListener('click', async () => {
        if (gameAnim) cancelAnimationFrame(gameAnim);
        if (!gameBodies) {
            status.textContent = 'Spawn first.';
            return;
        }
        status.textContent = 'Calling API…';
        const payload = {
            ...gameBodies,
            p1: { ...gameBodies.p1 },
            p2: { ...gameBodies.p2 },
            v1: { ...gameBodies.v1 },
            v2: { ...gameBodies.v2 },
            mode: 'manual',
        };
        try {
            const { data } = await axios.post('/api/lab/experiments', payload);
            status.textContent = `Experiment #${data.id} — MAE ${data.comparison?.velocity_mae}`;
        } catch (err) {
            status.textContent = err.response?.data?.message || err.message;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLabPage();
    initAnalysisPage();
    initLeaderboardPage();
    initTrainingPage();
    initVideoPage();
    initGamePage();
});
