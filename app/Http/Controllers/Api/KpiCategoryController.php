<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiCategory;
use Illuminate\Http\Request;

class KpiCategoryController extends Controller
{
    public function index()
    {
        return response()->json(KpiCategory::with('kpis')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:kpi_categories,name',
            'icon' => 'nullable|string|max:50',
        ]);

        $category = KpiCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show(KpiCategory $kpiCategory)
    {
        return response()->json($kpiCategory->load('kpis'));
    }

    public function update(Request $request, KpiCategory $kpiCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:kpi_categories,name,' . $kpiCategory->id,
            'icon' => 'nullable|string|max:50',
        ]);

        $kpiCategory->update($validated);
        return response()->json($kpiCategory);
    }

    public function destroy(KpiCategory $kpiCategory)
    {
        $kpiCategory->delete();
        return response()->json(['message' => 'Catégorie KPI supprimée avec succès']);
    }
}
