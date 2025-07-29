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
        $delivery = Delivery::query()->with(['exam', 'group.takers'])->whereDate('scheduled_at', '>', Carbon::now())->limit(5)->get();
        $logs = ActivityLog::query()->with(['causer', 'subject'])->orderBy('created_at', 'DESC')->limit(5)->get();

        return Inertia::render('BackOffice/Dashboard', [
            'countCategory' => Category::query()->count(),
            'countItem' => Item::query()->count(),
            'countQuestion' => Question::query()->count(),
            'countTest' => Exam::query()->count(),
            'upcomingDelivery' => $delivery,
            'logs' => $logs,
            'serverInfo' => $this->getServerInformation(),
        ]);
    }

    /**
     * Get Total CPU and usage, Total RAM and usage, and Total Disk and usage information.
     *
     * @return void
     */
    private function getServerInformation(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
        ];
    }

    private function getCpuUsage()
    {
        $cpuCount = shell_exec("grep -c ^processor /proc/cpuinfo");
        $cpuUsage = sys_getloadavg();
        $cpuUsagePercent = round($cpuUsage[0] * 100 / $cpuCount, 2);

        return [
            'count' => $cpuCount,
            'usage' => $cpuUsagePercent
        ];
    }

    private function getRamUsage()
    {
        $ramInfo = shell_exec("free -m");
        $ramInfo = explode("\n", $ramInfo);
        $ramInfo = preg_split('/\s+/', $ramInfo[1]);
        $totalRam = $ramInfo[1];
        $usedRam = $ramInfo[2];
        $freeRam = $ramInfo[3];

        return [
            'total' => round($totalRam / 1024, 2),
            'used' => round($usedRam / 1024, 2),
            'free' => round($freeRam / 1024, 2)
        ];
    }

    private function getDiskUsage()
    {
        $diskInfo = shell_exec("df -h");
        $diskInfo = explode("\n", $diskInfo);
        $diskInfo = preg_split('/\s+/', $diskInfo[1]);
        $totalDisk = $diskInfo[1];
        $usedDisk = $diskInfo[2];
        $freeDisk = $diskInfo[3];

        return [
            'total' => $totalDisk,
            'used' => $usedDisk,
            'free' => $freeDisk
        ];
    }
}
