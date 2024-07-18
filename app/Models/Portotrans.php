<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portotrans extends Model
{
    use HasFactory;

    protected $guarded = ["id"];
    protected $hidden = [
        'password', 
        'email_verified_at', 
        'remember_token'
    ];

    public function portoMember(){
        return $this->belongsTo(PortoMember::class);
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'portomember_id', 'id');
    // }

    public function user()
    {
        return $this->belongsTo(PortoMember::class, 'portomember_id', 'id');
    }
}
