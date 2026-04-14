<?php

namespace App\Http\Controllers;

use App\Models\Ventanilla;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentanillaController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Ventanillas/Index', [
            'ventanillas' => Ventanilla::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:ventanillas']);
        Ventanilla::create($request->only('nombre'));
        return redirect()->back()->with('success', 'Ventanilla creada.');
    }

    public function update(Request $request, Ventanilla $ventanilla)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:ventanillas,nombre,'.$ventanilla->id]);
        $ventanilla->update($request->only('nombre'));
        return redirect()->back()->with('success', 'Ventanilla actualizada.');
    }

    public function destroy(Ventanilla $ventanilla)
    {
        $ventanilla->delete();
        return redirect()->back()->with('success', 'Ventanilla eliminada.');
    }
}
