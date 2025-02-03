<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temporada;
use App\Models\Socio;
use App\Models\Voto;
use App\Models\Candidato;
use Illuminate\Http\Request;
use App\Exports\SociosVotacionExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index($id)
    {   
        if($id == 0)
        {
            $totalSociosF = Socio::all();
            $cantidadSocios = count($totalSociosF);

            $temporadaUltima = Temporada::orderBy('id', 'DESC')
                ->take(1)
                ->get();

            $id= $temporadaUltima[0]['id'];
                
            $cantidadVotos = Voto::where('temporada_id', $temporadaUltima[0]['id'])
                ->groupBy('temporada_id')
                ->select(Voto::raw('count(votos.id) AS total'), 'temporadas.*')
                ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
                ->orderBy('temporadas.fecha_inicio', 'DESC')
                ->take(1)
                ->get();

            $quienesVotaron = Voto::where('temporada_id', $temporadaUltima[0]['id'])
                            ->select('socios.*')
                            ->join('socios','socios.id','votos.socio_id')
                            ->get();

            $obtenerId = [];
            foreach($quienesVotaron as $voto)
            {
                array_push($obtenerId, $voto['id']);
            }

            $quienesNoVotaron = Socio::select('socios.*')
                            ->whereNotIn('socios.id',$obtenerId)
                            ->get();

            $totalCandidatos = Candidato::select(
                'candidatos.*',
                'temporadas.tema',
                'temporadas.fecha_inicio',
                'temporadas.fecha_fin'
            )
                ->where('candidatos.temporada_id', $temporadaUltima[0]['id'])
                ->join('temporadas', 'temporadas.id', 'candidatos.temporada_id')
                ->orderBy('temporadas.fecha_inicio', 'DESC')
                ->get();

            $arrayPuntajesCandidato = [];
            $colores = ['red','green','blue','orange','yellow','purple','pink','gray'];
            $total_votos = 0;
            foreach ($totalCandidatos as $candidato) {
                $total_votos = $this->cantidadVotosPorCandidato($temporadaUltima[0]['id'], $candidato['id']);
                array_push($arrayPuntajesCandidato, [
                    'candidato' => $candidato,
                    'total_votos' => count($total_votos) == 0 ? 0 : $total_votos[0]['total'],
                    'porcentaje' => round((100 * (count($total_votos) == 0 ? 0 : $total_votos[0]['total']) / ((count($cantidadVotos) == 0) ? 0 : $cantidadVotos[0]['total'])),2),
                    'colores' => $colores[random_int(0,(count($colores) - 1))]
                ]);
            }
            $mostrarCandidatos = [];
            $votoC = collect($arrayPuntajesCandidato)->SortByDesc("porcentaje");
            foreach($votoC as $v)
            {
                array_push($mostrarCandidatos, $v);
            }
            $votosNulos = Voto::where('temporada_id', $id)
            ->where('votos.candidato_id',2)
            ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
            ->orderBy('temporadas.fecha_inicio', 'DESC')
            ->get();

            $votosBlanco = Voto::where('temporada_id', $id)
            ->where('votos.candidato_id',1)
            ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
            ->orderBy('temporadas.fecha_inicio', 'DESC')
            ->get();
        }
        else
        {
            $totalSociosF = Socio::all();
            $cantidadSocios = count($totalSociosF);

            $temporadaUltima = Temporada::where('id', $id)
                ->take(1)
                ->get();

            $cantidadVotos = Voto::where('temporada_id', $id)
                ->groupBy('temporada_id')
                ->select(Voto::raw('count(votos.id) AS total'), 'temporadas.*')
                ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
                ->orderBy('temporadas.fecha_inicio', 'DESC')
                ->take(1)
                ->get();

            $votosNulos = Voto::where('temporada_id', $id)
            ->where('votos.candidato_id',2)
            ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
            ->orderBy('temporadas.fecha_inicio', 'DESC')
            ->get();

            $votosBlanco = Voto::where('temporada_id', $id)
            ->where('votos.candidato_id',1)
            ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
            ->orderBy('temporadas.fecha_inicio', 'DESC')
            ->get();

            $quienesVotaron = Voto::where('temporada_id', $id)
                            ->select('socios.*')
                            ->join('socios','socios.id','votos.socio_id')
                            ->get();
            
            $obtenerId = [];
            foreach($quienesVotaron as $voto)
            {
                array_push($obtenerId, $voto['id']);
            }

            $quienesNoVotaron = Socio::select('socios.*')
                            ->whereNotIn('socios.id',$obtenerId)
                            ->get();

            $totalCandidatos = Candidato::select(
                'candidatos.*',
                'temporadas.tema',
                'temporadas.fecha_inicio',
                'temporadas.fecha_fin'
            )
                ->where('candidatos.temporada_id', $id)
                ->join('temporadas', 'temporadas.id', 'candidatos.temporada_id')
                ->orderBy('temporadas.fecha_inicio', 'DESC')
                ->get();

            $arrayPuntajesCandidato = [];
            $colores = ['red','green','blue','orange','yellow','indigo','purple','pink','gray','red','green','blue','orange','yellow','indigo','purple','pink','gray','red','green','blue','orange','yellow','indigo','purple','pink','gray','red','green','blue','orange','yellow','indigo','purple','pink','gray'];
            $total_votos = 0;
            $i = 0;
            foreach ($totalCandidatos as $candidato) {
                $i++;
                $total_votos = $this->cantidadVotosPorCandidato($id, $candidato['id']);
                array_push($arrayPuntajesCandidato, [
                    'candidato' => $candidato,
                    'total_votos' => count($total_votos) == 0 ? 0 : $total_votos[0]['total'],
                    'porcentaje' => round((100 * (count($total_votos) == 0 ? 0 : $total_votos[0]['total']) / ((count($cantidadVotos) == 0) ? 0 : $cantidadVotos[0]['total'])),2),
                    'colores' => $colores[$i]
                ]);
            }
            $mostrarCandidatos = [];
            $votoC = collect($arrayPuntajesCandidato)->SortByDesc("porcentaje");
            foreach($votoC as $v)
            {
                array_push($mostrarCandidatos, $v);
            }
        }
        
        return [
            'cantidadSocios' => $cantidadSocios,
            'cantidadVotos' => (count($cantidadVotos) == 0) ? 0 : $cantidadVotos[0]['total'],
            'cantidadNoVotaron' => $cantidadSocios - ((count($cantidadVotos) == 0) ? 0 : $cantidadVotos[0]['total']),
            'ultimaFechaVotacion' => $temporadaUltima,
            'votos_candidato' => $mostrarCandidatos,
            'socios_Votaron' => $quienesVotaron,
            'socios_No_Votaron' => $quienesNoVotaron,
            'votos_nulos' => count($votosNulos),
            'votos_blanco' => count($votosBlanco),
        ];
    }

    protected function cantidadVotosPorCandidato($temporadaUltima_id, $candidato_id)
    {
        $cantidadVotos = Voto::where('temporada_id', $temporadaUltima_id)
            ->where('candidato_id', $candidato_id)
            ->select(Voto::raw('count(votos.id) AS total'), 'temporadas.*')
            ->join('temporadas', 'temporadas.id', 'votos.temporada_id')
            ->orderBy('temporadas.fecha_inicio', 'DESC')
            ->groupBy('candidato_id')
            ->get();

        return $cantidadVotos;
    }

    public function export() 
    {
        $ldate = date('Y-m-d-H-i-s');
        
        return Excel::download(new SociosVotacionExport, 'Votacion_socios-'.$ldate.'.xlsx');
    }

    public function temporadas(){
        return Temporada::select('id','tema')->whereNotIn('id',[1])->get();
    }

}
