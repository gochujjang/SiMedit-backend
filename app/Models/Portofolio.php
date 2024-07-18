<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $hidden = [
        'password', 
        'email_verified_at', 
        'remember_token'
    ];


    public function portoMembers()
    {
        return $this->hasMany(PortoMember::class);
    }

    public function transaksi_porto(){
        return $this->hasMany(Portotrans::class, 'portomember_id');
    }
}
