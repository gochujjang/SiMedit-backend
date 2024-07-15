<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortoMember extends Model
{
    use HasFactory;
    protected $guarded = ["id"];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portofolio()
    {
        return $this->belongsTo(Portofolio::class);
    }

    public function portoTrans()
    {
        return $this->hasMany(Portotrans::class);
    }
}
