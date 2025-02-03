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
        $user::insert([
            [
                'name' => 'Paulo',
                'email' => 'paulo@admin.pe',
                'password' => Hash::make('@pau1997'),
                'rol_id' => 1
            ],
            [
                'name' => 'COOPAC SAN VIATOR',
                'email' => 'administrador@coopacsanviator.org.pe',
                'password' => Hash::make('coop@c2050sanV1ator'),
                'rol_id' => 1
            ],
            [
                'name' => 'Reporteria',
                'email' => 'resultados@coopacsanviator.org.pe',
                'password' => Hash::make('result@DosC00p@c'),
                'rol_id' => 2
            ]
        ]);
    }
}
