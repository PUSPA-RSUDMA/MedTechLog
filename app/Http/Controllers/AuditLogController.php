<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', '%' . $request->string('model')->toString());
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('audit-logs.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
