<?php

namespace App\Providers;

use App\Models\Dosen;
use App\Models\Lowongan;
use App\Models\Pengumuman;
use App\Observers\DosenObserver;
use App\Observers\LowonganObserver;
use App\Observers\PengumumanObeserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pengumuman::observe(PengumumanObeserver::class);
        Lowongan::observe(LowonganObserver::class);
        Dosen::observe(DosenObserver::class);
    }
}
