<?php

namespace App\Models\Module;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datapelanggan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function odps()
    {
        return $this->belongsTo('App\Models\Module\Odp','odp_id');
    }

}
