<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Log\ActivityLog;
use Carbon\Carbon;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('allowed:administrator');
    }

    #[Get('/back-office/dashboard', name: 'back-office.dashboard')]
    public function index(): Response
    {
        $deliveries = Delivery::query()->whereDate('scheduled_at', '>', Carbon::now())->limit(5)->get();
        
        // Fix relationships for ClientScope - load and set relations manually
        foreach ($deliveries as $delivery) {
            $exam = $delivery->exam;
            $group = $delivery->group;
            
            if ($exam) {
                $delivery->setRelation('exam', $exam);
            }
            if ($group) {
                $group->load('takers');
                $delivery->setRelation('group', $group);
            }
        }
        // Scope recent activity to the current tenant only. The activity_log table
        // has no client_id column, so filter by the causer's client; otherwise other
        // tenants' history leaks onto this dashboard (cross-tenant data exposure).
        $clientId = auth()->user()?->client_id;
        $logsQuery = ActivityLog::query()->with(['causer', 'subject'])->orderBy('created_at', 'DESC');
        if ($clientId) {
            $clientUserIds = \App\Models\Accounts\User::withoutGlobalScopes()
                ->where('client_id', $clientId)
                ->pluck('id');
            $logsQuery->where('causer_type', \App\Models\Accounts\User::class)
                ->whereIn('causer_id', $clientUserIds);
        }
        $logs = $logsQuery->limit(5)->get();

        return Inertia::render('BackOffice/Dashboard', [
            'countCategory' => Category::query()->count(),
            'countItem' => Item::query()->count(),
            'countQuestion' => Question::query()->count(),
            'countTest' => Exam::query()->count(),
            'upcomingDelivery' => $deliveries,
            'logs' => $logs,
        ]);
    }

}
