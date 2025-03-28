import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';

// Function to generate and download PDF from report card element
export const generatePDF = async () => {
  const reportElement = document.getElementById('report-card');
  if (!reportElement) return;

  try {
    // Create a deep clone of the report card for PDF generation
    const reportClone = reportElement.cloneNode(true);

    // Force all images to load properly with crossOrigin attributes
    const allImages = reportClone.querySelectorAll('img');
    allImages.forEach(img => {
    //   img.setAttribute('crossOrigin', 'anonymous');

      // Set proper error handling for images
      img.onerror = function() {
        this.onerror = null;
        if (this.src.includes('schoolcompasse.s3.us-east-1.amazonaws.com')) {
          // Try direct URL if S3 URL fails

          const fileName = this.src.split('/').pop();
          console.log(fileName);
          this.src = `https://schoolcompasse.s3.us-east-1.amazonaws.com/${fileName}`;
        } else {
          // Use placeholder if all attempts fail
          this.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjwvc3ZnPg==";
        }
      };

      // For Amazon S3 URLs, ensure they're properly loaded
      if (img.src && img.src.includes('schoolcompasse.s3.us-east-1.amazonaws.com')) {
        // Make a copy of the current source
        const currentSrc = img.src;
        // Set crossOrigin before setting src to avoid CORS issues
        // img.setAttribute('crossOrigin', 'anonymous');
        img.src = currentSrc;
      }
    });

    // Enhance visibility for PDF conversion
    const enforceVisibility = (element) => {
      if (element.style) {
        // Force full opacity and solid colors
        element.style.opacity = '1';
        element.style.color = element.style.color || '#000000';

        // Make backgrounds solid
        if (element.style.backgroundColor && element.style.backgroundColor.includes('rgba')) {
          element.style.backgroundColor = element.style.backgroundColor.replace(/rgba?\(([^,]+),([^,]+),([^,]+),?[^)]*\)/, 'rgb($1,$2,$3)');
        }

        // Increase contrast
        if (element.tagName === 'TD' || element.tagName === 'TH' ||
            element.tagName === 'P' || element.tagName === 'DIV' ||
            element.tagName === 'SPAN' || element.tagName === 'H1' ||
            element.tagName === 'H2' || element.tagName === 'H3' ||
            element.tagName === 'H4' || element.tagName === 'H5' ||
            element.tagName === 'STRONG' || element.tagName === 'B') {
          element.style.color = '#000000';
          element.style.fontWeight = element.style.fontWeight || '500';
        }

        // Ensure table borders are solid and content is centered
        if (element.tagName === 'TABLE') {
          element.style.borderCollapse = 'collapse';
          element.style.border = '2px solid #000';
          element.style.width = '100%';
          element.style.marginBottom = '20px';
        }

        if (element.tagName === 'TH') {
          element.style.backgroundColor = '#e6f2ff';
          element.style.color = '#000000';
          element.style.fontWeight = 'bold';
          element.style.border = '1px solid #000';
          element.style.textAlign = 'center';
          element.style.padding = '8px';
        }

        if (element.tagName === 'TD') {
          element.style.border = '1px solid #000';
          element.style.textAlign = 'center';
          element.style.padding = '8px';
        }

        // Style psychomotor tables differently from affective domain tables
        if (element.tagName === 'TABLE') {
          const tableTitle = element.previousElementSibling?.textContent || '';
          if (tableTitle.includes('Psychomotor Domain')) {
            element.style.backgroundColor = '#f8f9fa';
            element.style.borderColor = '#2c5282';
            element.querySelectorAll('th').forEach(th => {
              th.style.backgroundColor = '#2c5282';
              th.style.color = '#ffffff';
            });
          } else if (tableTitle.includes('Affective Domain')) {
            element.style.backgroundColor = '#fff5f5';
            element.style.borderColor = '#c53030';
            element.querySelectorAll('th').forEach(th => {
              th.style.backgroundColor = '#c53030';
              th.style.color = '#ffffff';
            });
          }
        }
      }

      // Process child elements
      if (element.childNodes) {
        Array.from(element.childNodes).forEach(child => {
          if (child.nodeType === 1) { // Element node
            enforceVisibility(child);
          }
        });
      }
    };

    // Create a temporary container to work with the clone
    const tempContainer = document.createElement('div');
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.style.width = reportElement.offsetWidth + 'px';
    tempContainer.appendChild(reportClone);
    document.body.appendChild(tempContainer);

    // Apply visibility enforcement
    enforceVisibility(reportClone);

    // Show watermark for PDF (already in print-only class, but enforce visibility)
    // const watermark = reportClone.querySelector('.watermark');
    // if (watermark) {
    //   watermark.style.display = 'flex';
    //   watermark.style.opacity = '0.05';
    // }

    // Force image loading before canvas generation by creating promises for each image
    const images = reportClone.querySelectorAll('img');
    const imagePromises = Array.from(images).map(img => {
      // Make sure crossOrigin is set before loading
    //   img.setAttribute('crossOrigin', 'anonymous');

      // For images that are already complete, resolve immediately
      if (img.complete) return Promise.resolve();

      // For images still loading, create a promise that resolves on load or error
      return new Promise(resolve => {
        const originalSrc = img.src;

        img.onload = () => {
          console.log(`Image loaded successfully: ${img.src}`);
          resolve();
        };

        img.onerror = () => {
          console.error(`Failed to load image: ${originalSrc}, trying direct URL`);
          // If loading from S3 fails, try a direct URL approach
          if (originalSrc.includes('schoolcompasse.s3.us-east-1.amazonaws.com')) {
            const fileName = originalSrc.split('/').pop();
            img.src = `https://schoolcompasse.s3.us-east-1.amazonaws.com/${fileName}`;
            // Resolve regardless - we'll use a placeholder if this also fails
            setTimeout(resolve, 500);
          } else {
            // Use placeholder and resolve
            img.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjwvc3ZnPg==";
            resolve();
          }
        };

        // Trigger loading if needed by resetting the src
        if (!img.complete) {
          const currentSrc = img.src;
          img.src = currentSrc;
        }
      });
    });

    await Promise.all(imagePromises);

    // Generate high-quality canvas
    const canvas = await html2canvas(reportClone, {
      scale: 5, // Higher scale for better quality
      useCORS: true,
      allowTaint: true,
      logging: false,
      backgroundColor: '#ffffff',
      onclone: (clonedDoc, element) => {
        // Ensure watermark is visible in PDF
        // const watermark = element.querySelector('.watermark');
        // if (watermark) {
        //   watermark.style.display = 'flex';
        //   watermark.style.opacity = '0.05';
        // }

        // Ensure all table cells are properly centered
        const tableCells = element.querySelectorAll('td, th');
        tableCells.forEach(cell => {
            cell.style.paddingBottom= "0.25rem";
            cell.style.paddingTop = "0.25rem";
          cell.style.textAlign = 'center';
        });

        // Left-align first column cells (subject names)
        const subjectCells = element.querySelectorAll('tr td');
        subjectCells.forEach(cell => {
            cell.style.paddingBottom= "0.25rem";
            cell.style.paddingTop= "0.25rem";
          cell.style.textAlign = 'center';
        });
      }
    });

    // Determine orientation based on content dimensions
    const orientation = canvas.width / canvas.height > 1 ? 'landscape' : 'portrait';

    // Create PDF with high-quality settings
    const pdf = new jsPDF({
      orientation: orientation,
      unit: 'mm',
      format: 'a4',
      compress: false // Better quality without compression
    });

    // Get dimensions based on orientation
    const pdfWidth = orientation === 'landscape' ? 297 : 210; // A4 width in mm
    const pdfHeight = orientation === 'landscape' ? 210 : 297; // A4 height in mm

    // Calculate image dimensions to fit the page with proper margins
    const imgWidth = pdfWidth - 20; // 10mm margins on each side
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    // Add image to PDF
    pdf.addImage(
      canvas.toDataURL('image/png', 1.0), // Maximum quality
      'PNG',
      10, // Left margin
      10, // Top margin
      imgWidth,
      imgHeight,
      undefined,
      'FAST'
    );

    // If content needs multiple pages
    if (imgHeight > pdfHeight - 20) {
      let heightLeft = imgHeight;
      let position = 10;

      // First page is already added above
      heightLeft -= (pdfHeight - 20);

      // Add additional pages as needed
      while (heightLeft > 0) {
        position = -(pdfHeight - 20 - position);
        pdf.addPage();

        pdf.addImage(
          canvas.toDataURL('image/png', 1.0),
          'PNG',
          10,
          position,
          imgWidth,
          imgHeight,
          undefined,
          'FAST'
        );

        heightLeft -= (pdfHeight - 20);
      }
    }

    // Generate filename with student name if available
    const studentName = document.querySelector('#student-name')?.textContent.trim().replace(/\s+/g, '_') || 'Student';
    const timestamp = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    const filename = `${studentName}_Report_${timestamp}.pdf`;

    // Save the PDF
    pdf.save(filename);

    // Clean up - remove temp container
    if (tempContainer && document.body.contains(tempContainer)) {
      document.body.removeChild(tempContainer);
    }

    return true; // Return success
  } catch (error) {
    console.error('Error generating PDF:', error);

    // Clean up in case of error
    const tempContainer = document.querySelector('div[style*="-9999px"]');
    if (tempContainer && document.body.contains(tempContainer)) {
      document.body.removeChild(tempContainer);
    }

    throw error; // Rethrow to allow caller to handle
  }
};
