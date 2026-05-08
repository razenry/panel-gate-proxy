<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authenticating... | Raznar Hosting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.98); }
        }
        .animate-pulse-slow { animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 flex flex-col items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full text-center space-y-8 animate-pulse-slow">
        <div class="flex justify-center">
             <div class="w-16 h-16 rounded-2xl bg-zinc-900 dark:bg-white flex items-center justify-center shadow-xl">
                 <svg class="w-8 h-8 text-white dark:text-zinc-900 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                     <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                 </svg>
             </div>
        </div>

        <div class="space-y-3">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">Securing your session</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Authenticating as <span class="font-medium text-zinc-900 dark:text-zinc-200">{{ $email }}</span>...</p>
        </div>

        <form id="sso-form" action="{{ route('sso.finalize') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
        </form>
    </div>

    <script>
        // Automatic submission after a small delay for UX
        setTimeout(() => {
            document.getElementById('sso-form').submit();
        }, 800);
    </script>
</body>
</html>
