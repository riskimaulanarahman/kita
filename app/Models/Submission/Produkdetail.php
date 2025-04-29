<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produkdetail extends Model
{
    use HasFactory;

    protected $table = 'request_produk_detail';

    protected $guarded = ['id'];
    protected $casts = [
        'req_id' => 'integer',
        'status_produk' => 'string',
        'harga_beli' => 'integer',
        'harga_jual_pasaran' => 'integer',
        'isi_perkarton' => 'integer',
        'minimal_order' => 'integer',
    ];
    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

}