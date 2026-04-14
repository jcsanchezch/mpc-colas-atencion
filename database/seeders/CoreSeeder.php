<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AtencionPrioritaria;
use App\Models\Ventanilla;
use App\Models\Tramite;

class CoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Ventanilla::updateOrCreate([
                'nombre' => "Ventanilla $i",
                'codigo' => "$i"
            ]);
        }

        $tramites = [
            ['nombre' => 'Acceso a Información'],
            ['nombre' => 'Transportes'],
            ['nombre' => 'Comercio'],
            ['nombre' => 'Construcción'],
            ['nombre' => 'Trámites en General'],
        ];

        foreach ($tramites as $tramite) {
            Tramite::updateOrCreate([
                'nombre' => $tramite['nombre']
            ]);
        }

        $atencionesPrioritarias = [
            ['nombre' => 'Adulto mayor'],
            ['nombre' => 'Mujer embarazada'],
            ['nombre' => 'Persona con discapacidad'],
            ['nombre' => 'Persona con niño en brazos'],
        ];

        foreach ($atencionesPrioritarias as $atencion) {
            AtencionPrioritaria::updateOrCreate([
                'nombre' => $atencion['nombre']
            ]);
        }
    }
}
