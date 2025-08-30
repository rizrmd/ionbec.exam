<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Accounts\User;
use App\Models\Exams\Exam;
use App\Models\Attempts\Attempt;
use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Inertia\Response;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Support\Facades\DB;

class RootDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('allowed:root');
    }

    #[Get('/back-office/root-dashboard', name: 'back-office.root-dashboard')]
    public function index(Request $request): Response
    {
        $clientStats = $this->getClientStatistics();
        $systemStats = $this->getSystemStatistics();
        $topClients = $this->getTopClientsStatistics();
        $recentActivity = $this->getRecentActivity();

        // Merge client and system stats into a single stats object
        $stats = array_merge($clientStats, $systemStats);

        return inertia('BackOffice/RootDashboard/Index', [
            'stats' => $stats,
            'topClients' => $topClients,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function getClientStatistics(): array
    {
        $totalClients = Client::count();
        $activeClients = Client::where('is_active', true)->count();
        $inactiveClients = $totalClients - $activeClients;

        // Calculate monthly growth
        $lastMonthClients = Client::where('created_at', '>=', now()->subMonth())->count();
        $monthlyGrowth = $totalClients > 0 ? round(($lastMonthClients / $totalClients) * 100, 1) : 0;

        return [
            'total_clients' => $totalClients,
            'active_clients' => $activeClients,
            'inactive_clients' => $inactiveClients,
            'activation_rate' => $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0,
            'monthly_growth' => $monthlyGrowth,
            'active_rate' => $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0,
        ];
    }

    private function getSystemStatistics(): array
    {
        $totalUsers = User::withoutGlobalScopes()->count();
        $activeUsers = User::withoutGlobalScopes()->whereNotNull('email_verified_at')->count();
        $totalExams = Exam::withoutGlobalScopes()->count();
        $publishedExams = Exam::withoutGlobalScopes()->where('is_published', true)->count();
        $totalAttempts = Attempt::withoutGlobalScopes()->count();
        $completedAttempts = Attempt::withoutGlobalScopes()->whereNotNull('finished_at')->count();
        $totalTakers = Taker::withoutGlobalScopes()->count();
        $totalClients = Client::count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_exams' => $totalExams,
            'published_exams' => $publishedExams,
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'total_takers' => $totalTakers,
            'avg_users_per_client' => $totalClients > 0 ? round($totalUsers / $totalClients, 1) : 0,
            'avg_exams_per_client' => $totalClients > 0 ? round($totalExams / $totalClients, 1) : 0,
            'completion_rate' => $totalAttempts > 0 ? round(($completedAttempts / $totalAttempts) * 100, 1) : 0,
        ];
    }

    private function getTopClientsStatistics(): array
    {
        $topClientsByUsers = Client::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(function ($client) {
                return [
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'users_count' => $client->users_count,
                ];
            })
            ->toArray();

        $topClientsByExams = Client::withCount('exams')
            ->orderBy('exams_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(function ($client) {
                return [
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'exams_count' => $client->exams_count,
                ];
            })
            ->toArray();

        $topClientsByAttempts = Client::select('clients.*')
            ->selectRaw('(SELECT COUNT(*) FROM attempts WHERE attempts.client_id = clients.id) as attempts_count')
            ->orderBy('attempts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($client) {
                return [
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'attempts_count' => $client->attempts_count,
                ];
            })
            ->toArray();

        return [
            'byUsers' => $topClientsByUsers,
            'byExams' => $topClientsByExams,
            'byAttempts' => $topClientsByAttempts,
        ];
    }

    private function getRecentActivity(): array
    {
        $recentClients = Client::latest()
            ->limit(5)
            ->get(['id', 'name', 'slug', 'created_at'])
            ->map(function ($client) {
                return [
                    'type' => 'client',
                    'name' => $client->name,
                    'slug' => $client->slug,
                    'action' => 'Client created',
                    'timestamp' => $client->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $recentUsers = User::withoutGlobalScopes()
            ->with('client:id,name,slug')
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'client_id', 'created_at'])
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'name' => $user->name,
                    'email' => $user->email,
                    'client' => $user->client ? $user->client->name : 'System',
                    'action' => 'User registered',
                    'timestamp' => $user->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $recentExams = Exam::withoutGlobalScopes()
            ->with('client:id,name,slug')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'client_id', 'created_at'])
            ->map(function ($exam) {
                return [
                    'type' => 'exam',
                    'name' => $exam->title,
                    'client' => $exam->client ? $exam->client->name : 'System',
                    'action' => 'Exam created',
                    'timestamp' => $exam->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $recentAttempts = Attempt::withoutGlobalScopes()
            ->with(['attemptedBy:id,name', 'delivery.exam:id,title'])
            ->latest()
            ->limit(5)
            ->get(['id', 'attempted_by', 'delivery_id', 'created_at', 'finished_at'])
            ->map(function ($attempt) {
                return [
                    'type' => 'attempt',
                    'name' => $attempt->attemptedBy ? $attempt->attemptedBy->name : 'Unknown',
                    'exam' => $attempt->delivery && $attempt->delivery->exam ? $attempt->delivery->exam->title : 'Unknown',
                    'action' => $attempt->finished_at ? 'Attempt completed' : 'Attempt started',
                    'timestamp' => $attempt->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        // Return structured data as expected by the Vue component
        return [
            'clients' => $recentClients,
            'users' => $recentUsers,
            'exams' => $recentExams,
            'attempts' => $recentAttempts,
        ];
    }
}