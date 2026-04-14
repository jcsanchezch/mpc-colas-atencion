<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaVentanillaTrabajador extends Model
{
    protected $table = 'ventanillas_dias_trabajadores';

    protected $fillable = ['ventanilla_id', 'dia_id', 'trabajador_id', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function dia()
    {
        return $this->belongsTo(Dia::class);
    }

    public function ventanilla()
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }
}
