<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model
{
    use HasFactory;

    protected $guarded = ["id"];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function portotrans(){
        return $this->hasMany(Portotrans::class);
    }

    public function transaksi_porto(){
        return $this->hasMany(Portotrans::class, 'porto_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'porto_members');
    }

    public function portoMembers()
    {
        return $this->hasMany(PortoMember::class);
    }
}
