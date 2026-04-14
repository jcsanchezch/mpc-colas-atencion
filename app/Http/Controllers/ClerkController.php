<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Events\TicketCalled;
use App\Events\TicketStatusUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ClerkController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('ventanilla', 'tramites');
        $tramiteIds = $user->tramites->pluck('id');

        // Pending tickets for procedures the user can handle
        $pendingTickets = Ticket::with('tramite')
            ->whereIn('tramite_id', $tramiteIds)
            ->where('estado', 'ESPERANDO')
            ->orderBy('tiene_discapacidad', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Tickets currently assigned to this clerk and active
        $activeTicket = Ticket::with('tramite')
            ->where('usuario_id', $user->id)
            ->whereIn('estado', ['LLAMANDO', 'ATENDIENDO'])
            ->first();

        return Inertia::render('Clerk/Dashboard', [
            'pendingTickets' => $pendingTickets,
            'activeTicket' => $activeTicket,
            'userDesk' => $user->ventanilla ? $user->ventanilla->nombre : null
        ]);
    }

    public function callNext(Request $request)
    {
        $user = Auth::user();

        // If clerk already has an active ticket, they must finish it first
        $activeTicket = Ticket::where('usuario_id', $user->id)
            ->whereIn('estado', ['LLAMANDO', 'ATENDIENDO'])
            ->first();

        if ($activeTicket) {
            return redirect()->back()->withErrors(['message' => 'Ya tienes un turno en atención.']);
        }

        $tramiteIds = $user->tramites()->pluck('tramites.id');

        // Find next in queue
        $nextTicket = Ticket::with('tramite')
            ->whereIn('tramite_id', $tramiteIds)
            ->where('estado', 'ESPERANDO')
            ->orderBy('tiene_discapacidad', 'desc')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextTicket) {
            return redirect()->back()->with('error', 'No hay más tickets en espera.');
        }

        $affected = Ticket::where('id', $nextTicket->id)
            ->where('estado', 'ESPERANDO')
            ->update([
                'estado' => 'LLAMANDO',
                'usuario_id' => $user->id,
                'ventanilla' => $user->ventanilla ? $user->ventanilla->nombre : 'Mesa de Ayuda',
                'cantidad_llamados' => 1
            ]);

        if ($affected) {
            $nextTicket->refresh();
            event(new TicketCalled($nextTicket));
            return redirect()->back()->with('success', "Ticket {$nextTicket->numero} llamado.");
        }

        return redirect()->back()->withErrors(['message' => 'El ticket fue tomado por otro operador.']);
    }

    public function reCall(Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->usuario_id !== $user->id || !in_array($ticket->estado, ['LLAMANDO', 'ATENDIENDO'])) {
            return abort(403);
        }

        $ticket->increment('cantidad_llamados');
        event(new TicketCalled($ticket));
        return redirect()->back()->with('success', 'Ticket vuelto a llamar.');
    }

    public function serve(Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->usuario_id !== $user->id || $ticket->estado !== 'LLAMANDO') {
            return abort(403);
        }

        $ticket->update(['estado' => 'ATENDIENDO']);
        event(new TicketStatusUpdated($ticket));

        return redirect()->back();
    }

    public function complete(Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->usuario_id !== $user->id || !in_array($ticket->estado, ['LLAMANDO', 'ATENDIENDO'])) {
            return abort(403);
        }

        $ticket->update(['estado' => 'ATENDIDO']);
        event(new TicketStatusUpdated($ticket));

        return redirect()->back()->with('success', 'Atención finalizada.');
    }

    public function abandon(Ticket $ticket)
    {
        $user = Auth::user();
        // Allow abandon if it's called and called at least 3 times
        if ($ticket->usuario_id !== $user->id || $ticket->estado !== 'LLAMANDO' || $ticket->cantidad_llamados < 3) {
            return abort(403);
        }

        $ticket->update(['estado' => 'ABANDONADO']);
        event(new TicketStatusUpdated($ticket));

        return redirect()->back()->with('success', 'Ticket marcado como abandonado.');
    }
}
