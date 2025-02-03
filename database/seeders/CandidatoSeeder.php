<?php

namespace Database\Seeders;

use App\Models\Candidato;
use Illuminate\Database\Seeder;

class CandidatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Candidato $candidato)
    {
        $candidato::insert([
            [
                'id' => 1,
                'nombres' => 'EN BLANCO',
                'dni' => 00000000,
                'temporada_id' => 1
            ],
            [
                'id' => 2,
                'nombres' => 'NULO',
                'dni' => 00000000,
                'temporada_id' => 1
            ]
        ]);
    }
}
