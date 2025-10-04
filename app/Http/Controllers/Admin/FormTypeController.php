<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormType;
use Illuminate\Http\Request;

class FormTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formTypes = FormType::orderBy('name')->paginate(10);
        return view('admin.form-types.index', compact('formTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.form-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'local_price' => 'required|numeric|min:0',
            'international_price' => 'required|numeric|min:0',
            'conversion_rate' => 'nullable|numeric|min:0|max:9999.9999',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        FormType::create($request->all());

        return redirect()->route('admin.form-types.index')
            ->with('success', 'Form type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FormType $formType)
    {
        return view('admin.form-types.show', compact('formType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormType $formType)
    {
        return view('admin.form-types.edit', compact('formType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FormType $formType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'local_price' => 'required|numeric|min:0',
            'international_price' => 'required|numeric|min:0',
            'conversion_rate' => 'nullable|numeric|min:0|max:9999.9999',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $formType->update($request->all());

        return redirect()->route('admin.form-types.index')
            ->with('success', 'Form type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormType $formType)
    {
        $formType->delete();

        return redirect()->route('admin.form-types.index')
            ->with('success', 'Form type deleted successfully.');
    }
}
