<?php

namespace App\Http\Controllers;

use App\Models\{modelName};
use Illuminate\Http\Request;

class {modelName}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = {modelName}::all();
        $fields = array_filter(
            \Schema::getColumnListing('{tableName}'),
            fn($field) => !in_array($field, ['id', 'created_at', 'updated_at'])
        );
        return view('{viewPath}', compact('records','fields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('{createViewPath}');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            {validationRules}
        ]);

        {modelName}::create($request->all());

        return redirect()->route('{routeName}.index')
            ->with('success', '{modelName} created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show({modelName} $modelName)
    {
        return view('show', compact('{variableName}'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Find the model instance by id
        ${variableName} = {modelName}::find($id);
        return view('{editViewPath}', compact('{variableName}'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            {validationRules}
        ]);

        // Find the model instance by id and update it
        ${variableName} = {modelName}::find($id);
        ${variableName}->update($request->all());

        return redirect()->route('{routeName}.index')
            ->with('success', '{modelName} updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the model instance by id and delete it
        ${variableName} = {modelName}::find($id);
        ${variableName}->delete();

        return redirect()->route('{routeName}.index')
            ->with('success', '{modelName} deleted successfully.');
    }
}
