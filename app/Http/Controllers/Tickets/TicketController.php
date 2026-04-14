<?php

namespace App\Http\Controllers\Tickets;

use App\Events\TicketCreated;
use App\Http\Controllers\Controller;
use App\Models\AtencionPrioritaria;
use App\Models\Cliente;
use App\Models\Dia;
use App\Models\Ticket;
use App\Models\Tramite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $tramites = Tramite::where('activo', true)->get();
        $atencionesPrioritarias = AtencionPrioritaria::all();

        return Inertia::render('Tickets/Ticket/Index', [
            'tramites'              => $tramites,
            'atencionesPrioritarias' => $atencionesPrioritarias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni'                    => 'required|string|size:8',
            'tramite_id'             => 'required|exists:tramites,id',
            'atencion_prioritaria_id' => 'nullable|exists:atenciones_prioritarias,id',
        ]);

        $dia = Dia::where('fecha', today())
            ->where('activo', true)
            ->first();

        if (!$dia) {
            return back()->withErrors(['dia' => 'No hay una jornada activa para hoy.']);
        }

        $cliente = Cliente::firstOrCreate(
            ['dni' => $validated['dni']],
            ['paterno' => '', 'materno' => '', 'nombres' => '']
        );

        $dia->increment('contador');
        $numero = $dia->contador;

        $prioridad = !empty($validated['atencion_prioritaria_id']);

        $ticket = Ticket::create([
            'dia_id'                  => $dia->id,
            'cliente_id'              => $cliente->id,
            'numero'                  => $numero,
            'prioridad'               => $prioridad,
            'atencion_prioritaria_id' => $validated['atencion_prioritaria_id'] ?? null,
            'tramite_id'              => $validated['tramite_id'],
            'hora_esperando'          => now(),
            'estado'                  => 'ESPERANDO',
        ]);

        $ticket->load('tramite', 'atencionPrioritaria');

        broadcast(new TicketCreated($ticket));

        return back()->with([
            'success'        => 'Ticket generado exitosamente',
            'numeroTurno'    => $numero,
            'dni'            => $validated['dni'],
            'prioridad'      => $prioridad,
            'atencionNombre' => $ticket->atencionPrioritaria?->nombre,
        ]);
    }
}
