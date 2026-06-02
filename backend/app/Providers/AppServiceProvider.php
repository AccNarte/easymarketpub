<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Policies\ConversationPolicy;
use App\Policies\ListingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Listing::class, ListingPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
    }
}
