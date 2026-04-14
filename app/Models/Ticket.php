<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'dia_id',
        'cliente_id',
        'numero',
        'prioridad',
        'atencion_prioritaria_id',
        'tramite_id',
        'hora_esperando',
        'hora_llamando',
        'hora_atendiendo',
        'hora_atendido',
        'hora_abandonado',
        'tiempo_atendiendo_atendido',
        'tiempo_esperando_atendido',
        'estado',
        'atendido',
        'cerrado',
        'ventanilla_id',
        'trabajador_id',
    ];

    protected function casts(): array
    {
        return [
            'prioridad'          => 'boolean',
            'atendido'           => 'boolean',
            'cerrado'            => 'boolean',
            'numero'                      => 'integer',
            'hora_esperando'              => 'datetime',
            'hora_llamando'               => 'datetime',
            'hora_atendiendo'             => 'datetime',
            'hora_atendido'               => 'datetime',
            'hora_abandonado'             => 'datetime',
            'tiempo_atendiendo_atendido'  => 'integer',
            'tiempo_esperando_atendido'   => 'integer',
        ];
    }

    public function dia()
    {
        return $this->belongsTo(Dia::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function atencionPrioritaria()
    {
        return $this->belongsTo(AtencionPrioritaria::class);
    }

    public function tramite()
    {
        return $this->belongsTo(Tramite::class);
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
