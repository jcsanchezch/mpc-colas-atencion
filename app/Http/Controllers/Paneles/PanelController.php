<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Inertia\Inertia;
use function App\Http\Controllers\today;

class PanelController extends Controller
{
    public function index()
    {
        $activeTickets = Ticket::with('tramite')
            ->whereIn('estado', ['LLAMANDO', 'ATENDIENDO'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $completedTickets = Ticket::with('tramite')
            ->where('estado', 'ATENDIDO')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();

        $abandonedTickets = Ticket::with('tramite')
            ->where('estado', 'ABANDONADO')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();

        $pendingTickets = Ticket::with('tramite')
            ->where('estado', 'ESPERANDO')
            ->whereDate('created_at', today())
            ->orderBy('tiene_discapacidad', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Panel/Index', [
            'initialActiveTickets'    => $activeTickets,
            'initialCompletedTickets' => $completedTickets,
            'initialAbandonedTickets' => $abandonedTickets,
            'initialPendingTickets'   => $pendingTickets,
        ]);
    }
}
