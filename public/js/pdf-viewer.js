/**
 * PDF Viewer Wrapper for PDF.js
 * View-only mode - No download functionality
 * 
 * Usage:
 * <div class="pdf-container" data-pdf-url="/path/to/file.pdf">
 *   <div class="pdf-toolbar">
 *     <button class="pdf-prev">◄</button>
 *     <span class="pdf-page-info">
 *       <span class="pdf-page-num">1</span> / <span class="pdf-page-count">-</span>
 *     </span>
 *     <button class="pdf-next">►</button>
 *     <button class="pdf-zoom-in">+</button>
 *     <button class="pdf-zoom-out">-</button>
 *   </div>
 *   <div class="pdf-canvas-wrapper">
 *     <canvas class="pdf-canvas"></canvas>
 *   </div>
 * </div>
 */

// Set worker path
pdfjsLib.GlobalWorkerOptions.workerSrc = '/public/js/pdf.worker.min.js';

class PDFViewer {
    constructor(container) {
        this.container = container;
        this.canvas = container.querySelector('.pdf-canvas');
        this.ctx = this.canvas.getContext('2d');
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.5;

        // Get elements
        this.pageNumElem = container.querySelector('.pdf-page-num');
        this.pageCountElem = container.querySelector('.pdf-page-count');
        this.prevBtn = container.querySelector('.pdf-prev');
        this.nextBtn = container.querySelector('.pdf-next');
        this.zoomInBtn = container.querySelector('.pdf-zoom-in');
        this.zoomOutBtn = container.querySelector('.pdf-zoom-out');

        // Bind events
        this.bindEvents();

        // Load PDF
        const url = container.dataset.pdfUrl;
        if (url) {
            this.loadPDF(url);
        }
    }

    bindEvents() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.onPrevPage());
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.onNextPage());
        }
        if (this.zoomInBtn) {
            this.zoomInBtn.addEventListener('click', () => this.onZoomIn());
        }
        if (this.zoomOutBtn) {
            this.zoomOutBtn.addEventListener('click', () => this.onZoomOut());
        }
    }

    async loadPDF(url) {
        try {
            // Disable right-click on canvas to prevent "Save Image As"
            this.canvas.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            });

            const loadingTask = pdfjsLib.getDocument(url);
            this.pdfDoc = await loadingTask.promise;

            if (this.pageCountElem) {
                this.pageCountElem.textContent = this.pdfDoc.numPages;
            }

            // Initial render
            this.renderPage(this.pageNum);

        } catch (error) {
            console.error('Error loading PDF:', error);
            this.showError('Gagal memuat PDF. Silakan refresh halaman.');
        }
    }

    renderPage(num) {
        this.pageRendering = true;

        this.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({ scale: this.scale });
            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;

            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };

            const renderTask = page.render(renderContext);

            renderTask.promise.then(() => {
                this.pageRendering = false;
                if (this.pageNumPending !== null) {
                    this.renderPage(this.pageNumPending);
                    this.pageNumPending = null;
                }
            });
        });

        // Update page number
        if (this.pageNumElem) {
            this.pageNumElem.textContent = num;
        }

        // Update button states
        this.updateButtons();
    }

    queueRenderPage(num) {
        if (this.pageRendering) {
            this.pageNumPending = num;
        } else {
            this.renderPage(num);
        }
    }

    onPrevPage() {
        if (this.pageNum <= 1) {
            return;
        }
        this.pageNum--;
        this.queueRenderPage(this.pageNum);
    }

    onNextPage() {
        if (this.pageNum >= this.pdfDoc.numPages) {
            return;
        }
        this.pageNum++;
        this.queueRenderPage(this.pageNum);
    }

    onZoomIn() {
        this.scale += 0.25;
        if (this.scale > 3) this.scale = 3;
        this.queueRenderPage(this.pageNum);
    }

    onZoomOut() {
        this.scale -= 0.25;
        if (this.scale < 0.5) this.scale = 0.5;
        this.queueRenderPage(this.pageNum);
    }

    updateButtons() {
        if (this.prevBtn) {
            this.prevBtn.disabled = (this.pageNum <= 1);
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = (this.pageNum >= this.pdfDoc.numPages);
        }
    }

    showError(message) {
        const wrapper = this.container.querySelector('.pdf-canvas-wrapper');
        if (wrapper) {
            wrapper.innerHTML = `
                <div style="color: white; text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p>${message}</p>
                </div>
            `;
        }
    }
}

// Auto-initialize all PDF containers on page load
document.addEventListener('DOMContentLoaded', function () {
    const containers = document.querySelectorAll('.pdf-container[data-pdf-url]');
    containers.forEach(container => {
        new PDFViewer(container);
    });
});

// Function to manually initialize a PDF viewer (for dynamically added content)
function initPDFViewer(container) {
    return new PDFViewer(container);
}

// Export for use in other scripts
window.PDFViewer = PDFViewer;
window.initPDFViewer = initPDFViewer;
