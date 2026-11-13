<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — DEEN Electronics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-950 text-slate-100">
    <div class="max-w-md w-full space-y-8">
        <!-- Brand Header -->
        <div class="text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-blue-600 items-center justify-center shadow-lg shadow-blue-500/20 mb-4">
                <span class="font-extrabold text-2xl text-white">D</span>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-white">DEEN Electronics</h2>
            <p class="mt-1 text-xs text-slate-400">Enterprise Administration & Security Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8">
            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/40 border border-emerald-800 text-emerald-300 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            @if (isset($errorMessage))
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/40 border border-red-800 text-red-300 text-xs">
                    <div>{{ $errorMessage }}</div>
                </div>
            @elseif ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/40 border border-red-800 text-red-300 text-xs">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form class="space-y-5" method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <!-- Username or Email -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-300 mb-1.5">Username or Email</label>
                    <input id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="admin or user@deenelectronics.com">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="••••••••••••">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 text-slate-400 cursor-pointer">
                        <input id="remember" name="remember" type="checkbox" value="1"
                               class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
                        <span>Remember this device</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-lg shadow-blue-600/30 transition-all">
                        Sign In to Admin Console
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Storefront Link -->
        <div class="text-center text-xs text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">&larr; Return to Storefront</a>
        </div>
    </div>
</body>
</html>
