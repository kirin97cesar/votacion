<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(User $user)
    {
        $user::create([
            'name' => 'Paulo',
            'email' => 'paulo@ludik.pe',
            'password' => Hash::make('@pau1997'),
            'rol_id' => 1
        ]);
    }
}
