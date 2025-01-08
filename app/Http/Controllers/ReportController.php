<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Models\Status;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['status', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reports);
    }

    public function store(StoreReportRequest $request)
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports', 'public');
        }

        $report = new Report();
        $report->location = $request->location;
        $report->photo = $photoPath;
        $report->date = now();
        $report->status_id = 1;
        $report->user_id = auth()->id();
        $report->save();

        $report->categories()->attach($request->category_id);

        $report->load(['categories', 'status', 'user']);

        return response()->json($report, 201);
    }

    public function show(Report $report)
    {
        return response()->json($report->load(['user', 'status', 'category']));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        $user = auth()->user();
        if($report->user_id !== $user->id &&
            $user->role->role !== 'admin' &&
            $user->role->role !== 'curator') {
            return response()->json([
                'error' => 'Unauthorized to update this report'
            ], 403);
        }

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports', 'public');
            $report->photo = $photoPath;
        }

        if ($request->has('location')) {
            $report->location = $request->location;
        }

        if ($request->has('category_id')) {
            $report->category_id = $request->category_id;
        }

        $report->save();

        return response()->json([
            'message' => 'Report updated successfully',
            'report' => $report
        ]);
    }

    public function updateStatus(UpdateReportStatusRequest $request, Report $report)
    {
        $statusOrder = [
            'pendente' => 1,
            'em resolução' => 2,
            'resolvido' => 3
        ];

        $currentStatus = $report->status;
        $newStatus = Status::findOrFail($request->status_id);

        $currentOrder = $statusOrder[$currentStatus->status];
        $newOrder = $statusOrder[$newStatus->status];

        if ($newOrder <= $currentOrder) {
            return response()->json([
                'error' => 'Invalid status progression. Status can only move forward: pendente -> em resolução -> resolvido',
                'current_status' => $currentStatus->status,
                'attempted_status' => $newStatus->status
            ], 400);
        }

        $report->status_id = $request->status_id;
        $report->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'report' => $report->load('status')
        ]);
    }

    public function destroy(Report $report)
    {
        $user = auth()->user();
        if($user->role->role !== 'admin' && $user->role->role !== 'curator') {
            return response()->json([
                'error' => 'Unauthorized. Only admin or curator can delete reports.'
            ], 403);
        }

        $report->delete();

        return response()->json([
            "message" => "Report deleted successfully"
        ]);
    }

    public function getUserOwnReports()
    {
        $user_id = auth()->id();
        $reports = Report::where('user_id', $user_id)
            ->with(['user', 'status', 'category'])
            ->get();

        return response()->json($reports);
    }
}
