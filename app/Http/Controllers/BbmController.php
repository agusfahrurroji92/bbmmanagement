<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BbmImport;
use App\Exports\BbmExport;
use App\Exports\BbmRatioExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Bbm;
use Illuminate\Support\Facades\DB;
use Validator;

class BbmController extends Controller
{
    public function bbmManagement(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'file_bbm' => 'required | file',
        ]);
        if($validation->passes()){
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
        }else{
            return response()->json(implode(",",$validation->messages()->all()));
        }
    }

    public function reportBbm(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'date_from' => 'required | date_format:d-m-Y',
            'date_to' => 'required |date_format:d-m-Y',
        ]);
        if($validation->passes()){
            $date_from = date_format(date_create($request->date_from),'Y-m-d');
            $date_to = date_format(date_create($request->date_to),'Y-m-d');
            $q = Bbm::whereBetween('tgl',[$date_from,$date_to]);
            if(!empty($request->cabang)){
                $q = $q->whereRaw("LOWER(cabang) in (".strtolower($request->cabang).")");
            }
            switch($request->type_report){
                case 'ratio':
                    $res = $q->selectRaw("date_format(tgl,'%m-%Y') as bulan, cabang,no_kendaraan,jenis_mobil,FORMAT(max(analisa),2) as max_ratio,FORMAT(min(analisa),2) as min_ratio,FORMAT(sum(analisa)/count(analisa),2) as avr_ratio")
                    ->where('analisa','!=',0)->groupBy(DB::raw("date_format(tgl,'%m-%Y')"),'cabang','no_kendaraan','jenis_mobil')->orderBy(DB::raw("date_format(tgl,'%m-%Y')"),'asc')->orderBy('cabang','asc')->orderBy('no_kendaraan','asc')->get();
                    $no = 0;
                    $data = array();
                    $data_key = array();
                    $bulan = array();
                    foreach($res as $item){
                        $keyMap = $item->cabang."_".$item->no_kendaraan;
                        $cekData = array_search($keyMap,$data_key);
                        if(!empty($cekData)){
                            foreach($bulan as $itemBu){
                                if($itemBu == $item->bulan){
                                    $data[$cekData-1]["max_ratio_".$item->bulan] = $item->max_ratio;
                                    $data[$cekData-1]["min_ratio_".$item->bulan] = $item->min_ratio;
                                    $data[$cekData-1]["avr_ratio_".$item->bulan] = $item->avr_ratio;
                                }else if(!isset($data[$cekData-1]["max_ratio_".$item->bulan])){
                                    $data[$cekData-1]["max_ratio_".$item->bulan] = "";
                                    $data[$cekData-1]["min_ratio_".$item->bulan] = "";
                                    $data[$cekData-1]["avr_ratio_".$item->bulan] = "";
                                }
                            }
                        }else{
                            $no++;
                            if(empty(array_search($item->bulan,$bulan))){
                                $bulan[$no] = $item->bulan;
                            }
                            $ins = [
                                'no' => $no,
                                "cabang"=> $item->cabang,
                                "no_kendaraan"=> $item->no_kendaraan,
                                "jenis_mobil"=> $item->jenis_mobil
                            ];
                            foreach($bulan as $itemBu){
                                if($itemBu == $item->bulan){
                                    $ins["max_ratio_".$item->bulan] = $item->max_ratio;
                                    $ins["min_ratio_".$item->bulan] = $item->min_ratio;
                                    $ins["avr_ratio_".$item->bulan] = $item->avr_ratio;
                                }else{
                                    $ins["max_ratio_".$itemBu] = "";
                                    $ins["min_ratio_".$itemBu] = "";
                                    $ins["avr_ratio_".$itemBu] = "";
                                }
                            }
                            array_push($data,$ins);
                            $data_key[$no] = $keyMap;
                        }
                    }

                    $export = new BbmRatioExport($data,$bulan);
                    $nama_file = "bbm_";
                    if(!empty($request->cabang)){
                        $nama_file .= str_replace(',','-',$request->cabang);
                    }else{
                        $nama_file .= "all_";
                    }
                    $nama_file .= $request->date_from."_".$request->date_to;
                    return Excel::download($export, $nama_file .'.xlsx');
                    break;
                default:
                    $q = $q->select(DB::raw("DATE_FORMAT(tgl,'%d/%m/%Y')"),'no_kendaraan','jenis_mobil','km_awal','km_akhir','total_km','sum_prev_km','isi_bbm',
                    'supir','ritase','divisi','harga_bbm','analisa','cabang','ket');
                    $res = $q->orderBy('no_kendaraan','asc')->orderBy('tgl','asc')->orderBy('cabang','asc')->get();
                    $nopol = "";
                    $data = array();
                    $formatSpace =[
                        "tgl"=> "",
                        "no_kendaraan"=> "",
                        "jenis_mobil"=> "",
                        "km_awal"=> "",
                        "km_akhir"=> "",
                        "total_km"=> "",
                        "sum_prev_km"=> "",
                        "isi_bbm"=> "",
                        "supir"=> "",
                        "ritase"=> "",
                        "divisi"=> "",
                        "harga_bbm"=> "",
                        "analisa"=> "",
                        "cabang"=> "",
                        "ket"=> null
                    ];
                    foreach($res as $key => $item){
                        if($key > 0 && $item->no_kendaraan != $nopol){
                            array_push($data,$formatSpace);
                        }else{
                            array_push($data,$item);
                        }
                        $nopol = $item->no_kendaraan;
                    }

                    $export = new BbmExport($data);
                    $nama_file = "bbm_";
                    if(!empty($request->cabang)){
                        $nama_file .= str_replace(',','-',$request->cabang);
                    }else{
                        $nama_file .= "all_";
                    }
                    $nama_file .= $request->date_from."_".$request->date_to;
                    return Excel::download($export, $nama_file .'.xlsx');
                    break;
            }
        }else{
            return response()->json(implode(",",$validation->messages()->all()));
        }
    }

}
