<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sampeldetail extends Model
{
    use HasFactory;

    protected $table = 'request_sampel_detail';

    protected $guarded = ['id'];
    protected $casts = [
        'sampel_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'status_sampel' => 'string',
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