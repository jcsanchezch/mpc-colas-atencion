<?php

namespace App\Http\Controllers\Atencion;

use App\Events\TicketCalled;
use App\Events\TicketStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('tramites', 'ventanilla');

        $tramiteIds = $user->tramites->pluck('id');

        $pendingTickets = Ticket::with(['tramite', 'atencionPrioritaria'])
            ->whereIn('tramite_id', $tramiteIds)
            ->where('estado', 'ESPERANDO')
            ->orderByDesc('prioridad')
            ->orderBy('hora_esperando')
            ->get();

        $activeTicket = Ticket::with(['tramite', 'atencionPrioritaria'])
            ->where('trabajador_id', $user->trabajador_id)
            ->whereIn('estado', ['LLAMANDO', 'ATENDIENDO'])
            ->first();

        return Inertia::render('Atencion/Tickets/Index', [
            'pendingTickets' => $pendingTickets,
            'activeTicket'   => $activeTicket,
            'ventanilla'     => $user->ventanilla?->only('id', 'codigo', 'nombre'),
        ]);
    }

    public function callNext(Request $request)
    {
        $user = Auth::user();

        $activeTicket = Ticket::where('trabajador_id', $user->trabajador_id)
            ->whereIn('estado', ['LLAMANDO', 'ATENDIENDO'])
            ->first();

        if ($activeTicket) {
            return redirect()->back()->withErrors(['message' => 'Ya tienes un turno en atención.']);
        }

        $tramiteIds = $user->tramites()->pluck('tramites.id');

        $nextTicket = Ticket::with(['tramite', 'atencionPrioritaria'])
            ->whereIn('tramite_id', $tramiteIds)
            ->where('estado', 'ESPERANDO')
            ->orderByDesc('prioridad')
            ->orderBy('hora_esperando')
            ->first();

        if (!$nextTicket) {
            return redirect()->back()->with('info', 'No hay tickets en espera.');
        }

        $affected = Ticket::where('id', $nextTicket->id)
            ->where('estado', 'ESPERANDO')
            ->update([
                'estado'        => 'LLAMANDO',
                'trabajador_id' => $user->trabajador_id,
                'ventanilla_id' => $user->ventanilla?->id,
                'hora_llamando' => now(),
            ]);

        if (!$affected) {
            return redirect()->back()->withErrors(['message' => 'El ticket fue tomado por otro operador.']);
        }

        $nextTicket->refresh();
        event(new TicketCalled($nextTicket));

        return redirect()->back()->with('success', "Ticket #{$nextTicket->numero} llamado.");
    }

    public function reCall(Ticket $ticket)
    {
        $user = Auth::user();

        if ($ticket->trabajador_id !== $user->trabajador_id || $ticket->estado !== 'LLAMANDO') {
            abort(403);
        }

        event(new TicketCalled($ticket));

        return redirect()->back()->with('success', 'Ticket vuelto a llamar.');
    }

    public function serve(Ticket $ticket)
    {
        $user = Auth::user();

        if ($ticket->trabajador_id !== $user->trabajador_id || $ticket->estado !== 'LLAMANDO') {
            abort(403);
        }

        $ticket->update([
            'estado'          => 'ATENDIENDO',
            'hora_atendiendo' => now(),
        ]);

        event(new TicketStatusUpdated($ticket));

        return redirect()->back()->with('success', 'Atención iniciada.');
    }

    public function complete(Ticket $ticket)
    {
        $user = Auth::user();

        if ($ticket->trabajador_id !== $user->trabajador_id || !in_array($ticket->estado, ['LLAMANDO', 'ATENDIENDO'])) {
            abort(403);
        }

        $horaAtendido = now();
        $tiempoEsperando = $ticket->hora_esperando
            ? (int) $ticket->hora_esperando->diffInSeconds($horaAtendido)
            : 0;
        $tiempoAtendiendo = $ticket->hora_atendiendo
            ? (int) $ticket->hora_atendiendo->diffInSeconds($horaAtendido)
            : 0;

        $ticket->update([
            'estado'                     => 'ATENDIDO',
            'hora_atendido'              => $horaAtendido,
            'atendido'                   => true,
            'tiempo_esperando_atendido'  => $tiempoEsperando,
            'tiempo_atendiendo_atendido' => $tiempoAtendiendo,
        ]);

        event(new TicketStatusUpdated($ticket));

        return redirect()->back()->with('success', 'Atención completada.');
    }

    public function abandon(Ticket $ticket)
    {
        $user = Auth::user();

        if ($ticket->trabajador_id !== $user->trabajador_id || $ticket->estado !== 'LLAMANDO') {
            abort(403);
        }

        $ticket->update([
            'estado'          => 'ABANDONADO',
            'hora_abandonado' => now(),
        ]);

        event(new TicketStatusUpdated($ticket));

        return redirect()->back()->with('success', 'Ticket marcado como abandonado.');
    }
}
