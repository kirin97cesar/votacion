<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Rol $rol)
    {
        $rol::insert([
            [
                'id' => 1,
                'rol' => 'admin'
            ],
            [
                'id' => 2,
                'rol' => 'reportes'
            ]
        ]);
    }
}
