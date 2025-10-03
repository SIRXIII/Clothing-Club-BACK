<?php

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user && ((int) $user->id === (int) $id);
});

Broadcast::channel('support.ticket.{ticketId}', function ($user, $ticketId) {
    return true;
});

Broadcast::channel('App.Models.Partner.{id}', fn($partner, $id) => (int) $partner->id === (int) $id);
Broadcast::channel('App.Models.Traveler.{id}', fn($traveler, $id) => (int) $traveler->id === (int) $id);
Broadcast::channel('App.Models.Rider.{id}', fn($rider, $id) => (int) $rider->id === (int) $id);
