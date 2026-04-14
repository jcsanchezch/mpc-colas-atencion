<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $table = 'trabajadores';

    protected $fillable = ['dni', 'paterno', 'materno', 'nombres'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function tramites()
    {
        return $this->belongsToMany(Tramite::class, 'trabajadores_tramites');
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
