<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function trabajadores()
    {
        return $this->belongsToMany(Trabajador::class, 'trabajadores_tramites');
    }

    public function ventanillas()
    {
        return $this->belongsToMany(Ventanilla::class, 'ventanillas_tramites');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
