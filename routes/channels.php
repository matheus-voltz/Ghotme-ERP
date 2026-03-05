<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('company.{id}', function ($user, $id) {
    // Garante que o usuário logado só ouça o canal da sua própria empresa
    return (int) $user->company_id === (int) $id;
});
