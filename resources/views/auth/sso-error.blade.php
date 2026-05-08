<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Failed | Raznar Hosting</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 flex flex-col items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-sm border border-zinc-200 dark:border-zinc-800 text-center space-y-6">
        <div class="flex justify-center">
             <div class="w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-950/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
             </div>
        </div>

        <div class="space-y-2">
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Authentication Failed</h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $message ?? 'The SSO request could not be completed.' }}</p>
        </div>

        <div class="pt-4">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-medium text-sm hover:opacity-90 transition-opacity">
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
