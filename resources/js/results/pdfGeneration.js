async function downloadPdf() {
    try {
        setLoading(true);
        setError(null);

        // Get the current URL
        const currentUrl = window.location.href;
        const url = new URL(currentUrl);
        const studentId = url.searchParams.get('studentId');
        const termId = url.searchParams.get('termId');
        const academyId = url.searchParams.get('academyId');

        if (!studentId || !termId || !academyId) {
            throw new Error('Missing required parameters');
        }

        // Fetch the PDF data
        const response = await fetch(`/api/generate-pdf/${studentId}/${termId}/${academyId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const pdfUrl = data.pdf_url;

        // Create an iframe to display the PDF
        const iframe = document.createElement('iframe');
        iframe.src = pdfUrl;
        iframe.style.width = '100%';
        iframe.style.height = '800px';
        iframe.style.border = 'none';

        // Clear previous content and append the iframe
        const container = document.getElementById('pdf-container');
        container.innerHTML = '';
        container.appendChild(iframe);

        setLoading(false);
    } catch (error) {
        console.error('Error loading PDF:', error);
        setError('Failed to load PDF. Please try again.');
        setLoading(false);
    }
}
