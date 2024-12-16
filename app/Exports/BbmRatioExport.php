<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BbmRatioExport implements FromCollection, WithHeadings,  WithEvents
{
    use RegistersEventListeners;
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $header;

    /**

     * Write code on Method

     *

     * @return response()

     */

    public function __construct($data,$header)
    {
 
        $this->data = $data;
        $this->header = $header;
 
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
        $header1 = ['NO','Cabang','Plat Mobil','Jenis Mobil'];
        $header2 = ['','','',''];
        foreach($this->header as $item){
            if(!empty($item)){
                $header1[] = '';
                $header1[] = date_format(date_create('01-'.$item),"M-Y");
                $header1[] = '';
                $header2[] = 'Tertinggi';
                $header2[] = 'Terendah';
                $header2[] = 'Average Ratio';
            }
        }
        return array($header1,$header2);

    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
    //     ];
    // }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->getStyle('A1:ZZ2')->getFont()->setBold(true);
            },
        ];
    }
}
