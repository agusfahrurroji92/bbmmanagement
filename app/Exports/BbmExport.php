<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BbmExport implements FromCollection, WithHeadings
{
    use RegistersEventListeners;
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function __construct($data)
    {
 
        $this->data = $data;
 
    }
 
   
 
     /**
 
      * Write code on Method
 
      *
 
      * @return response()
 
      */
 
    public function collection()
    {
 
         return collect($this->data);
 
    }

    public function headings() :array
    {

        return [
            'Tgl',
            'No Polisi',
            'Jenis Mobil',
            'Km Awal',
            'Km Akhir',
            'Total Km',
            'Prev Sum Total Km',
            'Isi Bbm',
            'Supir',
            'Ritase',
            'Divisi',
            'Harga Bbm',
            'Analisa',
            'Cabang',
            'Keterangan',
        ];

    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
    //     ];
    // }

    // public static function afterSheet(AfterSheet $event)
    // {
    //     $sheet = $event->sheet->getDelegate();
    //     foreach($sheet->get as $key => $value){
    //         if($value[11] < 5){
    //             $from_col = "A".$key+1;
    //             $to_col = "M".$key+1;
    //             $sheet->getStyle($from_col.':'.$to_col)->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()->setARGB('FFFF0000');
    //         }
    //     }
    // }
}
