<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->unique();
            $table->string('paterno');
            $table->string('materno');
            $table->string('nombres');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores');
        });


        Schema::create('tramites', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('trabajadores_tramites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores');
            $table->foreignId('tramite_id')->constrained('tramites');
            $table->timestamps();

            $table->unique(['trabajador_id', 'tramite_id']);
        });

        Schema::create('ventanillas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('ventanillas_tramites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ventanilla_id')->constrained('ventanillas');
            $table->foreignId('tramite_id')->constrained('tramites');
            $table->timestamps();

            $table->unique(['ventanilla_id', 'tramite_id']);
        });

        Schema::create('dias', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedInteger('contador')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('ventanillas_dias_trabajadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ventanilla_id')->constrained('ventanillas');
            $table->foreignId('dia_id')->constrained('dias');
            $table->foreignId('trabajador_id')->constrained('trabajadores');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['ventanilla_id', 'dia_id','trabajador_id']);
        });


        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->unique();
            $table->string('paterno');
            $table->string('materno');
            $table->string('nombres');
            $table->timestamps();
        });


        Schema::create('atenciones_prioritarias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });


        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dia_id')->constrained('dias');
            $table->foreignId('cliente_id')->constrained('clientes');

            $table->unsignedInteger('numero');

            $table->boolean('prioridad')->default(false);
            $table->foreignId('atencion_prioritaria_id')->nullable()->constrained('atenciones_prioritarias');


            $table->foreignId('tramite_id')->constrained('tramites');

            $table->timestamp('hora_esperando', 0)->nullable();
            $table->timestamp('hora_llamando', 0)->nullable();
            $table->timestamp('hora_atendiendo', 0)->nullable();
            $table->timestamp('hora_atendido', 0)->nullable();
            $table->timestamp('hora_abandonado', 0)->nullable();

            $table->integer('tiempo_atendiendo_atendido')->default(0);
            $table->integer('tiempo_esperando_atendido')->default(0);

            $table->string('estado')->default('ESPERANDO'); // ESPERANDO, LLAMANDO, ATENDIENDO, ATENDIDO, ABANDONADO

            $table->boolean('atendido')->default(false);
            $table->boolean('cerrado')->default(false);

            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas');
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores');

            $table->timestamps();
        });



    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ventanillas_dias_trabajadores');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('atenciones_prioritarias');
        Schema::dropIfExists('dias');
        Schema::dropIfExists('ventanillas_tramites');
        Schema::dropIfExists('ventanillas');
        Schema::dropIfExists('trabajadores_tramites');
        Schema::dropIfExists('tramites');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['trabajador_id']);
            $table->dropColumn('trabajador_id');
        });

        Schema::dropIfExists('trabajadores');
    }
};
