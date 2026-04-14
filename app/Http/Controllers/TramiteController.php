<?php

namespace App\Http\Controllers;

use App\Models\Tramite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TramiteController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Tramites/Index', [
            'tramites' => Tramite::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tramites',
            'prefijo' => 'required|string|max:2|unique:tramites'
        ]);
        Tramite::create($request->only('nombre', 'prefijo'));
        return redirect()->back()->with('success', 'Trámite creado.');
    }

    public function update(Request $request, Tramite $tramite)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tramites,nombre,'.$tramite->id,
            'prefijo' => 'required|string|max:2|unique:tramites,prefijo,'.$tramite->id
        ]);
        $tramite->update($request->only('nombre', 'prefijo'));
        return redirect()->back()->with('success', 'Trámite actualizado.');
    }

    public function destroy(Tramite $tramite)
    {
        $tramite->delete();
        return redirect()->back()->with('success', 'Trámite eliminado.');
    }
}
