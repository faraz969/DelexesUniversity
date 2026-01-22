<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intake;
use Illuminate\Http\Request;

class IntakeController extends Controller
{
    public function index()
    {
        $intakes = Intake::orderBy('sort_order')->get();
        return view('admin.intakes.index', compact('intakes'));
    }

    public function create()
    {
        return view('admin.intakes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        Intake::create($validated);

        return redirect()->route('admin.intakes.index')
            ->with('success', 'Intake created successfully.');
    }

    public function show(Intake $intake)
    {
        return view('admin.intakes.show', compact('intake'));
    }

    public function edit(Intake $intake)
    {
        return view('admin.intakes.edit', compact('intake'));
    }

    public function update(Request $request, Intake $intake)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $intake->update($validated);

        return redirect()->route('admin.intakes.index')
            ->with('success', 'Intake updated successfully.');
    }

    public function destroy(Intake $intake)
    {
        $intake->delete();

        return redirect()->route('admin.intakes.index')
            ->with('success', 'Intake deleted successfully.');
    }
}
