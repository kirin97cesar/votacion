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

    public function comprobarTiempo()
    {
        $hoy = date("Y-m-d H:i:s");
        $temporadaActual = Temporada::where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get();

        if (count($temporadaActual) == 0) {
            return response()->json(['status' => 'error', 'message' => 'Se terminó el tiempo para votar'], 400);
        }
        return response()->json(['status' => 'success', 'message' => 'Si hay votación'], 200);
    }

    protected function comprobarEstado()
    {
        $hoy = date("Y-m-d H:i:s");
        $temporadaActual = Temporada::where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get();

        if (count($temporadaActual) == 0) {
            return false;
        }
        return true;
    }

    protected function getUserIpAddress() {

        foreach ( [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ] as $key ) {
    
            // Comprobamos si existe la clave solicitada en el array de la variable $_SERVER 
            if ( array_key_exists( $key, $_SERVER ) ) {
    
                // Eliminamos los espacios blancos del inicio y final para cada clave que existe en la variable $_SERVER 
                foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {
    
                    // Filtramos* la variable y retorna el primero que pase el filtro
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }
    
        return '?'; // Retornamos '?' si no hay ninguna IP o no pase el filtro
    } 

    protected function obtenerUbicacion($ip){
        $res = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip), true);
        $pais = $res['geoplugin_countryName'];
        $ciudad = $res['geoplugin_regionName'];
        $ubicacionName = $ciudad. '-' . $pais; 
        return $ubicacionName;
    }

    public function votar(Request $request)
    {
        $estadoVoto = $this->comprobarEstado();
        if(!$estadoVoto){
            return response()->json(['status' => 'error', 'message' => 'Se terminó el tiempo para votar'], 400);
        } 

        $ipClient = $this->getUserIpAddress();
        $ubicacionName = $this->obtenerUbicacion($ipClient);
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
                $voto->ip_client = $ipClient;
                $voto->ubicacion = $ubicacionName;
                $voto->save();

                return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
            }

            if (count($buscarNumero) == 0 && ($request->numero >= 0 || $request->numero == '0')) {
                $voto = new Voto();
                $voto->socio_id = $request->socio_id;
                $voto->candidato_id = 2;
                $voto->temporada_id = $request->temporada_id;
                $voto->ip_client = $ipClient;
                $voto->ubicacion = $ubicacionName;
                $voto->save();

                return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
            }

            

            $voto = new Voto();
            $voto->socio_id = $request->socio_id;
            $voto->candidato_id = $buscarNumero[0]['id'];
            $voto->temporada_id = $request->temporada_id;
            $voto->ip_client = $ipClient;
            $voto->ubicacion = $ubicacionName;
            $voto->save();

            return response()->json(['status' => 'success', 'message' => 'Se registró su voto'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }
}
