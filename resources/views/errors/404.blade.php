<!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Page Not Found - School Management System</title>
     <script src="https://cdn.tailwindcss.com"></script>
     <style>
         @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
         body {
             font-family: 'Poppins', sans-serif;
             background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
         }
         .school-icon {
             width: 80px;
             height: 80px;
             background: #fff;
             border-radius: 50%;
             display: flex;
             align-items: center;
             justify-content: center;
             box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
         }
         .school-icon svg {
             width: 40px;
             height: 40px;
             color: #4f46e5;
         }
         .error-code {
             font-size: 120px;
             font-weight: 700;
             color: #4f46e5;
             line-height: 1;
             text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
         }
     </style>
 </head>
 <body class="min-h-screen flex items-center justify-center p-4">
     <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 text-center">
         <div class="school-icon mx-auto mb-6">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
             </svg>
         </div>
         <div class="error-code mb-4">404</div>
         <h1 class="text-3xl font-bold text-gray-900 mb-4">Page Not Found</h1>
         <p class="text-gray-600 mb-6">Oops! The page you're looking for seems to have taken a field trip. Let's get you back to class!</p>

         <div class="space-y-4">
             <a href="{{ url('/') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition duration-200">
                 Return to Homepage
             </a>

             <div class="mt-4">
                 <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800">
                     Go Back
                 </a>
             </div>
         </div>
     </div>
 </body>
 </html>
