<?php

namespace App\Http\Controllers;

use App\Models\ExamSessionLog;
use App\Services\GeolocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExamLogController extends Controller
{
    protected $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    /**
     * Store exam log entry (API endpoint for frontend)
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'attempt_id' => 'nullable|integer|exists:attempts,id',
                'session_key' => 'required|string|max:255',
                'tab_count' => 'nullable|integer|min:1',
                'notes' => 'nullable|string',
                'event_type' => 'required|string|max:50'
            ]);

            // Get location data
            $locationData = $this->geolocationService->getLocation($request->ip());

            // Create log entry
            $log = ExamSessionLog::create([
                'attempt_id' => $validated['attempt_id'] ?? null,
                'session_key' => $validated['session_key'],
                'tab_count' => $validated['tab_count'] ?? 1,
                'ip_address' => $request->ip(),
                'country' => $locationData['country'] ?? null,
                'city' => $locationData['city'] ?? null,
                'isp' => $locationData['org'] ?? null,
                'user_agent' => $request->userAgent(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'log_id' => $log->id,
                'location' => $locationData,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create exam log', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create log entry'
            ], 500);
        }
    }

    /**
     * Display admin dashboard with exam logs
     */
    public function index(Request $request)
    {
        $query = ExamSessionLog::with(['attempt.taker'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->filled('suspicious_only') && $request->suspicious_only === 'true') {
            $query->where('tab_count', '>', 1);
        }

        $logs = $query->paginate(50);

        // Get statistics
        $stats = [
            'total_logs' => ExamSessionLog::count(),
            'multiple_tab_logs' => ExamSessionLog::where('tab_count', '>', 1)->count(),
            'unique_ips' => ExamSessionLog::distinct('ip_address')->count(),
            'today_logs' => ExamSessionLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.exam-logs', compact('logs', 'stats'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = ExamSessionLog::with(['attempt.taker'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->filled('suspicious_only') && $request->suspicious_only === 'true') {
            $query->where('tab_count', '>', 1);
        }

        $logs = $query->get();

        $filename = 'exam-logs-' . date('Y-m-d-H-i-s') . '.csv';
        $handle = fopen('php://memory', 'w');

        // CSV headers
        fputcsv($handle, [
            'ID',
            'Created At',
            'Candidate Name',
            'Attempt ID',
            'Session Key',
            'IP Address',
            'Country',
            'City',
            'ISP',
            'Tab Count',
            'User Agent',
            'Notes'
        ]);

        // CSV data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->attempt->taker->name ?? 'Unknown',
                $log->attempt_id,
                $log->session_key,
                $log->ip_address,
                $log->country,
                $log->city,
                $log->isp,
                $log->tab_count,
                $log->user_agent,
                $log->notes,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show detailed log entry
     */
    public function show(ExamSessionLog $log)
    {
        // Load with relationships
        $log->load(['attempt.taker']);

        return view('admin.exam-log-detail', compact('log'));
    }

    /**
     * Delete log entry
     */
    public function destroy(ExamSessionLog $log)
    {
        // Check permission
        if (!auth()->user()->canManageLogs()) {
            abort(403, 'You do not have permission to delete logs.');
        }

        try {
            $log->delete();

            return redirect()->route('admin.exam-logs')
                ->with('success', 'Log entry deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Failed to delete exam log', [
                'log_id' => $log->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.exam-logs')
                ->with('error', 'Failed to delete log entry.');
        }
    }

    /**
     * DEBUG: Debug admin access without middleware
     */
    public function debugAdmin()
    {
        $debugInfo = [
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => auth()->check() ? auth()->user()->email : 'Not authenticated',
            'user_name' => auth()->check() ? auth()->user()->name : 'Not authenticated',
            'is_admin_property' => auth()->check() ? (bool)auth()->user()->is_admin : 'Not authenticated',
            'admin_role_property' => auth()->check() ? (auth()->user()->admin_role ?? 'NULL') : 'Not authenticated',
            'is_admin_method' => auth()->check() ? auth()->user()->isAdmin() : 'Not authenticated',
            'can_manage_logs' => auth()->check() ? auth()->user()->canManageLogs() : 'Not authenticated',
            'has_full_admin_access' => auth()->check() ? auth()->user()->hasFullAdminAccess() : 'Not authenticated',
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'request_ip' => request()->ip(),
            'session_id' => session()->getId(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Check if admin user exists
        $adminUser = \App\Models\Accounts\User::where('email', 'admin@localhost.com')->first();
        $debugInfo['admin_user_exists'] = $adminUser ? true : false;

        if ($adminUser) {
            $debugInfo['admin_user_info'] = [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'is_admin' => (bool)$adminUser->is_admin,
                'admin_role' => $adminUser->admin_role ?? 'NULL',
                'email_verified_at' => $adminUser->email_verified_at?->toDateTimeString() ?? 'NULL',
                'model_is_admin' => $adminUser->isAdmin(),
                'model_can_manage_logs' => $adminUser->canManageLogs(),
            ];
        }

        // Check middleware registration
        $debugInfo['middleware_registered'] = app()->make('router')->getRoutes()->getRoutes();

        return response()->json([
            'debug_info' => $debugInfo,
            'message' => 'Debug information for admin access troubleshooting',
            'next_steps' => [
                '1. Check if user is authenticated',
                '2. Check if is_admin property is TRUE',
                '3. Check if isAdmin() method returns TRUE',
                '4. Run SQL: UPDATE users SET is_admin = true WHERE email = "admin@localhost.com"',
                '5. Clear browser cache and Laravel cache'
            ]
        ]);
    }
}