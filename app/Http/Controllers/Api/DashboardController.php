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
        // 1️⃣ Obtener temporada
        if ($id == 0) {
            $temporada = Temporada::orderByDesc('id')->first();
        } else {
            $temporada = Temporada::where('id', $id)->firstOrFail();
        }

        $id = $temporada->id;

        // 2️⃣ Total socios
        $cantidadSocios = Socio::count();

        // 3️⃣ Total votos de la temporada
        $cantidadVotos = Voto::where('temporada_id', $id)->count();

        // 4️⃣ Votos por candidato (UNA sola query)
        $votosPorCandidato = Voto::selectRaw('candidato_id, COUNT(*) as total')
            ->where('temporada_id', $id)
            ->groupBy('candidato_id')
            ->pluck('total', 'candidato_id');

        // 5️⃣ Obtener candidatos + datos de temporada
        $totalCandidatos = Candidato::select(
                'candidatos.*',
                'temporadas.tema',
                'temporadas.fecha_inicio',
                'temporadas.fecha_fin'
            )
            ->join('temporadas', 'temporadas.id', '=', 'candidatos.temporada_id')
            ->where('candidatos.temporada_id', $id)
            ->orderByDesc('temporadas.fecha_inicio')
            ->get();

        // 6️⃣ Armar array de resultados
        $colores = ['red','green','blue','orange','yellow','indigo','purple','pink','gray'];

        $arrayPuntajesCandidato = [];

        foreach ($totalCandidatos as $index => $candidato) {

            $total_votos = $votosPorCandidato[$candidato->id] ?? 0;

            $arrayPuntajesCandidato[] = [
                'candidato' => $candidato,
                'total_votos' => $total_votos,
                'porcentaje' => $cantidadVotos > 0
                    ? round((100 * $total_votos / $cantidadVotos), 2)
                    : 0,
                'colores' => $colores[$index % count($colores)]
            ];
        }

        $mostrarCandidatos = collect($arrayPuntajesCandidato)
            ->sortByDesc('porcentaje')
            ->values();

        // 7️⃣ IDs de socios que votaron
        $idsVotaron = Voto::where('temporada_id', $id)
            ->pluck('socio_id');

        // 8️⃣ Socios que votaron
        $sociosVotaron = Socio::whereIn('id', $idsVotaron)->get();

        // 9️⃣ Socios que NO votaron
        $sociosNoVotaron = Socio::whereNotIn('id', $idsVotaron)->get();

        // 🔟 Votos especiales
        $votosNulos = Voto::where('temporada_id', $id)
            ->where('candidato_id', 2)
            ->count();

        $votosBlanco = Voto::where('temporada_id', $id)
            ->where('candidato_id', 1)
            ->count();

        return [
            'cantidadSocios' => $cantidadSocios,
            'cantidadVotos' => $cantidadVotos,
            'cantidadNoVotaron' => $cantidadSocios - $cantidadVotos,
            'ultimaFechaVotacion' => $temporada,
            'votos_candidato' => $mostrarCandidatos,
            'socios_Votaron' => $sociosVotaron,
            'socios_No_Votaron' => $sociosNoVotaron,
            'votos_nulos' => $votosNulos,
            'votos_blanco' => $votosBlanco,
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
