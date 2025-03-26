import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

const Error = () => {
    const [error, setError] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        // Get error message from localStorage
        const errorMessage = localStorage.getItem('errorMessage');
        if (errorMessage) {
            setError(errorMessage);
        }
    }, []);

    const handleGoBack = () => {
        // Clear error message from localStorage
        localStorage.removeItem('errorMessage');
        // Navigate back
        navigate(-1);
    };

    return (
        <div className="fixed inset-0 bg-gray-100 flex items-center justify-center">
            <div className="bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
                <div className="text-center">
                    <div className="mb-4">
                        <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 className="text-xl font-semibold text-gray-900 mb-2">Error</h2>
                    <p className="text-gray-600 mb-6">{error}</p>
                    <button
                        onClick={handleGoBack}
                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Error;
