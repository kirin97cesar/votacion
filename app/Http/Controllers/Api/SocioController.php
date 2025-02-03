<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Socio;
use App\Models\Voto;
use Illuminate\Http\Request;
use Exception;

class SocioController extends Controller
{

    public function index()
    {
        return Socio::all();
    }


    public function store(Request $request)
    {
        try {

            $buscarSocio = Socio::where('codigo', $request->codigo)->get();
            if (count($buscarSocio) > 0) return response()->json(['status' => 'error', 'message' => 'Ya se registró este código'], 500);
            $socio = new Socio();
            $socio->nombres = $request->nombres;
            $socio->codigo = $request->codigo;
            $socio->save();
            return response()->json(['status' => 'success', 'message' => 'Se registró este socio'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }


    public function show($id)
    {
        try {
            $socio = Socio::where('id', $id)->get();
            return $socio;
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }

    public function buscador(Request $request)
    {
        try {
            $socios = Socio::orWhere('nombres', 'LIKE', '%' . $request->buscar . '%')
                ->orWhere('codigo', 'LIKE', '%' . $request->buscar . '%')
                ->get();
            return $socios;
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $socio = Socio::find($id);
            $socio->nombres = $request->nombres;
            $socio->codigo = $request->codigo;
            $socio->save();
            return response()->json(['status' => 'success', 'message' => 'Se actualizó el socio'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $socios = Voto::where('socio_id', $id)->get();
            if (count($socios) > 0) {
                foreach ($socios as $socio) {
                    Voto::find($socio['id'])->delete();
                }
            }
            Socio::find($id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Se eliminó este socio'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }
}
