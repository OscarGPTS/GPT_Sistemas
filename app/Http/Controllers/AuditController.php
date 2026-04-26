<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');
        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%$action%");
        }
        if ($entity = $request->input('entity_type')) {
            $query->where('entity_type', 'like', "%$entity%");
        }
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        return view('audit.index', [
            'logs' => $query->latest()->paginate(30)->withQueryString(),
            'filters' => $request->only(['action', 'entity_type', 'user_id']),
        ]);
    }
}
