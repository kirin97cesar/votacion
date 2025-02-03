<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Socio;
use App\Models\Temporada;
use App\Models\Voto;
use Exception;
use Illuminate\Http\Request;

class VotacionController extends Controller
{

    public function candidatosTemporada(Request $request)
    {
        $candidatos = Candidato::where('temporada_id', $request->temporada_id)->get();
        return $candidatos;
    }

    public function logearVoto(Request $request)
    {
        $socio = Socio::where('codigo', $request->codigo)
            ->where('pass', $request->dni)
            ->get();

        $temporadaActual = Temporada::where('fecha_inicio', '<=', $request->fechaActual)
            ->where('fecha_fin', '>=', $request->fechaActual)
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get();

        if (count($socio) == 0) {
            return response()->json(['status' => 'error', 'message' => 'Credenciales Inválidas'], 404);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => $socio[0],
                'temporada' => (count($temporadaActual) == 0) ? 0 : $temporadaActual[0]
            ], 200);
        }
    }

    public function comprobarTiempo(Request $request)
    {
        $temporadaActual = Temporada::where('fecha_inicio', '<=', $request->fechaActual)
            ->where('fecha_fin', '>=', $request->fechaActual)
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get();

        if (count($temporadaActual) == 0) {
            return response()->json(['status' => 'error', 'message' => 'Se terminó el tiempo para votar'], 400);
        }
        return response()->json(['status' => 'success', 'message' => 'Si hay votación'], 200);
    }

    public function votar(Request $request)
    {
        try {
            $info = $request->info;
            $cantidadLetras = 0;
            $obtenerNuevo = 0;

            for($i = 0; $i<=3; $i++)
            {
                $cantidadLetras = strlen($info) / 2;
                $obtenerNuevo = substr($info, 0, $cantidadLetras);
                $info = base64_decode($obtenerNuevo);
            }

            $request = json_decode($info);

            $temporadaActual = Temporada::where('fecha_inicio', '<=', $request->fechaActual)
                ->where('fecha_fin', '>=', $request->fechaActual)
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get();

            if (count($temporadaActual) == 0) {
                return response()->json(['status' => 'error', 'message' => 'Se terminó el tiempo para votar'], 400);
            }

            $buscarSiVoto = Voto::where('socio_id', $request->socio_id)
                ->where('temporada_id', $temporadaActual[0]['id'])
                ->get();

            if (count($buscarSiVoto) > 0) return response()->json(['status' => 'error', 'message' => 'Ya votó'], 400);

            $buscarNumero = Candidato::where('numero', $request->numero)
                ->where('temporada_id', $temporadaActual[0]['id'])
                ->get();

            if (count($buscarNumero) == 0 && ($request->numero == '' || $request->numero == 'null' || $request->numero == 'undefined' || $request->numero == null)) {
                $voto = new Voto();
                $voto->socio_id = $request->socio_id;
                $voto->candidato_id = 1;
                $voto->temporada_id = $request->temporada_id;
                $voto->save();

                return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
            }

            if (count($buscarNumero) == 0 && ($request->numero >= 0 || $request->numero == '0')) {
                $voto = new Voto();
                $voto->socio_id = $request->socio_id;
                $voto->candidato_id = 2;
                $voto->temporada_id = $request->temporada_id;
                $voto->save();

                return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
            }

            

            $voto = new Voto();
            $voto->socio_id = $request->socio_id;
            $voto->candidato_id = $buscarNumero[0]['id'];
            $voto->temporada_id = $request->temporada_id;
            $voto->save();

            return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }
}
