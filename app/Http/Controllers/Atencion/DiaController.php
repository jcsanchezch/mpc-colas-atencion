<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Dia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiaController extends Controller
{
    public function index()
    {
        return Inertia::render('Atencion/Dias/Index', [
            'dias' => Dia::orderBy('fecha', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'       => 'required|date|unique:dias',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        ]);
        Dia::create([
            'fecha'       => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin'    => $request->hora_fin,
            'activo'      => true,
            'contador'    => 0,
        ]);
        return redirect()->back()->with('success', 'Día creado.');
    }

    public function update(Request $request, Dia $dia)
    {
        $request->validate([
            'fecha'       => 'required|date|unique:dias,fecha,'.$dia->id,
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'activo'      => 'boolean',
        ]);
        $dia->update($request->only('fecha', 'hora_inicio', 'hora_fin', 'activo'));
        return redirect()->back()->with('success', 'Día actualizado.');
    }

    public function destroy(Dia $dia)
    {
        $dia->delete();
        return redirect()->back()->with('success', 'Día eliminado.');
    }
}
