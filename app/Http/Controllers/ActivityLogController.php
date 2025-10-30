<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view logs');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::with(['causer', 'subject'])->latest();

            // FILTRO POR USUÁRIO
            if ($request->filled('user_id')) {
                $query->where('causer_id', $request->user_id)
                      ->where('causer_type', User::class);
            }

            // FILTRO POR DATA/HORA
            if ($request->filled('data_inicio')) {
                $query->where('created_at', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->where('created_at', '<=', $request->data_fim);
            }

            return DataTables::of($query)
                ->editColumn('created_at', function ($activity) {
                    return $activity->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('causer_name', function ($activity) {
                    return $activity->causer->name ?? 'Sistema';
                })
                ->addColumn('subject_info', function ($activity) {
                    if ($activity->subject) {
                        $type = class_basename($activity->subject_type);
                        return "{$type} ID: {$activity->subject_id}";
                    }
                    return 'N/A';
                })
                ->editColumn('properties', function ($activity) {
                    return '<pre>' . json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                })
                ->rawColumns(['properties'])
                ->make(true);
        }

        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.logs.index', compact('users'));
    }
}
