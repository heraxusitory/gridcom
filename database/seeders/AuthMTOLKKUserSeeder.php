<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthMTOLKKUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::updateOrCreate([
            'name' => 'wsLKK@mail.ru',
            'email' => 'wsLKK@mail.ru',
            'password' => Hash::make(11223344),
        ]);
    }
}
