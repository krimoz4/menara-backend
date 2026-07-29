<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function index()
    {
        return response()->json(Kpi::with('category')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kpi_category_id' => 'required|exists:kpi_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'target_value' => 'required|numeric',
            'is_higher_better' => 'boolean',
        ]);

        $kpi = Kpi::create($validated);
        return response()->json($kpi->load('category'), 201);
    }

    public function show(Kpi $kpi)
    {
        return response()->json($kpi->load(['category', 'records']));
    }

    public function update(Request $request, Kpi $kpi)
    {
        $validated = $request->validate([
            'kpi_category_id' => 'sometimes|required|exists:kpi_categories,id',
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'unit' => 'sometimes|required|string|max:50',
            'target_value' => 'sometimes|required|numeric',
            'is_higher_better' => 'boolean',
        ]);

        $kpi->update($validated);
        return response()->json($kpi->load('category'));
    }

    public function destroy(Kpi $kpi)
    {
        $kpi->delete();
        return response()->json(['message' => 'KPI supprimé avec succès']);
    }
}
