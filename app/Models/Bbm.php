<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bbm extends Model
{
    // use HasFactory;
    protected $table = "bbms";
 
    protected $fillable = [
        'tgl',
        'no_kendaraan',
        'jenis_mobil',
        'km_awal',
        'km_akhir',
        'total_km',
        'sum_prev_km',
        'isi_bbm',
        'supir',
        'ritase',
        'divisi',
        'harga_bbm',
        'analisa',
        'cabang',
        'ket',
    ];
}
