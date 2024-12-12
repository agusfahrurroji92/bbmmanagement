<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BbmImport;
use App\Exports\BbmExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Bbm;
use Illuminate\Support\Facades\DB;

class BbmController extends Controller
{
    public function BbmManagement(Request $request)
    {
        $data = Excel::toArray(new BbmImport(), $request->file_bbm);
        $prev_total_km = array();
        $res = array();
        $bigIns = array();
        $head = array();
        $nama_cabang = count($data[0]) > 0 ? $data[0][1][12] : "sheet1";
        $no_car = "";
        $indx = 0;
        foreach ($data[0] as $key => $value) {
            if($key == 0){
                $head = $value;
            }else{
                if(!empty($value[1])){
                    $indx++;
                    $data_i = [
                        'tgl'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value[0])->format('Y-m-d'),
                        'no_kendaraan'=>$value[1],
                        'jenis_mobil'=>$value[2],
                        'km_awal'=>empty($value[3]) ? 0 : $value[3],
                        'km_akhir'=>empty($value[4]) ? 0 : $value[4],
                        'total_km'=>empty($value[5]) ? 0 : $value[5],
                        'sum_prev_km'=>0,
                        'isi_bbm'=>(empty($value[6]) || $value[6] == "-") ? (float) 0 : (float) $value[6],
                        'supir'=>$value[7],
                        'ritase'=>$value[8],
                        'divisi'=>$value[9],
                        'harga_bbm'=>empty($value[10]) ? 0 : str_replace('Rp. ','',$value[10]),
                        'analisa'=>$value[11],
                        'cabang'=>$value[12],
                    ];
                    if($value[1] == $no_car){
                        $prev_km = empty($prev_total_km[$value[1]]) ? 0 : $prev_total_km[$value[1]];
                        if(empty($value[6])){
                            $total_km = $prev_km + $value[5];
                            $data_i['sum_prev_km'] = $total_km;
                            $prev_total_km[$value[1]] = $total_km;
                            if($indx % 100 == 0){
                                array_push($res,$data_i);
                                array_push($bigIns,$res);
                                $res = array();
                            }else{
                                array_push($res,$data_i);
                                if($indx == count($data[0])){
                                    array_push($bigIns,$res);
                                }
                            }
                            $data_i = [];
                        }else{
                            $total_km = $prev_km + $value[5];
                            $analisa = (int) $value[6] == 0 ? null : (int) $total_km / (int) $value[6];
                            $data_i['sum_prev_km'] = 0;
                            $data_i['analisa'] = $analisa != null ? (float) $analisa : (float) $analisa;
                            if($indx % 100 == 0){
                                array_push($res,$data_i);
                                array_push($bigIns,$res);
                                $res = array();
                            }else{
                                array_push($res,$data_i);
                                if($indx == count($data[0])){
                                    array_push($bigIns,$res);
                                }
                            }
                            $data_i = [];
                            $prev_total_km[$value[1]] = 0;
                        }
                    }else{
                        $data_prev = Bbm::select(DB::raw("IFNULL(sum_prev_km,0) as sum_prev_km"))->where('no_kendaraan',$value[1])->orderBy('tgl','desc')->first();
                        $prev_km = $data_prev == null ? 0 : $data_prev->sum_prev_km;
                        if(empty($value[6])){
                            $total_km = $prev_km + $value[5];
                            $data_i['sum_prev_km'] = $total_km;
                            $prev_total_km[$value[1]] = $total_km;
                            if($indx % 100 == 0){
                                array_push($res,$data_i);
                                array_push($bigIns,$res);
                                $res = array();
                            }else{
                                array_push($res,$data_i);
                                if($indx == count($data[0])){
                                    array_push($bigIns,$res);
                                }
                            }
                            $data_i = [];
                        }else{
                            $total_km = $prev_km + $value[5];
                            $analisa = (int) $value[6] == 0 ? null : (int) $total_km / (int) $value[6];
                            $data_i['sum_prev_km'] = 0;
                            $data_i['analisa'] = $analisa != null ? (float) $analisa : (float) $analisa;
                            if($indx % 100 == 0){
                                array_push($res,$data_i);
                                array_push($bigIns,$res);
                                $res = array();
                            }else{
                                array_push($res,$data_i);
                                if($indx == count($data[0])){
                                    array_push($bigIns,$res);
                                }
                            }
                            $data_i = [];
                            $prev_total_km[$value[1]] = 0;
                        }
                    }
                    $no_car = $value[1];
                }
            }
        }
        DB::beginTransaction();
        try{
            foreach($bigIns as $key =>$item){
                $ins = Bbm::upsert($item,['tgl','no_kendaraan']);
            }
            DB::commit();
        }catch(\Exception $e){ 
            DB::rollBack();
            return $e->getMessage();
        }
        return $res;
        $export = new BbmExport($res,$head);
        return Excel::download($export, 'bbm_'.$nama_cabang.'.xlsx');
    }
}
