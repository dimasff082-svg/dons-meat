<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Internal — DONS MEAT</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-red-950 to-slate-900 text-slate-800">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border border-red-900/10">
        
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-red-600 to-red-900 text-white font-extrabold text-2xl shadow-md mb-4">
                D
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">DONS MEAT</h1>
            <p class="mt-2 text-sm text-slate-600">
                Sistem Informasi Penjualan Daging — Panel Internal
            </p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form -->
        <form class="mt-8 space-y-6" method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                        Email Akun
                    </label>
                    <input id="email" 
                           name="email" 
                           type="text" 
                           autocomplete="username" 
                           required 
                           value="{{ old('email') }}"
                           class="appearance-none block w-full px-3.5 py-2.5 border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 text-sm transition"
                           placeholder="nama@donsmeat.local">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">
                        Password
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="current-password" 
                           required 
                           class="appearance-none block w-full px-3.5 py-2.5 border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 text-sm transition"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" 
                           name="remember" 
                           type="checkbox" 
                           class="h-4 w-4 text-red-700 focus:ring-red-600 border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-xs sm:text-sm text-slate-600">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" 
                        id="loginSubmitBtn"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-gradient-to-r from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-700 transition">
                    Masuk ke Panel Internal
                </button>
            </div>

            <p class="text-center text-xs text-slate-400 mt-4">
                Khusus Pemilik, Admin, dan Kasir DONS MEAT. Tidak memerlukan pendaftaran untuk pelanggan.
            </p>
        </form>

    </div>
</body>
</html>
