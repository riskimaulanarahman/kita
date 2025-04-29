<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogError extends Model
{
    protected $table = "_log_error";

    protected $guarded = ['id'];
}
