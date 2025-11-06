<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class AppController
{
    protected function allowedAction($permission)
    {
        if (Gate::denies('authorization', $permission)) {
            throw new AuthorizationException('This action is unauthorized');
        }
    }
}
