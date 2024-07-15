<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portotrans extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function portoMember(){
        return $this->belongsTo(PortoMember::class);
    }
}
