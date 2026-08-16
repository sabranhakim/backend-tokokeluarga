<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

new class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $throttleKey = strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam $seconds detik.",
            ]);
        }

        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $this->email)->first();

        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator untuk informasi lebih lanjut.',
            ]);
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
};
?>

<div class="min-h-screen bg-[#f8fafc] flex flex-col lg:flex-row">
    <!-- Left: Branding Panel (desktop) -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-[#182f76] via-[#1a41b5] to-[#1f4fd9]">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-32 -right-16 w-[28rem] h-[28rem] bg-white/5 rounded-full"></div>
        <div class="absolute top-1/3 right-0 w-72 h-72 bg-blue-300/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 w-full flex flex-col justify-center px-14 xl:px-20 py-12">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-11 h-11 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-[#1f4fd9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="text-xl font-extrabold tracking-tight text-white">Toko Grosir<span class="text-amber-300"> Kue Keluarga</span></span>
            </div>

            <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-[1.1] tracking-tight">
                Kelola <span class="text-amber-300">Stok</span> dengan Mudah
            </h1>
            <p class="mt-5 max-w-md text-blue-100 text-lg font-light leading-relaxed">
                Sistem informasi inventaris dan distribusi untuk Toko Grosir Kue Keluarga — semua data barang, stok, dan transaksi terkelola dalam satu aplikasi.
            </p>

            <div class="mt-12 max-w-md">
                <img src="{{ asset('images/login_image.jpg') }}" alt="Ilustrasi inventaris toko" class="rounded-2xl shadow-2xl border border-white/10 object-cover w-full aspect-[4/3]" loading="lazy" />
            </div>
        </div>
    </div>

    <!-- Right: Form Panel -->
    <div class="relative flex-1 flex items-center justify-center p-6 md:p-12">
        <!-- Mobile brand -->
        <div class="lg:hidden absolute top-6 left-6 flex items-center gap-2.5">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <span class="text-lg font-extrabold tracking-tight text-slate-800">Toko<span class="text-blue-600">Keluarga</span></span>
        </div>

        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl p-8 md:p-10 shadow-xl shadow-slate-200/60 border border-slate-100">
                <div class="text-center mb-8">
                    <div class="mx-auto mb-5 w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Welcome Back</h2>
                    <p class="text-sm text-slate-500 mt-1">Masuk untuk mengelola stok toko</p>
                </div>

                <form wire:submit.prevent="login" class="space-y-5">
                    <!-- Email Field -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1" for="email">Email</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <path d="M12 12q-1.65 0-2.825-1.175Q8 9.65 8 8q0-1.65 1.175-2.825Q10.35 4 12 4q1.65 0 2.825 1.175Q16 6.35 16 8q0 1.65-1.175 2.825Q13.65 12 12 12Zm-8 8v-2.8q0-.85.438-1.563.437-.712 1.162-1.087 1.55-.775 3.15-1.163Q10.35 13 12 13t3.25.387q1.6.388 3.15 1.163.725.375 1.162 1.087Q20 16.35 20 17.2V20Zm2-2h12v-.8q0-.275-.137-.5-.138-.225-.363-.35-1.35-.675-2.725-1.012Q14.4 15 12 15t-2.775.338q-1.375.337-2.725 1.012-.225.125-.363.35-.137.225-.137.5ZM12 10q.825 0 1.413-.588Q14 8.825 14 8q0-.825-.587-1.413Q12.825 6 12 6q-.825 0-1.412.587Q10 7.175 10 8q0 .825.588 1.412Q11.175 10 12 10Zm0-2Zm0 10Z"/>
                            </svg>
                            <input wire:model="email" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400 text-slate-900" id="email" placeholder="nama@email.com" type="email" required autofocus/>
                        </div>
                        @error('email') <span class="text-red-500 text-xs ml-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-end px-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500" for="password">Password</label>
                        </div>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <path d="M6 22q-.825 0-1.412-.587Q4 20.825 4 20V10q0-.825.588-1.413Q5.175 8 6 8h1V6q0-2.075 1.463-3.538Q9.925 1 12 1t3.538 1.462Q17 3.925 17 6v2h1q.825 0 1.413.587Q20 9.175 20 10v10q0 .825-.587 1.413Q18.825 22 18 22Zm0-2h12V10H6Zm6-3q.825 0 1.413-.587Q14 15.825 14 15q0-.825-.587-1.413Q12.825 13 12 13q-.825 0-1.412.587Q10 14.175 10 15q0 .825.588 1.413Q11.175 17 12 17ZM9 8h6V6q0-1.25-.875-2.125T12 3q-1.25 0-2.125.875T9 6ZM6 20V10v10Z"/>
                            </svg>
                            <input wire:model="password" class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400 text-slate-900" id="password" placeholder="••••••••" type="{{ $showPassword ? 'text' : 'password' }}" required/>

                            <!-- Toggle Password Button -->
                            <button type="button" wire:click="togglePassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                                @if($showPassword)
                                    <!-- Lucide: Eye-Off -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                        <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                        <line x1="2" y1="2" x2="22" y2="22"></line>
                                    </svg>
                                @else
                                    <!-- Lucide: Eye -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                @endif
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs ml-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-3 px-1">
                        <input wire:model="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" id="remember" type="checkbox"/>
                        <label class="text-sm text-slate-600 cursor-pointer select-none" for="remember">Ingat saya</label>
                    </div>

                    <!-- Login Button -->
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 hover:opacity-95 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 group" type="submit">
                        <span>Masuk ke Dashboard</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="group-hover:translate-x-1 transition-transform">
                            <path d="m12 20-1.425-1.4 5.6-5.6H4v-2h12.175l-5.6-5.6L12 4l8 8Z"/>
                        </svg>
                    </button>
                </form>

                <!-- Bottom Note -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col items-center gap-3">
                    <p class="text-xs text-slate-400 text-center">
                        Hanya untuk personal yang berwenang. Hubungi administrator untuk akses.
                    </p>
                    <div class="flex gap-4">
                        <span class="text-[0.65rem] text-slate-400 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" height="14" viewBox="0 0 24 24" width="14" fill="currentColor">
                                <path d="M12 22q-3.475-.875-5.738-3.988Q4 14.9 4 11.1V5l8-3 8 3v6.1q0 3.8-2.262 6.912Q15.475 21.125 12 22Zm0-2.1q2.525-.775 4.263-3.212Q18 14.25 18 11.1V6.375l-6-2.25-6 2.25V11.1q0 3.15 1.738 5.588Q9.475 19.125 12 19.9Zm-1.1-4.8 4.25-4.25-1.4-1.4-2.85 2.85-1.45-1.45-1.4 1.4ZM12 12.05Z"/>
                            </svg>
                            Secure
                        </span>
                        <span class="text-[0.65rem] text-slate-400 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" height="14" viewBox="0 0 24 24" width="14" fill="currentColor">
                                <path d="M12 21q-3.45 0-6.012-2.287Q3.425 16.425 3.05 13h2.025q.35 2.6 2.313 4.3Q9.35 19 12 19q2.925 0 4.962-2.037Q19 14.925 19 12t-2.038-4.962Q14.925 5 12 5q-1.725 0-3.225.8T6.25 8H9v2H3V4h2v2.35q1.275-1.6 3.112-2.475Q10 3 12 3q1.875 0 3.513.712 1.637.713 2.85 1.926 1.212 1.212 1.925 2.85Q21 10.125 21 12t-.712 3.513q-.713 1.637-1.926 2.85-1.212 1.212-2.85 1.925Q13.875 21 12 21Zm1-5h-2v-5l4.25 2.525-1 1.725-3.25-1.9Z"/>
                            </svg>
                            v2.4.0
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
