<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// use LdapRecord\Laravel\Auth\LdapAuthenticatable;
// use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
// use LdapRecord\Laravel\Auth\HasLdapUser;

use App\Models\Session;
use App\Models\Theme;
use App\Models\Employee;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'fullname',
        'company_name',
        'email',
        'password',
        'avatar',
        'username',
        'password',
        'passtxt',
        'theme',
        'isAdmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'isAdmin' => 'integer',
        'theme_id' => 'integer',
    ];
    // protected $with = ['employee'];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function theme()
    {
        return $this->hasOne(Theme::class);
    }

}