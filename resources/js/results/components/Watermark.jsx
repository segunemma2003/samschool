
import React from 'react';

const Watermark = ({ logoUrl, opacity = 0.05 }) => {
  // Default placeholder logo if none is provided
  const defaultLogo = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjkwIiBmaWxsPSIjZjBmMGYwIiBzdHJva2U9IiNjY2MiIHN0cm9rZS13aWR0aD0iMiIvPjx0ZXh0IHg9IjEwMCIgeT0iMTA1IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkxvZ288L3RleHQ+PC9zdmc+";

  // Helper function to properly load images
  const getImageSrc = (imagePath) => {
    if (!imagePath) return defaultLogo;

    if (imagePath.startsWith('data:image')) {
      return imagePath;
    }

    // Handle S3 URLs
    if (imagePath.includes('s3.us-east-1.amazonaws.com')) {
      return imagePath;
    }

    return `https://schoolcompasse.s3.us-east-1.amazonaws.com/${imagePath}`;
  };

  return (
    <div className="watermark print-only" style={{ opacity }}>
      <img
        src={getImageSrc(logoUrl)}
        alt="School Logo Watermark"
        className="watermark-image"
        style={{ opacity: opacity || 0.05 }}
      />
    </div>
  );
};

export default Watermark;