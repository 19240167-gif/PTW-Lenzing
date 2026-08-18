<?php

namespace App\Providers;

use App\Models\WorkPermit;
use App\Policies\WorkPermitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        WorkPermit::class => WorkPermitPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
