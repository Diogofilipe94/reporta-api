<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportDetail;
use App\Http\Requests\StoreReportDetailRequest;
use Illuminate\Http\Request;

class ReportDetailController extends Controller
{
    public function store(StoreReportDetailRequest $request, Report $report)
    {
        if($report->detail) {
            return response()->json([
                'error' => 'Report detail already exists'
            ], 400);
        }

        $detail = new ReportDetail();
        $detail->report_id = $report->id;
        $detail->technical_description = $request->technical_description;
        $detail->priority = $request->priority;
        $detail->resolution_notes = $request->resolution_notes;
        $detail->estimated_cost = $request->estimated_cost;
        $detail->save();

        return response()->json([
            'message' => 'Report detail created successfully',
            'detail' => $detail
        ], 201);
    }

    public function show(Report $report)
    {
        $detail = $report->detail;

        if(!$detail) {
            return response()->json([
                'error' => 'Report detail not found'
            ], 404);
        }

        return response()->json($detail);
    }

    public function update(StoreReportDetailRequest $request, Report $report)
    {
        $detail = $report->detail;

        if(!$detail) {
            return response()->json([
                'error' => 'Report detail not found'
            ], 404);
        }

        $detail->technical_description = $request->technical_description;
        $detail->priority = $request->priority;
        $detail->resolution_notes = $request->resolution_notes;
        $detail->estimated_cost = $request->estimated_cost;
        $detail->save();

        return response()->json([
            'message' => 'Report detail updated successfully',
            'detail' => $detail
        ]);
    }
}
