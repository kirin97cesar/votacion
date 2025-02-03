<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temporada;
use Exception;
use Illuminate\Http\Request;

class TemporadaController extends Controller
{
    public function index()
    {
        return Temporada::orderBy('fecha_inicio', 'DESC')
        ->whereNotIn('id',[1])
        ->get();
    }

    public function show($id)
    {
        $temporada = Temporada::where('id', $id)->whereNotIn('id',[1])->get();
        return $temporada;
    }


    public function store(Request $request)
    {
        try {
            if (strlen(trim($request->fecha_inicio)) == 0 || $request->fecha_inicio == null || $request->fecha_inicio == 'undefined') {
                return response()->json(['status' => 'error', 'message' => 'Ingresa la fecha de inicio de la votación'], 500);
            }
            if (strlen(trim($request->fecha_fin)) == 0 || $request->fecha_fin == null || $request->fecha_fin == 'undefined') {
                return response()->json(['status' => 'error', 'message' => 'Ingresa la fecha de cierre de la votación'], 500);
            }
            if (strlen(trim($request->tema)) == 0 || $request->tema == null || $request->tema == 'null' || $request->tema == 'undefined') {
                return response()->json(['status' => 'error', 'message' => 'Ingresa el asunto de votación'], 500);
            }
            if($request->fecha_inicio >= $request->fecha_fin)
            {
                return response()->json(['status' => 'error', 'message' => 'La fecha de inicio no puede ser menor que la fecha de fin de la votación!'], 500);
            }

            $hayTemporadas = Temporada::where('fecha_inicio', $request->fecha_inicio)
                ->where('fecha_fin', $request->fecha_fin)
                ->get();
            if (count($hayTemporadas) > 0) {
                return response()->json(['status' => 'error', 'message' => 'Ya esta registrada esta fecha de votación'], 500);
            }
            $temporada = new Temporada();
            $temporada->fecha_inicio = $request->fecha_inicio;
            $temporada->fecha_fin = $request->fecha_fin;
            $temporada->tema = $request->tema;
            $temporada->save();
            return response()->json(['status' => 'success', 'message' => 'Se registró la temporada de votación'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            if($request->fecha_inicio >= $request->fecha_fin)
            {
                return response()->json(['status' => 'error', 'message' => 'La fecha de inicio no puede ser menor que la fecha de fin de la votación!'], 500);
            }
            if (strlen(trim($request->tema)) == 0 || $request->tema == null || $request->tema == 'null' || $request->tema == 'undefined') {
                return response()->json(['status' => 'error', 'message' => 'Ingresa el asunto de votación'], 500);
            }
            $temporada = Temporada::find($id);
            $temporada->fecha_inicio = $request->fecha_inicio;
            $temporada->fecha_fin = $request->fecha_fin;
            $temporada->tema = $request->tema;
            $temporada->save();
            return response()->json(['status' => 'success', 'message' => 'Se actualizó la temporada de votación'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Temporada::find($id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Se eliminó la temporada de votación'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }

    public function buscador(Request $request)
    {
        try{
            $temporadas = Temporada::orWhere('tema', 'like','%'.$request->buscar.'%')
                        ->orWhere('fecha_inicio', 'like','%'.$request->buscar.'%')
                        ->orWhere('fecha_fin', 'like','%'.$request->buscar.'%')
                        ->get();
            return $temporadas;
        }catch(Exception $error)
        {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }
}
