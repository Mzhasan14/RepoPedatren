<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
   public function index(Request $request)
{
    // Ambil log activity terbaru, paginasi 15 per page
    $logs = Activity::orderBy('created_at', 'desc')->paginate(15);

    // Ubah format agar kirim nama causer, bukan ID
    $logs->getCollection()->transform(function ($item) {
        return [
            'id'           => $item->id,
            'description'  => $item->description,
            'log_name'     => $item->log_name,
            'event'        => $item->event,
            // Ganti id jadi nama/username:
            'causer_username' => $item->causer ? ($item->causer->name ?? $item->causer->username ?? '-') : '-',
            'causer_type'  => $item->causer_type,
            'subject_id'   => $item->subject_id,
            'subject_type' => $item->subject_type,
            'properties'   => $item->properties,
            'batch_uuid'   => $item->batch_uuid,
            'created_at'   => $item->created_at->toDateTimeString(),
        ];
    });

    return response()->json($logs);
}

    // public function index(Request $request)
    // {
    //     try {
    //         $table = config('activitylog.table_name');
    //         $connection = config('activitylog.database_connection') ?? config('database.default');

    //         $query = DB::connection($connection)->table($table)
    //             ->select([
    //                 'id',
    //                 'log_name',
    //                 'description',
    //                 'event',
    //                 'causer_id',
    //                 'causer_type',
    //                 'subject_id',
    //                 'subject_type',
    //                 'properties',
    //                 'batch_uuid',
    //                 'created_at',
    //                 'updated_at'
    //             ]);

    //         // Optional Filters
    //         if ($request->filled('log_name')) {
    //             $query->where('log_name', $request->input('log_name'));
    //         }
    //         if ($request->filled('event')) {
    //             $query->where('event', $request->input('event'));
    //         }
    //         if ($request->filled('causer_id')) {
    //             $query->where('causer_id', $request->input('causer_id'));
    //         }
    //         if ($request->filled('causer_type')) {
    //             $query->where('causer_type', $request->input('causer_type'));
    //         }
    //         if ($request->filled('subject_id')) {
    //             $query->where('subject_id', $request->input('subject_id'));
    //         }
    //         if ($request->filled('subject_type')) {
    //             $query->where('subject_type', $request->input('subject_type'));
    //         }
    //         if ($request->filled('date_from')) {
    //             $query->whereDate('created_at', '>=', $request->input('date_from'));
    //         }
    //         if ($request->filled('date_to')) {
    //             $query->whereDate('created_at', '<=', $request->input('date_to'));
    //         }

    //         // Order by latest created
    //         $query = $query->orderByDesc('created_at');

    //         // Pagination
    //         $perPage = (int) $request->input('limit', 25);
    //         $currentPage = (int) $request->input('page', 1);

    //         $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
    //     } catch (\Throwable $e) {
    //         Log::error("[ActivityLogController] Error: {$e->getMessage()}");

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server',
    //         ], 500);
    //     }

    //     if ($results->isEmpty()) {
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Data kosong',
    //             'data' => [],
    //         ], 200);
    //     }

    //     // Format data jika ingin diubah, contoh sederhana: decode JSON properties
    //     $formatted = $results->map(function ($item) {
    //         if (is_string($item->properties)) {
    //             $item->properties = json_decode($item->properties, true);
    //         }
    //         return $item;
    //     });


    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Data ditemukan',
    //         'total_data' => $results->total(),
    //         'current_page' => $results->currentPage(),
    //         'per_page' => $results->perPage(),
    //         'total_pages' => $results->lastPage(),
    //         'data' => $formatted,
    //     ]);
    // }
}
