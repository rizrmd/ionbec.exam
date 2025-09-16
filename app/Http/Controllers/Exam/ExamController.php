<?php

namespace App\Http\Controllers\Exam;

use App\Models\Takers\Taker;
use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Illuminate\Support\Facades\DB;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Client;
use App\Models\Categories\Category;
use App\Models\Exams\Exam;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Models\Exams\Item;
use App\Models\Takers\Group;
use App\Models\Attempts\Attempt;
use App\Knowledge\Exam\Item\ItemType;
use Carbon\Carbon;

class ExamController extends Controller
{
    #[Post('/exam', name: 'exam.login')]
    public function index(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'token' => 'required',
        ]);

        // Handle DEMO token specially
        if (strtoupper(trim($request->token)) === 'DEMO') {
            return $this->handleDemoToken($request);
        }

        $deliveryTaker = DB::table('delivery_taker')->where('token', $request->token)->first();

        if ($deliveryTaker) {
            $taker = Taker::query()->where('id', $deliveryTaker->taker_id)->first();
            $delivery = Delivery::query()->where('id', $deliveryTaker->delivery_id)->first();
            $attempt = $taker->attempts()->where('delivery_id', $deliveryTaker->delivery_id)->first();

            //            if (Delivery::STATUS_ON_PROGRESS !== $delivery->last_status) {
            //                return redirect()->back()->withErrors(['exam' => 'Exam is not active.']);
            //            }

            // Check if exam is finished first (before login check)
            if (! is_null($attempt) && ! is_null($attempt->ended_at)) {
                Session::put('exam', [
                    'token' => $request->token,
                    'taker' => $taker,
                    'delivery' => $delivery,
                    'admin' => null,
                ]);
                return redirect()->route('exam.finished');
            }

            if ($deliveryTaker->is_login) {
                return redirect()->back()->withErrors(['exam' => 'Candidate is currently logged in']);
            }

            Session::put('exam', [
                'token' => $request->token,
                'taker' => $taker,
                'delivery' => $delivery,
                'admin' => null,
            ]);

            if (Delivery::STATUS_SCHEDULED === $delivery->last_status) {
                return redirect()->route('exam.waiting-room');
            }

            DB::table('delivery_taker')->where('token', $request->token)->update([
                'is_login' => true,
            ]);

            return redirect()->route('exam.main');
        }

        return redirect()->back()->withErrors(['token' => 'Token not found.']);
    }

    #[Get('/exam/logout', name: 'exam.logout')]
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $session = Session::get('exam');
        if (null != $session && array_key_exists('token', $session)) {
            DB::table('delivery_taker')->where('token', $session['token'])->update([
                'is_login' => false,
            ]);
            \Log::info('Logout: Reset is_login for token: ' . $session['token']);
        } else {
            // If no session, reset tokens that are logged in but have finished attempts
            // This is safe because finished exams shouldn't block re-entry
            $client = $request->attributes->get('client');
            if ($client) {
                $resetCount = DB::table('delivery_taker')
                    ->join('deliveries', 'delivery_taker.delivery_id', '=', 'deliveries.id')
                    ->join('attempts', 'attempts.delivery_id', '=', 'deliveries.id')
                    ->where('deliveries.client_id', $client->id)
                    ->where('delivery_taker.is_login', true)
                    ->whereNotNull('attempts.ended_at')
                    ->update(['delivery_taker.is_login' => false]);
                \Log::info('Logout: Reset ' . $resetCount . ' finished exam tokens for client: ' . $client->id);
            } else {
                \Log::warning('Logout: No exam session found and no client context');
            }
        }
        Session::forget('exam');
        
        // For client domains, redirect to home page instead of named route
        return redirect('/');
    }

    private function handleDemoToken(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Get current client from request context
            $client = $request->attributes->get('client');
            if (!$client) {
                return redirect()->back()->withErrors(['token' => 'DEMO must be accessed from a client domain.']);
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

            // Clean up any previous demo attempts for this taker
            Attempt::where('attempted_by', $taker->id)
                   ->where('exam_id', $exam->id)
                   ->delete();
            
            // Delete any existing demo deliveries to ensure clean start
            Delivery::where('name', 'LIKE', 'DEMO%')
                    ->where('exam_id', $exam->id)
                    ->delete();

            // Create new delivery that starts immediately and lasts 5 minutes
            $delivery = Delivery::create([
                'name' => 'DEMO - Live Session (' . now()->format('H:i:s') . ')',
                'exam_id' => $exam->id,
                'group_id' => $group->id,
                'duration' => 5, // 5 minutes
                'automatic_start' => false, // Manual start for immediate access
                'scheduled_at' => now(),
                'last_status' => 'published',
                'client_id' => $client->id,
            ]);

            // Generate unique token for this session
            $token = 'DEMO-' . strtoupper(substr(md5(microtime() . rand()), 0, 8));
            
            // Attach taker to delivery with unique token
            $delivery->takers()->attach($taker->id, [
                'token' => $token,
                'is_login' => false,
            ]);
            
            DB::commit();
            
            // Set session data for exam access
            Session::put('exam', [
                'token' => $token,
                'taker' => $taker,
                'delivery' => $delivery,
                'admin' => null,
            ]);
            
            // Redirect to main exam page
            return redirect()->route('exam.main');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['token' => 'Failed to initialize DEMO exam: ' . $e->getMessage()]);
        }
    }

    private function createDemoExamForClient($client)
    {
        // Try to find existing DEMO exam for this client first
        $exam = Exam::where('name', 'DEMO - Platform Showcase')
                   ->where('client_id', $client->id)
                   ->first();

        if (!$exam) {
            // If no exam exists, create one with explicit ID to avoid sequence issues
            $nextId = DB::table('exams')->max('id') + 1;
            $exam = new Exam([
                'name' => 'DEMO - Platform Showcase',
                'client_id' => $client->id,
                'code' => 'DEMO-' . strtoupper(substr(md5($client->id . 'demo'), 0, 6)),
            ]);
            $exam->id = $nextId;
            $exam->save();
        }

        // If exam was just created, populate it with demo questions
        if ($exam->wasRecentlyCreated || $exam->items()->count() === 0) {
            $this->populateDemoExam($exam, $client);
        }

        return $exam;
    }

    private function populateDemoExam($exam, $client)
    {
        // Try to find existing category first, or use a default one for this client
        $category = Category::where('client_id', $client->id)->first();
        
        if (!$category) {
            // If no category exists, create one with explicit ID to avoid sequence issues
            $nextCatId = DB::table('categories')->max('id') + 1;
            $category = new Category([
                'name' => 'DEMO Questions (' . now()->format('M d') . ')',
                'client_id' => $client->id,
            ]);
            $category->id = $nextCatId;
            $category->save();
        }

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
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Platform Feature: Multiple Choice',
            'content' => '<h3>Multiple Choice Question</h3><p>This demonstrates our multiple choice capabilities with automatic scoring.</p>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

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
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Platform Feature: Essay Questions',
            'content' => '<h3>Essay Question</h3><p>This demonstrates our essay question capabilities.</p>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

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
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Platform Feature: Interview Assessment', 
            'content' => '<h3>Interview Question</h3><p>This demonstrates our interview assessment capabilities.</p>',
            'type' => ItemType::INTERVIEW->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

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
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
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
        $item->id = $nextItemId;
        $item->save();

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
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
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
        $item->id = $nextItemId;
        $item->save();

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
}
