<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'trabajador_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    /**
     * Trámites que el usuario puede atender,
     * a través de la tabla trabajadores_tramites usando trabajador_id.
     */
    public function tramites()
    {
        return $this->belongsToMany(
            Tramite::class,
            'trabajadores_tramites',
            'trabajador_id', // columna en el pivot que apunta a trabajadores
            'tramite_id',
            'trabajador_id', // clave local en users
            'id'
        );
    }

    /**
     * Ventanilla asignada al trabajador en el día activo actual.
     */
    public function ventanilla()
    {
        return $this->hasOneThrough(
            Ventanilla::class,
            DiaVentanillaTrabajador::class,
            'trabajador_id', // FK en ventanillas_dias_trabajadores → trabajadores
            'id',            // FK en ventanillas
            'trabajador_id', // clave local en users
            'ventanilla_id'  // clave local en ventanillas_dias_trabajadores
        )->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('dias')
                ->whereColumn('dias.id', 'ventanillas_dias_trabajadores.dia_id')
                ->where('dias.activo', true);
        });
    }
}
