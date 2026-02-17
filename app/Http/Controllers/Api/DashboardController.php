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
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index($id)
    {
        // 1️⃣ Obtener temporada
        if ($id == 0) {
            $temporada = Temporada::orderByDesc('id')->firstOrFail();
        } else {
            $temporada = Temporada::findOrFail($id);
        }

        $temporadaId = $temporada->id;

        // 2️⃣ Total socios
        $cantidadSocios = Socio::count();

        // 3️⃣ Traer TODOS los votos de la temporada (UNA SOLA QUERY)
        $votos = Voto::where('temporada_id', $temporadaId)->get();

        $cantidadVotos = $votos->count();

        // 4️⃣ Agrupar votos por candidato
        $votosPorCandidato = $votos
            ->groupBy('candidato_id')
            ->map(function ($grupo) {
                return $grupo->count();
            });

        // 5️⃣ Votos especiales
        $votosBlanco = $votosPorCandidato[1] ?? 0;
        $votosNulos  = $votosPorCandidato[2] ?? 0;

        // 6️⃣ Obtener candidatos de la temporada
        $candidatos = Candidato::where('temporada_id', $temporadaId)
            ->orderByDesc('id')
            ->get();

        $colores = ['red','green','blue','orange','yellow','indigo','purple','pink','gray'];

        $arrayPuntajesCandidato = [];

        foreach ($candidatos as $index => $candidato) {

            $total_votos = $votosPorCandidato[$candidato->id] ?? 0;

            $arrayPuntajesCandidato[] = [
                'candidato'     => $candidato,
                'total_votos'   => $total_votos,
                'porcentaje'    => $cantidadVotos > 0
                    ? round((100 * $total_votos / $cantidadVotos), 2)
                    : 0,
                'colores'       => $colores[$index % count($colores)]
            ];
        }

        $mostrarCandidatos = collect($arrayPuntajesCandidato)
            ->sortByDesc('porcentaje')
            ->values();

        // 7️⃣ Socios que votaron
        $idsVotaron = $votos->pluck('socio_id')->unique();

        $sociosVotaron = Socio::whereIn('id', $idsVotaron)->get();

        // 8️⃣ Socios que NO votaron
        $sociosNoVotaron = Socio::whereNotIn('id', $idsVotaron)->get();

        return [
            'cantidadSocios'      => $cantidadSocios,
            'cantidadVotos'       => $cantidadVotos,
            'cantidadNoVotaron'   => $cantidadSocios - $cantidadVotos,
            'ultimaFechaVotacion' => $temporada,
            'votos_candidato'     => $mostrarCandidatos,
            'socios_Votaron'      => $sociosVotaron,
            'socios_No_Votaron'   => $sociosNoVotaron,
            'votos_nulos'         => $votosNulos,
            'votos_blanco'        => $votosBlanco,
        ];
    }

    public function export() 
    {
        $ldate = date('Y-m-d-H-i-s');
        return Excel::download(new SociosVotacionExport, 'Votacion_socios-'.$ldate.'.xlsx');
    }

    public function temporadas()
    {
        return Temporada::select('id','tema')
            ->whereNotIn('id',[1])
            ->get();
    }

    public function downloadPdf(Request $request)
    {
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

        $html .= '<img src="'.asset('storage/'.$imageName).'" width="700" height="900"/>';

        $html .= '<hr>
        <p style="text-align: center;">
        ©Derechos Reservados - Sistema de Votación Web V.1.0 - Generado '.$ldate_pdf.'
        </p>
        <hr>';

        $pdf->loadHtml($html);

        return $pdf->download('resultados_'.$ldate.'.pdf');
    }
}
