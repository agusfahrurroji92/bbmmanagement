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

class BbmExport implements FromCollection, WithHeadings, WithEvents, WithColumnFormatting
{
    use RegistersEventListeners;
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $head;

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function __construct($data,$head)
    {
 
        $this->data = $data;
        $this->head = $head;
 
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

        return $this->head;

    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

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
