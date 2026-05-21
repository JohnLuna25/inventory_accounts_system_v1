<?php
// sa_PrintExport.php
// Shared PDF export and print functions
?>
<script>
    /**
     * Export the entire report as a downloadable PDF using html2canvas + jsPDF.
     * Requires html2canvas and jsPDF libraries to be loaded in the main page.
     */
    async function exportPDF() {
        if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
            alert('PDF libraries not loaded. Please refresh the page.');
            return;
        }

        // Get the button that triggered the event (to restore its text later)
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';
        btn.disabled = true;

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = 190;
            const pageHeight = 277;
            const margin = 10;

            // Clone the report container to avoid modifying the live page
            const container = document.querySelector('.report-container');
            const clone = container.cloneNode(true);
            // Remove the filter bar from the clone (no need in PDF)
            const filterBar = clone.querySelector('.filter-bar');
            if (filterBar) filterBar.remove();

            /**
             * Renders a DOM element into the PDF, splitting it into multiple pages if needed.
             * @param {HTMLElement} element - The element to render
             * @param {boolean} startNewPage - Whether to start on a new page
             */
            async function renderElement(element, startNewPage = false) {
                if (!element) return;
                if (startNewPage && pdf.internal.getNumberOfPages() > 0) pdf.addPage();

                const wrapper = document.createElement('div');
                wrapper.style.cssText = `width:1100px; background:white; padding:20px 30px;`;
                wrapper.appendChild(element.cloneNode(true));
                document.body.appendChild(wrapper);
                await new Promise(r => setTimeout(r, 100));

                const canvas = await html2canvas(wrapper, { scale: 2.5, backgroundColor: '#ffffff', logging: false });
                document.body.removeChild(wrapper);

                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const imgWidth = pageWidth;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                let remaining = imgHeight;
                let yOffset = 0;
                while (remaining > 0) {
                    if (yOffset > 0) pdf.addPage();
                    const sliceH = Math.min(remaining, pageHeight);
                    const srcY = (yOffset / imgHeight) * canvas.height;
                    const srcH = (sliceH / imgHeight) * canvas.height;
                    const sliceCanvas = document.createElement('canvas');
                    sliceCanvas.width = canvas.width;
                    sliceCanvas.height = srcH;
                    const ctx = sliceCanvas.getContext('2d');
                    ctx.drawImage(canvas, 0, srcY, canvas.width, srcH, 0, 0, canvas.width, srcH);
                    pdf.addImage(sliceCanvas.toDataURL('image/jpeg', 0.95), 'JPEG', margin, margin, imgWidth, sliceH);
                    remaining -= sliceH;
                    yOffset += sliceH;
                }
            }

            // Extract header and each section (title + following table)
            const header = clone.querySelector('.report-header');
            const allSections = clone.querySelectorAll('.section-title');
            const elements = [];
            if (header) elements.push(header);

            for (let i = 0; i < allSections.length; i++) {
                const section = allSections[i];
                let table = section.nextElementSibling;
                while (table && table.tagName !== 'TABLE') table = table.nextElementSibling;
                if (section && table) {
                    const wrapper = document.createElement('div');
                    wrapper.appendChild(section.cloneNode(true));
                    wrapper.appendChild(table.cloneNode(true));
                    elements.push(wrapper);
                }
            }

            // Render each element (header first, then sections, each on a new page after the first)
            for (let i = 0; i < elements.length; i++) {
                await renderElement(elements[i], i > 0);
            }

            // Save PDF with a readable filename
            const today = new Date();
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const fileName = `Sales_Analytics_${months[today.getMonth()]}_${today.getDate()}_${today.getFullYear()}.pdf`;
            pdf.save(fileName);

        } catch (err) {
            console.error('PDF generation error:', err);
            alert('Failed to generate PDF: ' + err.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    /**
     * Print the current page using the browser's print dialog.
     * This respects CSS @media print rules.
     */
    async function printSection(sectionId) {
        // Find the section container (title + table)
        const sectionTitle = document.querySelector(`#${sectionId}-print`).closest('.section-title');
        if (!sectionTitle) {
            alert('Section not found.');
            return;
        }
        let table = sectionTitle.nextElementSibling;
        while (table && table.tagName !== 'TABLE') {
            table = table.nextElementSibling;
        }
        if (!table) {
            alert('Table not found for this section.');
            return;
        }

        // Clone and clean
        const titleClone = sectionTitle.cloneNode(true);
        const tableClone = table.cloneNode(true);
        const buttonsDiv = titleClone.querySelector('.section-buttons');
        if (buttonsDiv) buttonsDiv.remove();

        // Wrapper for capture
        const wrapper = document.createElement('div');
        wrapper.style.cssText = `
        position: absolute;
        top: -9999px;
        left: 0;
        width: 1000px;
        background: white;
        padding: 20px;
        font-family: 'Poppins', 'Nunito', Arial, sans-serif;
    `;
        wrapper.appendChild(titleClone);
        wrapper.appendChild(tableClone);
        document.body.appendChild(wrapper);

        // Capture the wrapper
        let canvas;
        try {
            await new Promise(r => setTimeout(r, 200)); // allow rendering
            canvas = await html2canvas(wrapper, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false
            });
        } catch (err) {
            document.body.removeChild(wrapper);
            alert('Failed to capture section: ' + err.message);
            return;
        }
        document.body.removeChild(wrapper);

        // Create an iframe to print the image
        const imgData = canvas.toDataURL('image/png');
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const iframeDoc = iframe.contentWindow.document;
        iframeDoc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Section</title>
            <style>
                body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; background: white; }
                img { max-width: 100%; height: auto; display: block; }
                @media print { body { margin: 0; padding: 0; } img { width: 100%; } }
            </style>
        </head>
        <body>
            <img src="${imgData}" id="printImg">
        </body>
        </html>
    `);
        iframeDoc.close();

        // Wait for image to load then print
        const img = iframeDoc.getElementById('printImg');
        if (img.complete) {
            iframe.contentWindow.print();
        } else {
            img.onload = () => iframe.contentWindow.print();
        }

        // Clean up after printing
        const printTimer = setTimeout(() => {
            document.body.removeChild(iframe);
        }, 5000);
        iframe.contentWindow.onafterprint = () => {
            clearTimeout(printTimer);
            document.body.removeChild(iframe);
        };
    }
</script>