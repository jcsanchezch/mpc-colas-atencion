<?php

namespace App\Http\Controllers;

use App\Models\Tramite;
use App\Models\Ticket;
use App\Events\TicketCreated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $tramites = Tramite::all();
        return Inertia::render('Ticket/Index', [
            'tramites' => $tramites
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|max:20',
            'tramite_id' => 'required|exists:tramites,id',
            'tiene_discapacidad' => 'boolean'
        ]);

        $tramite = Tramite::findOrFail($validated['tramite_id']);

        $latestTicket = Ticket::where('tramite_id', $tramite->id)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->numero);
            if (count($parts) == 2) {
                $nextNumber = intval($parts[1]) + 1;
            }
        }

        $numeroTicket = $tramite->prefijo . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $ticket = Ticket::create([
            'dni' => $validated['dni'],
            'tramite_id' => $validated['tramite_id'],
            'tiene_discapacidad' => $validated['tiene_discapacidad'] ?? false,
            'numero' => $numeroTicket,
            'estado' => 'ESPERANDO'
        ]);

        $ticket->load('tramite');

        broadcast(new TicketCreated($ticket));

        return redirect()->back()->with([
            'success' => 'Ticket generado exitosamente',
            'numeroTurno' => $numeroTicket,
            'dni' => $ticket->dni,
        ]);
    }
}
