<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = ['dni', 'paterno', 'materno', 'nombres'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
