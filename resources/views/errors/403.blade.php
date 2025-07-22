<!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Access Denied - School Management System</title>
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

         <h1 class="text-3xl font-bold text-gray-900 mb-4">Access Denied</h1>
         <p class="text-gray-600 mb-2">You are already logged in to another panel or account on this tenant.</p>
         <p class="text-gray-500 mb-6">To continue, please log out from your previous session. This helps keep your data secure and ensures you are using the correct panel.</p>

         <div class="space-y-4">
             @php
                 $previousPanel = null;
                 $panelLabel = null;
                 $panelMap = [
                     'app' => 'Admin',
                     'teacher' => 'Teacher',
                     'student' => 'Student',
                     'parent' => 'Parent',
                 ];
                 // Try to get panel from session first
                 if (session()->has('filament.panel')) {
                     $previousPanel = session('filament.panel');
                 }
                 // If not in session, try to get from referer URL
                 if (!$previousPanel && request()->hasHeader('referer')) {
                     $referer = request()->header('referer');
                     $refererPath = parse_url($referer, PHP_URL_PATH);
                     $segments = explode('/', trim($refererPath, '/'));
                     if (in_array($segments[0], array_keys($panelMap))) {
                         $previousPanel = $segments[0];
                     }
                 }
                 // If still no panel found, default to admin
                 if (!$previousPanel) {
                     $previousPanel = 'app';
                 }
                 $panelLabel = $panelMap[$previousPanel] ?? ucfirst($previousPanel);
             @endphp

             <div class="mb-2 text-sm text-gray-700">
                 <span class="font-semibold">Current active panel:</span>
                 <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $panelLabel }}</span>
             </div>

             <form method="POST" action="{{ route("filament.{$previousPanel}.auth.logout") }}" class="inline-block">
                 @csrf
                 <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-200">
                     Logout from {{ $panelLabel }} Panel
                 </button>
             </form>

             <div class="my-2 text-gray-500 text-xs">or</div>

             <form method="POST" action="{{ route('logout.everywhere') }}" class="inline-block">
                 @csrf
                 <button type="submit" class="bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-gray-900 transition duration-200">
                     Logout Everywhere (All Panels)
                 </button>
             </form>

             <div class="mt-4 flex flex-col items-center gap-2">
                 <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800 underline">
                     Go Back
                 </a>
                 <button onclick="window.location.reload()" class="text-blue-600 hover:text-blue-800 underline mt-1">
                     Try Again
                 </button>
             </div>
         </div>
         <div class="mt-8 text-xs text-gray-400">If you continue to have issues, try clearing your browser cookies or open a new incognito window.</div>
     </div>
 </body>
 </html>
