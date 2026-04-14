<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Ventanilla;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentanillaController extends Controller
{
    public function index()
    {
        return Inertia::render('Atencion/Ventanillas/Index', [
            'ventanillas' => Ventanilla::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:ventanillas',
            'nombre' => 'required|string|max:255',
        ]);
        Ventanilla::create($request->only('codigo', 'nombre'));
        return redirect()->back()->with('success', 'Ventanilla creada.');
    }

    public function update(Request $request, Ventanilla $ventanilla)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:ventanillas,codigo,'.$ventanilla->id,
            'nombre' => 'required|string|max:255',
        ]);
        $ventanilla->update($request->only('codigo', 'nombre'));
        return redirect()->back()->with('success', 'Ventanilla actualizada.');
    }

    public function destroy(Ventanilla $ventanilla)
    {
        $ventanilla->delete();
        return redirect()->back()->with('success', 'Ventanilla eliminada.');
    }
}
