<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Models\Status;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role->role !== 'admin' && $user->role->role !== 'curator') {
            return response()->json([
                'error' => 'Unauthorized. Only admin or curator can view all reports.'
            ], 403);
        }

        $reports = Report::with(['status', 'categories'])
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

        $user = auth()->user();

        $report = new Report();
        $report->location = $request->location;
        $report->photo = $photoPath;
        $report->date = now();
        $report->status_id = 1;
        $report->user_id = $user->id;
        $report->save();

        $report->categories()->attach($request->category_id);

        return response()->json(
            $report->load(['categories', 'status']),
            201
        );
    }

    public function show($id)
    {
        $user = auth()->user();
        $report = Report::where('id', $id)
            ->with(['user', 'status', 'categories'])
            ->first();

        if (!$report) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        }

        if ($user->role->role !== 'admin' &&
            $user->role->role !== 'curator' &&
            $report->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized to view this report'
            ], 403);
        }

        return response()->json($report);
    }

    public function update(UpdateReportRequest $request, $id)
    {
        $report = Report::where('id', $id)->first();

        if (!$report) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        }

        $user = auth()->user();
        if ($user->role->role !== 'admin' &&
            $user->role->role !== 'curator' &&
            $report->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized to update this report'
            ], 403);
        }

        if ($request->hasFile('photo')) {
            if ($report->photo) {
                Storage::disk('public')->delete($report->photo);
            }
            $report->photo = $request->file('photo')->store('reports', 'public');
        }

        if ($request->has('location')) {
            $report->location = $request->location;
        }

        $report->save();

        if ($request->has('category_id')) {
            $report->categories()->sync($request->category_id);
        }

        return response()->json([
            'message' => 'Report updated successfully',
            'report' => $report->load(['categories', 'status'])
        ]);
    }

    public function updateStatus(UpdateReportStatusRequest $request, $id)
    {
        $user = auth()->user();
        if ($user->role->role !== 'admin' && $user->role->role !== 'curator') {
            return response()->json([
                'error' => 'Unauthorized. Only admin or curator can update status.'
            ], 403);
        }

        $report = Report::where('id', $id)
            ->with('status')
            ->first();

        if (!$report) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        }

        $statusOrder = [
            'pendente' => 1,
            'em resolução' => 2,
            'resolvido' => 3
        ];

        $currentStatus = $report->status;
        $newStatus = Status::where('id', $request->status_id)->first();

        if (!$newStatus) {
            return response()->json([
                'error' => 'Invalid status'
            ], 400);
        }

        $currentOrder = $statusOrder[$currentStatus->status];
        $newOrder = $statusOrder[$newStatus->status];

        if ($newOrder <= $currentOrder) {
            return response()->json([
                'error' => 'Invalid status progression. Status can only move forward: pendente -> em resolução -> resolvido',
                'current_status' => $currentStatus->status,
                'attempted_status' => $newStatus->status
            ], 400);
        }

        $report->status_id = $newStatus->id;
        $report->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'report' => $report->load('status')
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->role->role !== 'admin' && $user->role->role !== 'curator') {
            return response()->json([
                'error' => 'Unauthorized. Only admin or curator can delete reports.'
            ], 403);
        }

        $report = Report::where('id', $id)->first();

        if (!$report) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        }

        if ($report->photo) {
            Storage::disk('public')->delete($report->photo);
        }

        $report->delete();

        return response()->json([
            "message" => "Report deleted successfully"
        ]);
    }

    public function getUserOwnReports()
    {
        $user = auth()->user();
        $reports = Report::where('user_id', $user->id)
            ->with(['status', 'categories'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }
}
