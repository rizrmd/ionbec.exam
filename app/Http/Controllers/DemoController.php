<?php

namespace App\Http\Controllers;

use App\Models\Exams\Exam;
use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Attempts\Attempt;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;

class DemoController extends Controller
{
    #[Post('/demo/start', name: 'demo.start')]
    public function handleDemoCode(Request $request)
    {
        $code = strtoupper(trim($request->input('demo_code', '')));
        
        if ($code !== 'DEMO') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid demo code. Please enter "DEMO".'
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            // Get or create demo components
            $demoData = $this->setupDemoExam();
            
            // Create new delivery that starts immediately and lasts 5 minutes
            $delivery = $this->createFreshDemoDelivery($demoData);
            
            // Generate unique token for this session
            $token = 'DEMO-' . strtoupper(substr(md5(microtime() . rand()), 0, 8));
            
            // Attach taker to delivery with unique token
            $delivery->takers()->attach($demoData['taker']->id, [
                'token' => $token,
                'is_login' => false,
            ]);
            
            // Clean up any previous demo attempts for this taker
            $this->cleanupPreviousDemoAttempts($demoData['taker'], $demoData['exam']);
            
            DB::commit();
            
            // Set session data for exam access
            Session::put('exam', [
                'token' => $token,
                'taker' => $demoData['taker'],
                'delivery' => $delivery,
                'admin' => null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Demo exam initialized! Redirecting to exam...',
                'token' => $token,
                'redirect_url' => route('exam.main')
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize demo exam: ' . $e->getMessage()
            ], 500);
        }
    }

    private function setupDemoExam()
    {
        // Find or create demo client
        $client = Client::firstOrCreate([
            'name' => 'Demo Platform',
        ], [
            'description' => 'Demo client for showcasing platform capabilities',
        ]);

        // Find demo exam
        $exam = Exam::where('name', 'DEMO - Platform Showcase')
                   ->where('client_id', $client->id)
                   ->first();

        if (!$exam) {
            throw new \Exception('Demo exam not found. Please run: php artisan db:seed --class=DemoExamSeeder');
        }

        // Find demo group and taker
        $group = Group::where('name', 'Demo Users')
                     ->where('client_id', $client->id)
                     ->first();

        $taker = Taker::where('email', 'demo@platform.test')
                     ->where('client_id', $client->id)
                     ->first();

        if (!$group || !$taker) {
            throw new \Exception('Demo components not found. Please run: php artisan db:seed --class=DemoExamSeeder');
        }

        return [
            'client' => $client,
            'exam' => $exam,
            'group' => $group,
            'taker' => $taker,
        ];
    }

    private function createFreshDemoDelivery($demoData)
    {
        // Delete any existing demo deliveries to ensure clean start
        Delivery::where('name', 'LIKE', 'DEMO%')
                ->where('exam_id', $demoData['exam']->id)
                ->delete();

        // Create new delivery that starts immediately
        $delivery = Delivery::create([
            'name' => 'DEMO - Live Session (' . now()->format('H:i:s') . ')',
            'exam_id' => $demoData['exam']->id,
            'group_id' => $demoData['group']->id,
            'duration' => 5, // 5 minutes
            'automatic_start' => false, // Manual start for immediate access
            'scheduled_at' => now(),
            'last_status' => 'published',
            'client_id' => $demoData['client']->id,
        ]);

        return $delivery;
    }

    private function cleanupPreviousDemoAttempts($taker, $exam)
    {
        // Delete any previous demo attempts for this taker and exam
        // This ensures each demo session is completely fresh
        Attempt::where('attempted_by', $taker->id)
               ->where('exam_id', $exam->id)
               ->delete();
    }

    #[Get('/demo', name: 'demo.index')]
    public function showDemoPage()
    {
        return view('demo.index');
    }

    #[Get('/demo/stats', name: 'demo.stats')]
    public function getDemoStats()
    {
        try {
            $client = Client::where('name', 'Demo Platform')->first();
            if (!$client) {
                return response()->json(['stats' => null]);
            }

            $exam = Exam::where('name', 'DEMO - Platform Showcase')
                       ->where('client_id', $client->id)
                       ->first();

            if (!$exam) {
                return response()->json(['stats' => null]);
            }

            $stats = [
                'total_questions' => $exam->items()->count(),
                'question_types' => [
                    'multiple_choice' => $exam->items()->where('type', 'multiple-choice')->count(),
                    'essay' => $exam->items()->where('type', 'essay')->count(),
                    'interview' => $exam->items()->where('type', 'interview')->count(),
                ],
                'duration_minutes' => 5,
                'features_showcased' => [
                    'Rich HTML content with styling',
                    'Multiple choice with auto-scoring',
                    'Essay questions for detailed responses',
                    'Interview assessments',
                    'Timer-based examinations',
                    'Instant exam restart capability'
                ]
            ];

            return response()->json(['stats' => $stats]);

        } catch (\Exception $e) {
            return response()->json(['stats' => null, 'error' => $e->getMessage()]);
        }
    }
}