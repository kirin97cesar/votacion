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
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                    'porcentaje' => round((100 * (count($total_votos) == 0 ? 0 : $total_votos[0]['total']) / ((count($cantidadVotos) > 0) ? $cantidadVotos[0]['total'] : 1)),2),
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
                Log::info('Datos del objeto', json_decode(json_encode($total_votos), true));
                $total_votos = $total_votos ?  $total_votos : [];
                array_push($arrayPuntajesCandidato, [
                    'candidato' => $candidato,
                    'total_votos' => count($total_votos) > 0 ? $total_votos[0]['total'] : 0,
                    'porcentaje' => round((100 * (count($total_votos) > 0 ? $total_votos[0]['total'] : 0) / ((count($cantidadVotos) > 0) ? $cantidadVotos[0]['total'] : 1)),2),
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
            'cantidadVotos' => (count($cantidadVotos) > 0) ? $cantidadVotos[0]['total'] : 0,
            'cantidadNoVotaron' => $cantidadSocios - ((count($cantidadVotos) > 0) ? $cantidadVotos[0]['total'] : 0),
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

    public function downloadPdf(Request $request){
        $ldate = date('Y-m-d-H-i-s');
        $image = $request->imagen;
        $imageInfo = explode(";base64,", $image);     
        $image = str_replace(' ', '+', $imageInfo[1]);
        $imageName = "votacion_resultados".$ldate.".png";
        Storage::disk('public')->put($imageName, base64_decode($image));
        $ldate_pdf = date('Y-m-d H:i:s');
        $pdf = app('dompdf.wrapper');
        $html  = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <h1>Resultados de votación</h1>';
        $html .= '<img src="https://votacion.onrender.com/storage/votacion/votacion_resultados'.$ldate.'.png" width="700" height="900"/>';
        $html .= '
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
        <br><br><br>
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
        <ul style="list-style: none; font-size: small; padding-top: 50px; border: 1px solid;">
            <li>_____________________________________</li>
            <li style="padding-top: 20px;"><b>NOMBRES Y APELLIDOS:</b></li>
            <li style="padding-top: 20px;">_____________________________________</li>
            <li style="padding-top: 20px;"><b>DNI:</b></li>
            <li style="padding-bottom: 20px;">&nbsp;</li>
        </ul>
    <hr>
        <p style="text-align: center;"> ©Derechos Reservados - Sistema de Votación Web V.1.0 - Generado '.$ldate_pdf.'</p>
    <hr>';
        $pdf->loadHtml($html);
        return $pdf->download('resultados_'.$ldate.'.pdf');
    }

}
