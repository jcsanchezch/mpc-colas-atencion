<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Tramite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TramiteController extends Controller
{
    public function index()
    {
        return Inertia::render('Atencion/Tramites/Index', [
            'tramites' => Tramite::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tramites',
        ]);
        Tramite::create([
            'nombre' => $request->nombre,
            'activo'  => true,
        ]);
        return redirect()->back()->with('success', 'Trámite creado.');
    }

    public function update(Request $request, Tramite $tramite)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tramites,nombre,'.$tramite->id,
            'activo'  => 'boolean',
        ]);
        $tramite->update($request->only('nombre', 'activo'));
        return redirect()->back()->with('success', 'Trámite actualizado.');
    }

    public function destroy(Tramite $tramite)
    {
        $tramite->delete();
        return redirect()->back()->with('success', 'Trámite eliminado.');
    }
}
