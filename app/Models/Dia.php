<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dia extends Model
{
    protected $table = 'dias';

    protected $fillable = ['fecha', 'hora_inicio', 'hora_fin', 'activo', 'contador'];

    protected function casts(): array
    {
        return [
            'fecha'  => 'date',
            'activo' => 'boolean',
        ];
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
