<?php

namespace App\Http\Controllers\Api;

use App\Models\Candidato;
use App\Models\Voto;
use App\Models\Temporada;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class CandidatoController extends Controller
{
   
    public function index()
    {
        $candidatos = Candidato::select('temporadas.tema AS temporada','candidatos.*')
            ->join('temporadas', 'temporadas.id', '=', 'candidatos.temporada_id')
            ->whereNotIn('candidatos.id',[1,2])
            ->whereNotIn('temporadas.id',[1])
            ->get();

        $temporadas = Temporada::select('id','tema')->whereNotIn('id',[1])->get();

        return [
            'candidatos' => $candidatos,
            'temporadas' => $temporadas
        ];
    }

    public function store(Request $request)
    {
        try{

            if(strlen(trim($request->nombres)) ==0 || $request->nombres == null || $request->nombres == 'null' || $request->nombre == 'undefined')
            {
                return response()->json(['status' => 'error', 'message' => 'Ingresa nombre del candidato' ], 500);
            }
            if(strlen(trim($request->numero)) ==0 || $request->numero == null || $request->numero == 'null' || $request->numero == 'undefined')
            {
                return response()->json(['status' => 'error', 'message' => 'Ingresa el número del candidato' ], 500);
            }

            if(strlen(trim($request->dni)) < 8 || $request->dni == null || $request->dni == 'undefined')
            {
                return response()->json(['status' => 'error', 'message' => 'Ingresa un dni válido' ], 500);
            }
            
            if(strlen(trim($request->codigo)) == 0 || $request->codigo == null || $request->codigo == 'undefined')
            {
                return response()->json(['status' => 'error', 'message' => 'Ingresa el código del candidato' ], 500);
            }

            $bCandi = Candidato::where('dni', $request->dni)
                        ->where('temporada_id', $request->temporada_id)
                        ->get();

            $buscarNumero = Candidato::where('numero', $request->numero)
                        ->where('temporada_id', $request->temporada_id)
                        ->get();

            if(count($bCandi) > 0) 
            {
                return response()->json(['status' => 'error', 'message' => 'Ya se registró este candidato' ], 500);
            }

            if(count($buscarNumero) > 0) 
            {
                return response()->json(['status' => 'error', 'message' => 'Ya se registró este número' ], 500);
            }

            $candidato = new Candidato();
            $candidato->nombres = $request->nombres;
            $candidato->dni = $request->dni;
            $candidato->numero = $request->numero;
            $candidato->codigo = $request->codigo;
            $candidato->temporada_id = $request->temporada_id;
            $candidato->save();

            return response()->json(['status' => 'success', 'message' => 'Se registró el candidato'], 200);
        }catch(Exception $error)
        {
            return response()->json(['status' => 'error', 'message' => $error ], 500);
        }
    }

    
    public function show($id)
    {
        try{
            $candidato = Candidato::where('id', $id)->get();
            return $candidato;
        }
        catch(Exception $error){
            return response()->json(['status' => 'error', 'message' => $error ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try{
            $candidato = Candidato::find($id);
            $candidato->nombres = $request->nombres;
            $candidato->dni = $request->dni;
            $candidato->numero = $request->numero;
            $candidato->codigo = $request->codigo;
            $candidato->temporada_id = $request->temporada_id;
            $candidato->save();
            return response()->json(['status' => 'success', 'message' => 'Se actualizó el candidato'], 200);
        }catch(Exception $error)
        {
            return response()->json(['status' => 'error', 'message' => $error ], 500);
        }
    }

    public function buscarCandidatos(Request $request)
    {
        try{
            $temporadas = Candidato::orWhere('dni', 'like','%'.$request->buscar.'%')
                        ->orWhere('codigo', 'like','%'.$request->buscar.'%')
                        ->orWhere('temporadas.tema', 'like','%'.$request->buscar.'%')
                        ->orWhere('nombres', 'like','%'.$request->buscar.'%')
                        ->orWhere('numero', 'like','%'.$request->buscar.'%')
                        ->join('temporadas','temporadas.id','candidatos.temporada_id')
                        ->select('temporadas.tema AS temporada','candidatos.*')
                        ->get();
            return $temporadas;
        }catch(Exception $error)
        {
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }
    }
    
    public function destroy($id)
    {
        try{
            $buscarVotos = Voto::where('candidato_id', $id)->get();
            if(count($buscarVotos) > 0)
            {
                foreach($buscarVotos as $voto)
                {
                    Voto::find($voto['id'])->delete();
                }
            }
            Candidato::find($id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Se eliminó a el candidato'], 200);
        }catch(Exception $error)
        {
            return response()->json(['status' => 'error', 'message' => $error ], 500);
        }
    }
}
