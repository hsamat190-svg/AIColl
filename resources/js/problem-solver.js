const axios = window.axios;
if (!axios) {
    console.warn('problem-solver: axios missing; load app.js first.');
}

function readSolverI18n() {
    const el = document.getElementById('problem-solver-i18n');
    if (!el?.textContent?.trim()) {
        return {};
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return {};
    }
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

function formatContent(text) {
    return escHtml(String(text || '')).replace(/\n/g, '<br>');
}

function initProblemSolver() {
    const ta = document.getElementById('solver-problem');
    const btn = document.getElementById('solver-submit');
    const statusEl = document.getElementById('solver-status');
    const wrap = document.getElementById('solver-result-wrap');
    const body = document.getElementById('solver-result-body');
    const sourceEl = document.getElementById('solver-source');
    const t = readSolverI18n();

    if (!ta || !btn || !wrap || !body) {
        return;
    }

    btn.addEventListener('click', async () => {
        const problem = ta.value.trim();
        if (!problem) {
            statusEl.textContent = t.placeholder || '';
            return;
        }
        btn.disabled = true;
        statusEl.textContent = t.loading || '…';
        body.innerHTML = '';
        sourceEl.textContent = '';
        wrap.classList.add('hidden');

        try {
            const { data } = await axios.post('/api/lab/problems/solve', { problem });
            wrap.classList.remove('hidden');
            const src = data?.source === 'openai' ? t.sourceOpenai || 'OpenAI' : t.sourceLocal || 'Local';
            sourceEl.textContent = src;
            const sections = Array.isArray(data?.sections) ? data.sections : [];
            if (sections.length === 0) {
                body.innerHTML = `<p class="text-violet-800">${formatContent(t.errorGeneric)}</p>`;
            } else {
                body.innerHTML = sections
                    .map((sec) => {
                        const h = sec.heading ? `<h4 class="font-semibold text-violet-950">${escHtml(sec.heading)}</h4>` : '';
                        const c = `<div class="text-violet-900">${formatContent(sec.content)}</div>`;

                        return `<section class="space-y-2">${h}${c}</section>`;
                    })
                    .join('');
            }
            if (data?.ok === false) {
                wrap.classList.add('ring-1', 'ring-amber-200');
            } else {
                wrap.classList.remove('ring-1', 'ring-amber-200');
            }
            statusEl.textContent = '';
        } catch (e) {
            wrap.classList.remove('hidden');
            sourceEl.textContent = '';
            body.innerHTML = `<p class="text-rose-700">${escHtml(t.errorNetwork || 'Request failed')}</p>`;
            statusEl.textContent = '';
        } finally {
            btn.disabled = false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProblemSolver);
} else {
    initProblemSolver();
}
