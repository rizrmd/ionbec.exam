<?php

namespace App\Http\Controllers;

use App\Models\Exams\Exam;
use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Attempts\Attempt;
use App\Models\Client;
use App\Models\Categories\Category;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Models\Exams\Item;
use App\Knowledge\Exam\Item\ItemType;
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
            $demoData = $this->setupDemoExam($request);
            
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

    private function setupDemoExam(Request $request)
    {
        // Get current client from request context (same way other controllers do)
        $client = $request->attributes->get('client');
        if (!$client) {
            throw new \Exception('No client context found. DEMO must be accessed from a client domain.');
        }

        // Create or get DEMO exam for this client
        $exam = $this->createDemoExamForClient($client);
        
        // Create or get demo group for this client
        $group = Group::firstOrCreate([
            'name' => 'DEMO Users',
            'client_id' => $client->id,
        ]);

        // Create or get demo taker for this client
        $taker = Taker::firstOrCreate([
            'name' => 'DEMO User',
            'email' => 'demo@' . ($client->slug ?? 'demo') . '.test',
            'client_id' => $client->id,
        ], [
            'password' => bcrypt('demo123'),
        ]);

        // Ensure taker is in the group
        $group->takers()->syncWithoutDetaching([$taker->id => ['taker_code' => 'DEMO001']]);

        return [
            'client' => $client,
            'exam' => $exam,
            'group' => $group,
            'taker' => $taker,
        ];
    }

    private function createDemoExamForClient($client)
    {
        // Find or create DEMO exam for this client
        $exam = Exam::firstOrCreate([
            'name' => 'DEMO - Platform Showcase',
            'client_id' => $client->id,
        ]);

        // If exam was just created, populate it with demo questions
        if ($exam->wasRecentlyCreated || $exam->items()->count() === 0) {
            $this->populateDemoExam($exam, $client);
        }

        return $exam;
    }

    private function populateDemoExam($exam, $client)
    {
        // Create demo category
        $category = Category::firstOrCreate([
            'name' => 'Demo Questions',
            'client_id' => $client->id,
        ]);

        // Create 5 different question types
        $items = [];

        // 1. Multiple Choice Question
        $items[] = $this->createMultipleChoiceItem($category->id);
        
        // 2. Essay Question  
        $items[] = $this->createEssayItem($category->id);
        
        // 3. Interview Question
        $items[] = $this->createInterviewItem($category->id);
        
        // 4. Multiple Choice with Rich Content
        $items[] = $this->createRichContentItem($category->id);
        
        // 5. Complex Scenario Essay
        $items[] = $this->createScenarioItem($category->id);

        // Attach items to exam with order
        $itemData = [];
        foreach ($items as $index => $item) {
            $itemData[$item->id] = ['order' => $index + 1];
        }
        $exam->items()->sync($itemData);
    }

    private function createMultipleChoiceItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Multiple Choice',
            'content' => '<h3>Multiple Choice Question</h3><p>This demonstrates our multiple choice capabilities with automatic scoring.</p>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        $question = Question::create([
            'item_id' => $item->id,
            'question' => 'Which features does our examination platform support?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
        ]);

        $answers = [
            ['answer' => 'Multiple choice questions with automatic scoring', 'is_correct' => true],
            ['answer' => 'Essay questions with rich text support', 'is_correct' => true], 
            ['answer' => 'Timer-based examinations with auto-submission', 'is_correct' => true],
            ['answer' => 'Only basic text-based questions', 'is_correct' => false],
        ];

        foreach ($answers as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer' => $answerData['answer'],
                'is_correct_answer' => $answerData['is_correct'],
            ]);
        }

        return $item;
    }

    private function createEssayItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Essay Questions',
            'content' => '<h3>Essay Question</h3><p>This demonstrates our essay question capabilities.</p>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'Explain the advantages of using an online examination platform. Discuss at least 3 key benefits and how they improve the examination experience.',
            'score' => 25,
            'type' => ItemType::ESSAY->value,
            'is_random' => false,
        ]);

        return $item;
    }

    private function createInterviewItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Interview Assessment', 
            'content' => '<h3>Interview Question</h3><p>This demonstrates our interview assessment capabilities.</p>',
            'type' => ItemType::INTERVIEW->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'Practical Assessment: How would you implement a secure authentication system for this examination platform? Consider security best practices and scalability.',
            'score' => 30,
            'type' => ItemType::INTERVIEW->value,
            'is_random' => false,
        ]);

        return $item;
    }

    private function createRichContentItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Rich Media Support',
            'content' => '<h3>🎨 Rich Media Question</h3>
                         <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin: 15px 0;">
                             <h4>🚀 Platform Capabilities:</h4>
                             <ul style="color: #f0f0f0;">
                                 <li>✅ HTML content with custom styling</li>
                                 <li>✅ Gradient backgrounds and rich formatting</li>
                                 <li>✅ Interactive visual elements</li>
                                 <li>✅ Responsive design support</li>
                             </ul>
                         </div>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        $question = Question::create([
            'item_id' => $item->id,
            'question' => 'Based on the rich content above, what can our platform handle?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
        ]);

        $answers = [
            ['answer' => 'Rich HTML content with custom CSS styling', 'is_correct' => true],
            ['answer' => 'Only plain text without formatting', 'is_correct' => false],
            ['answer' => 'Basic formatting with no visual elements', 'is_correct' => false],
            ['answer' => 'External tools required for rich content', 'is_correct' => false],
        ];

        foreach ($answers as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer' => $answerData['answer'],
                'is_correct_answer' => $answerData['is_correct'],
            ]);
        }

        return $item;
    }

    private function createScenarioItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Complex Scenarios',
            'content' => '<h3>📋 Real-World Scenario</h3>
                         <div style="background: #f8f9ff; padding: 20px; border-left: 4px solid #667eea; margin: 15px 0;">
                             <h4>🏫 Case Study: Educational Institution</h4>
                             <p>A university with 25,000 students wants to implement online examinations with these requirements:</p>
                             <ul>
                                 <li><strong>Scale:</strong> 5,000 concurrent users</li>
                                 <li><strong>Security:</strong> Anti-cheating measures</li>
                                 <li><strong>Flexibility:</strong> Multiple question formats</li>
                                 <li><strong>Integration:</strong> LMS connectivity</li>
                             </ul>
                         </div>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'Design a comprehensive solution for this university. Address: 1) Technical architecture for scale, 2) Security measures for integrity, 3) Integration strategy with existing systems, 4) User experience considerations. Provide specific recommendations.',
            'score' => 35,
            'type' => ItemType::ESSAY->value,
            'is_random' => false,
        ]);

        return $item;
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
        // Return static stats since demo exam is created on-demand
        $stats = [
            'total_questions' => 5,
            'question_types' => [
                'multiple_choice' => 2,
                'essay' => 2,
                'interview' => 1,
            ],
            'duration_minutes' => 5,
            'features_showcased' => [
                'Rich HTML content with custom styling',
                'Multiple choice with automatic scoring',
                'Essay questions for detailed responses', 
                'Interview assessments',
                'Complex scenario-based questions',
                'Timer-based examinations',
                'Instant exam restart capability',
                'Per-client dynamic exam creation'
            ]
        ];

        return response()->json(['stats' => $stats]);
    }
}