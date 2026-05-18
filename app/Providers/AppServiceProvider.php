<?php

namespace App\Providers;

use App\Models\ArsipSuratKeluar;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Policies\ArsipSuratKeluarPolicy;
use App\Policies\SuratKeluarPolicy;
use App\Policies\SuratMasukPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        SuratKeluar::class => SuratKeluarPolicy::class,
        SuratMasuk::class => SuratMasukPolicy::class,
        ArsipSuratKeluar::class => ArsipSuratKeluarPolicy::class,
    ];
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
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
