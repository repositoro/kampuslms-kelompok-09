<x-layout title="Masuk — KampusLMS">
    <div class="flex min-h-[75vh] items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <div>
                <h2 class="text-center text-2xl font-bold tracking-tight text-gray-900">
                    Masuk ke KampusLMS
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Gunakan akun terdaftar untuk melanjutkan
                </p>
            </div>

            {{-- Pesan Status (misal: setelah reset password atau logout) --}}
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500 @error('email') border-red-500 @enderror"
                           placeholder="nama@kampuslms.test">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="current-password" 
                           required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500 @error('password') border-red-500 @enderror"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox"
                               class="h-4 w-4 rounded border-gray-300 text-slate-800 focus:ring-slate-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat Saya</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="flex w-full justify-center rounded-md bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>