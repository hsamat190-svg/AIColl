/**
 * PDF экспорт страницы детализации записи истории (/lab/history/{id}).
 */
async function downloadHistoryDetailPdf(root, filename) {
    if (!root) return;
    const html2canvas = (await import('html2canvas')).default;
    const { jsPDF } = await import('jspdf');
    const snap = await html2canvas(root, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        scrollX: 0,
        scrollY: -window.scrollY,
    });
    const imgData = snap.toDataURL('image/png');
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const margin = 36;
    const pageImgH = pageH - 2 * margin;
    const imgW = pageW - 2 * margin;
    const imgH = (snap.height * imgW) / snap.width;

    pdf.addImage(imgData, 'PNG', margin, margin, imgW, imgH);
    let heightLeft = imgH - pageImgH;
    while (heightLeft > 0) {
        const y = margin - (imgH - heightLeft);
        pdf.addPage();
        pdf.addImage(imgData, 'PNG', margin, y, imgW, imgH);
        heightLeft -= pageImgH;
    }
    pdf.save(filename || 'lab-history.pdf');
}

function initHistoryDetailPage() {
    const btn = document.getElementById('history-detail-pdf-btn');
    const root = document.getElementById('history-detail-pdf-root');
    if (!btn || !root) return;
    const name = btn.getAttribute('data-filename') || 'lab-history.pdf';
    btn.addEventListener('click', () => {
        downloadHistoryDetailPdf(root, name).catch((err) => console.error(err));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHistoryDetailPage);
} else {
    initHistoryDetailPage();
}
