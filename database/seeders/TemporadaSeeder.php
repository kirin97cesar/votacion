<?php

namespace Database\Seeders;

use App\Models\Temporada;
use Illuminate\Database\Seeder;

class TemporadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Temporada $temporada)
    {
    $temporada::create([
        'id' => 1,
        'tema' => 'INVALIDO',
        'fecha_inicio' => '1800-01-01 00:00:00',
        'fecha_fin' => '1800-01-01 00:00:00'
    ]);
    }
}
