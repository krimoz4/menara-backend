<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiRecord;
use Illuminate\Http\Request;

class KpiRecordController extends Controller
{
    public function index()
    {
        $records = KpiRecord::with(['kpi', 'department'])->get();
        return response()->json($records);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kpi_id' => 'required|exists:kpis,id',
            'department_id' => 'required|exists:departments,id',
            'recorded_value' => 'required|numeric',
            'recorded_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $record = KpiRecord::create($validatedData);

        return response()->json([
            'message' => 'Relevé enregistré avec succès',
            'data' => $record
        ], 201);
    }

    public function show(KpiRecord $kpiRecord)
    {
        return response()->json($kpiRecord->load(['kpi', 'department']));
    }

    public function update(Request $request, KpiRecord $kpiRecord)
    {
        $validatedData = $request->validate([
            'kpi_id' => 'sometimes|required|exists:kpis,id',
            'department_id' => 'sometimes|required|exists:departments,id',
            'recorded_value' => 'sometimes|required|numeric',
            'recorded_date' => 'sometimes|required|date',
            'notes' => 'nullable|string'
        ]);

        $kpiRecord->update($validatedData);

        return response()->json([
            'message' => 'Relevé mis à jour avec succès',
            'data' => $kpiRecord->load(['kpi', 'department'])
        ]);
    }

    public function destroy(KpiRecord $kpiRecord)
    {
        $kpiRecord->delete();

        return response()->json([
            'message' => 'Relevé supprimé avec succès'
        ]);
    }
}