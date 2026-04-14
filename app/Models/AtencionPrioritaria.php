<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionPrioritaria extends Model
{
    protected $fillable = ['nombre'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
