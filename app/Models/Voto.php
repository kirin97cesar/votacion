<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voto extends Model
{
    use HasFactory;
    protected $table = 'votos';
    protected $fillable = ['socio_id','candidato_id','temporada_id'];
}
