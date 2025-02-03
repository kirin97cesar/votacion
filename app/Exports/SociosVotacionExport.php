<?php

namespace App\Exports;

use App\Models\Socio;
use App\Models\Temporada;
use App\Models\Voto;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\FromArray;

class SociosVotacionExport implements WithHeadings,WithColumnWidths,WithStyles,FromArray
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $temporadaUltima = Temporada::orderBy('id', 'DESC')
                ->take(1)
                ->get();

        $id= $temporadaUltima[0]['id'];

        $quienesVotaron = Voto::where('temporada_id', $id)
        ->select('socios.id')
        ->join('socios','socios.id','votos.socio_id')
        ->get();

        $quienesVotaron1 = Voto::where('temporada_id', $id)
        ->select('socios.nombres','socios.codigo',Voto::raw("'Sí Votó' AS condicion"))
        ->join('socios','socios.id','votos.socio_id')
        ->get();

        $obtenerId = [];
        foreach($quienesVotaron as $voto)
        {
            array_push($obtenerId, $voto['id']);
        }


        $quienesNoVotaron = Socio::select('socios.nombres','socios.codigo',Socio::raw("'No Votó' AS condicion"))
                        ->whereNotIn('socios.id',$obtenerId)
                        ->get();


       
        return [$quienesVotaron1, $quienesNoVotaron ];
    }

    public function headings(): array
    {
        return [
            ['Lista de Votantes'],
            ['Apellidos y Nombres','Código','Condición']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 60,
            'B' => 15,
            'C' => 20            
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB(Color::COLOR_DARKBLUE);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14);
    }
}
