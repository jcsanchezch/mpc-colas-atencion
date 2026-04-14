<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ventanilla extends Model
{
    protected $fillable = ['codigo', 'nombre'];

    public function tramites()
    {
        return $this->belongsToMany(Tramite::class, 'ventanillas_tramites');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(DiaVentanillaTrabajador::class);
    }
}
