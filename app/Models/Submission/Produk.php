<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'request_produk';

    protected $guarded = ['id'];

    protected $fillable = [
        'code_id',
        'user_id',
        'requestStatus',
    ];

    protected $casts = [
    ];

    public static function getFillableColumns()
    {
        // return (new static)->fillable;
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

}