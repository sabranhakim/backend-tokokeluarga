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

<div class="min-h-screen flex flex-col md:flex-row overflow-hidden">
    <!-- Top/Header Section (Left Side on Desktop) -->
    <section class="flex-1 bg-surface-50 flex flex-col justify-between p-8 md:p-16 lg:p-24 relative overflow-hidden">
        <!-- Logo -->
        <div class="z-10 flex items-center gap-3">
            <div class="w-20 h-25 bg-primary flex items-center justify-center rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" fill="currentColor" class="text-white">
                    <path d="M4 21V9h16v12Zm2-2h12v-8H6Zm-2-11V3h16v5Zm2-2h12V5H6Zm3 7h6v-2H9Zm0 0v-2h6v2Z"/>
                </svg>
            </div>
            <span class="text-4xl font-extrabold tracking-tight text-on-background">Grosir Toko Keluarga</span>
        </div>
        <!-- Hero Content -->
        <div class="mt-12 md:mt-0 z-10 max-w-md">
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-on-background leading-[1.1] tracking-tighter">
                Digitalizing <span class="text-primary">Sweet</span> Logistics
            </h1>
            <p class="mt-6 text-on-surface-variant text-lg font-light leading-relaxed">
                Streamlining bakery inventory and distribution with artisanal precision. Your warehouse, curated and connected.
            </p>
        </div>
        <!-- Illustration Graphic -->
        <div class="mt-12 md:absolute md:bottom-12 md:right-0 lg:right-[-5%] w-full md:w-[120%] max-w-lg z-0 opacity-90">
            <div class="relative group">
                <div class="absolute -inset-4 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-all duration-700"></div>
                <img alt="Smartphone scanning digital invoice" class="rounded-3xl shadow-2xl transform md:-rotate-6 md:translate-x-12 hover:rotate-0 transition-transform duration-500 border border-white/20" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDA829MyamANl_KynXcic4BVUHpSb--yg1k7f5bta_k87qiERHpQKRzobV79Mt-U8Y18GNxtv7vlB-RWykkv5jHwiu5xf8mcSas_u0W2UmgtrGTiV56R3NpL046t2ktn_LfBZBp0Z5lryjmjrJ2AetwX_-X5touV1WL7C_svOVa84K8-LT3ZJzML4EltOTZnP7EBRw2PEJZPBYq7xUEbcxgbukupaZjgPoIGRUs23HBuP_DlcWD_8VWB2GaxjgAlbW713vNCVdTTpbs"/>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center">
                    <div class="w-24 h-24 rounded-full border-2 border-primary/30 animate-ping absolute"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" height="48" viewBox="0 0 24 24" width="48" fill="currentColor" class="text-primary">
                        <path d="M2 11V4h7v2H4v5Zm13-7h7v7h-2V6h-5ZM4 20v-5h2v5h5v2H2Zm11 2v-2h5v-5h2v7ZM7 17V7h2v10Zm3 0V7h1v10Zm3 0V7h2v10Zm3 0V7h1v10Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Section (Right Side on Desktop) -->
    <section class="flex-1 bg-surface-container-low flex items-center justify-center p-6 md:p-12 relative">
        <!-- Floating Login Card -->
        <div class="w-full max-w-md bg-surface-container-lowest p-10 md:p-12 rounded-[2rem] shadow-[0px_12px_32px_rgba(62,39,35,0.06)] border border-white/50 relative z-10">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-on-background mb-2">Welcome Back</h2>
                <p class="text-on-surface-variant text-sm">Please enter your credentials to manage stock</p>
            </div>

            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Email Field -->
                <div class="space-y-2">
                    <label class="text-[0.7rem] uppercase tracking-widest font-semibold text-on-surface-variant ml-1" for="email">Email</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">
                            <path d="M12 12q-1.65 0-2.825-1.175Q8 9.65 8 8q0-1.65 1.175-2.825Q10.35 4 12 4q1.65 0 2.825 1.175Q16 6.35 16 8q0 1.65-1.175 2.825Q13.65 12 12 12Zm-8 8v-2.8q0-.85.438-1.563.437-.712 1.162-1.087 1.55-.775 3.15-1.163Q10.35 13 12 13t3.25.387q1.6.388 3.15 1.163.725.375 1.162 1.087Q20 16.35 20 17.2V20Zm2-2h12v-.8q0-.275-.137-.5-.138-.225-.363-.35-1.35-.675-2.725-1.012Q14.4 15 12 15t-2.775.338q-1.375.337-2.725 1.012-.225.125-.363.35-.137.225-.137.5ZM12 10q.825 0 1.413-.588Q14 8.825 14 8q0-.825-.587-1.413Q12.825 6 12 6q-.825 0-1.412.587Q10 7.175 10 8q0 .825.588 1.412Q11.175 10 12 10Zm0-2Zm0 10Z"/>
                        </svg>
                        <input wire:model="email" class="w-full pl-12 pr-4 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all placeholder:text-[#3E2723]/40 text-[#3E2723]" id="email" placeholder="Enter your email" type="email" required autofocus/>
                    </div>
                    @error('email') <span class="text-error text-xs ml-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <div class="flex justify-between items-end px-1">
                        <label class="text-[0.7rem] uppercase tracking-widest font-semibold text-on-surface-variant" for="password">Password</label>
                        <a class="text-[0.7rem] font-medium text-primary hover:underline" href="#">Forgot?</a>
                    </div>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">
                            <path d="M6 22q-.825 0-1.412-.587Q4 20.825 4 20V10q0-.825.588-1.413Q5.175 8 6 8h1V6q0-2.075 1.463-3.538Q9.925 1 12 1t3.538 1.462Q17 3.925 17 6v2h1q.825 0 1.413.587Q20 9.175 20 10v10q0 .825-.587 1.413Q18.825 22 18 22Zm0-2h12V10H6Zm6-3q.825 0 1.413-.587Q14 15.825 14 15q0-.825-.587-1.413Q12.825 13 12 13q-.825 0-1.412.587Q10 14.175 10 15q0 .825.588 1.413Q11.175 17 12 17ZM9 8h6V6q0-1.25-.875-2.125T12 3q-1.25 0-2.125.875T9 6ZM6 20V10v10Z"/>
                        </svg>
                        <input wire:model="password" class="w-full pl-12 pr-12 py-4 bg-surface-container-highest rounded-xl border-none focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all placeholder:text-[#3E2723]/40 text-[#3E2723]" id="password" placeholder="••••••••" type="{{ $showPassword ? 'text' : 'password' }}" required/>
                        
                        <!-- Toggle Password Button -->
                        <button type="button" wire:click="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 hover:text-primary transition-colors focus:outline-none">
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
                    @error('password') <span class="text-error text-xs ml-1">{{ $message }}</span> @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-3 px-1">
                    <input wire:model="remember" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" id="remember" type="checkbox"/>
                    <label class="text-sm text-on-surface-variant cursor-pointer select-none" for="remember">Keep me logged in</label>
                </div>

                <!-- Login Button -->
                <button class="w-full burnt-caramel-gradient text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 group" type="submit">
                    <span>Sign In to Dashboard</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" width="20" fill="currentColor" class="group-hover:translate-x-1 transition-transform">
                        <path d="m12 20-1.425-1.4 5.6-5.6H4v-2h12.175l-5.6-5.6L12 4l8 8Z"/>
                    </svg>
                </button>
            </form>

            <!-- Bottom Note -->
            <div class="mt-10 pt-8 border-t border-surface-variant flex flex-col items-center gap-4">
                <p class="text-xs text-on-surface-variant text-center">
                    Authorized Personnel Only. Contact system administrator for access requests.
                </p>
                <div class="flex gap-4">
                    <span class="text-[0.65rem] text-on-surface-variant/40 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" height="14" viewBox="0 0 24 24" width="14" fill="currentColor">
                            <path d="M12 22q-3.475-.875-5.738-3.988Q4 14.9 4 11.1V5l8-3 8 3v6.1q0 3.8-2.262 6.912Q15.475 21.125 12 22Zm0-2.1q2.525-.775 4.263-3.212Q18 14.25 18 11.1V6.375l-6-2.25-6 2.25V11.1q0 3.15 1.738 5.588Q9.475 19.125 12 19.9Zm-1.1-4.8 4.25-4.25-1.4-1.4-2.85 2.85-1.45-1.45-1.4 1.4ZM12 12.05Z"/>
                        </svg>
                        Secure
                    </span>
                    <span class="text-[0.65rem] text-on-surface-variant/40 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" height="14" viewBox="0 0 24 24" width="14" fill="currentColor">
                            <path d="M12 21q-3.45 0-6.012-2.287Q3.425 16.425 3.05 13h2.025q.35 2.6 2.313 4.3Q9.35 19 12 19q2.925 0 4.962-2.037Q19 14.925 19 12t-2.038-4.962Q14.925 5 12 5q-1.725 0-3.225.8T6.25 8H9v2H3V4h2v2.35q1.275-1.6 3.112-2.475Q10 3 12 3q1.875 0 3.513.712 1.637.713 2.85 1.926 1.212 1.212 1.925 2.85Q21 10.125 21 12t-.712 3.513q-.713 1.637-1.926 2.85-1.212 1.212-2.85 1.925Q13.875 21 12 21Zm1-5h-2v-5l4.25 2.525-1 1.725-3.25-1.9Z"/>
                        </svg>
                        v2.4.0
                    </span>
                </div>
            </div>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute top-10 right-10 w-32 h-32 bg-primary-container/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-48 h-48 bg-tertiary-container/10 rounded-full blur-3xl"></div>
    </section>
</div>
