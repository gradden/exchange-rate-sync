<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function createAdmin(): void
    {
        User::updateOrCreate(
            [
                'email' => config('users.default_admin_email')
            ],
            [
                'name' => 'Backpack Admin',
                'password' => Hash::make(config('users.default_admin_password'))
            ]
        );
    }
}
