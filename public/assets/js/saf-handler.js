/**
 * SAF Handler - Sample Acceptance Form Interactions
 * Version: 2.1 - CORRECTED
 * 
 * FIXED: UTF-8 encoding (proper emojis)
 * FIXED: Better error handling
 * Handles size changes and PDF download
 */

let currentSize = 'half-a4';

/**
 * Change page size/format
 * Updates all form containers to new size
 */
function changePageSize(size) {
    currentSize = size;
    
    // Get all form containers
    const containers = document.querySelectorAll('.form-container');
    
    containers.forEach(container => {
        // Update class
        container.className = 'form-container ' + size;
    });
    
    console.log(`📐 Changed format to: ${size}`);
}

/**
 * Download as PDF using html2pdf library
 * Generates single multi-page PDF with all form pages
 */
async function downloadPDF() {
    console.log(`📥 Generating PDF in ${currentSize} format...`);
    
    // Check if html2pdf is loaded
    if (typeof html2pdf === 'undefined') {
        alert('❌ PDF library not loaded. Please refresh the page and try again.');
        console.error('❌ html2pdf library not found');
        return;
    }

    // Get form container
    const formContainer = document.getElementById('formContainer');
    if (!formContainer) {
        alert('❌ Form container not found');
        return;
    }

    // Get AC reference for filename
    const footerCells = document.querySelectorAll('.payment-row td');
    let reportRef = 'SAF';
    if (footerCells.length >= 3) {
        const refText = footerCells[footerCells.length - 1].textContent;
        const match = refText.match(/Test report reference number:\s*([^\s]+)/);
        if (match && match[1]) {
            reportRef = match[1];
        }
    }
    
    const safeFilename = reportRef.replace(/[/\\:*?"<>|]/g, '-');
    const filename = `SAF_${safeFilename}.pdf`;

    // Disable button during generation
    const btnDownload = document.querySelector('.btn-print');
    
    if (btnDownload) {
        btnDownload.disabled = true;
        btnDownload.textContent = '⏳ Generating PDF...';
    }

    try {
        // PDF options based on format
        let opt = {};
        
        if (currentSize === 'half-a4') {
            opt = {
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: false
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'landscape',
                    compress: true
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };
        } else if (currentSize === 'full-a5') {
            opt = {
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: false
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a5', 
                    orientation: 'landscape',
                    compress: true
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };
        } else { // a4-natural
            opt = {
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: false
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait',
                    compress: true
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };
        }

        // Check page count for progress message
        const pageCount = document.querySelectorAll('.form-container').length;
        if (pageCount > 3) {
            console.log(`📄 Generating ${pageCount} pages... This may take 30-60 seconds.`);
        }

        // Generate PDF
        await html2pdf().set(opt).from(formContainer).save();

        console.log(`✅ PDF downloaded: ${filename}`);
        
        // Success message
        if (btnDownload) {
            btnDownload.textContent = '✅ Downloaded!';
            btnDownload.style.background = '#28a745';
            
            setTimeout(() => {
                btnDownload.textContent = '📥 Download as PDF';
                btnDownload.style.background = '';
            }, 3000);
        }

    } catch (error) {
        console.error('❌ PDF generation error:', error);
        alert('❌ Error generating PDF. Please try the browser print function instead (Ctrl+P) and save as PDF.');
        
        if (btnDownload) {
            btnDownload.textContent = '❌ Error - Try Browser Print';
            btnDownload.style.background = '#dc3545';
        }
    } finally {
        // Re-enable button
        if (btnDownload) {
            setTimeout(() => {
                btnDownload.disabled = false;
                if (!btnDownload.textContent.includes('Downloaded') && !btnDownload.textContent.includes('Error')) {
                    btnDownload.textContent = '📥 Download as PDF';
                    btnDownload.style.background = '';
                }
            }, 500);
        }
    }
}

/**
 * Initialize when DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ SAF Handler initialized');
    
    // Set initial format to all containers
    changePageSize('half-a4');

    // Check page count
    const pageCount = document.querySelectorAll('.form-container').length;
    console.log(`📄 SAF loaded with ${pageCount} page${pageCount > 1 ? 's' : ''}`);
    
    if (pageCount > 5) {
        console.warn(`⚠️ Large form (${pageCount} pages). PDF generation may take time.`);
    }
    
    // Check if html2pdf library is loaded
    if (typeof html2pdf === 'undefined') {
        console.error('❌ html2pdf library not loaded! PDF download will not work.');
        console.error('Please ensure /public/assets/libs/html2pdf.bundle.min.js is present.');
        
        const btnDownload = document.querySelector('.btn-print');
        if (btnDownload) {
            btnDownload.disabled = true;
            btnDownload.textContent = '❌ PDF Library Missing';
            btnDownload.title = 'html2pdf.bundle.min.js not found';
        }
    }
});