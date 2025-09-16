<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Models\Deliveries\Delivery;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Categories\Category;
use App\Models\Client;
use App\Knowledge\Exam\Item\ItemType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoExamSeeder extends Seeder
{
    public function run()
    {
        // Find or create demo client
        $demoClient = Client::firstOrCreate([
            'name' => 'Demo Platform',
        ], [
            'description' => 'Demo client for showcasing platform capabilities',
        ]);

        // Find or create demo category
        $demoCategory = Category::firstOrCreate([
            'name' => 'Demo Questions',
            'client_id' => $demoClient->id,
        ], [
            'description' => 'Demo questions showcasing platform capabilities',
        ]);

        // Create DEMO exam
        $demoExam = Exam::firstOrCreate([
            'name' => 'DEMO - Platform Showcase',
            'client_id' => $demoClient->id,
        ], [
            'description' => 'Demo exam showcasing all platform question types and capabilities',
        ]);

        // Create 5 different question types
        $items = [];

        // 1. Multiple Choice Question
        $mcItem = $this->createMultipleChoiceItem($demoCategory->id);
        $items[] = $mcItem;

        // 2. Essay Question
        $essayItem = $this->createEssayItem($demoCategory->id);
        $items[] = $essayItem;

        // 3. Interview Question
        $interviewItem = $this->createInterviewItem($demoCategory->id);
        $items[] = $interviewItem;

        // 4. Multiple Choice with Images
        $imageItem = $this->createImageMultipleChoiceItem($demoCategory->id);
        $items[] = $imageItem;

        // 5. Complex Scenario Essay
        $scenarioItem = $this->createScenarioEssayItem($demoCategory->id);
        $items[] = $scenarioItem;

        // Attach items to exam with order
        $itemData = [];
        foreach ($items as $index => $item) {
            $itemData[$item->id] = ['order' => $index + 1];
        }
        $demoExam->items()->sync($itemData);

        // Create Demo Group
        $demoGroup = Group::firstOrCreate([
            'name' => 'Demo Users',
            'client_id' => $demoClient->id,
        ], [
            'description' => 'Demo group for platform testing',
        ]);

        // Create Demo Taker
        $demoTaker = Taker::firstOrCreate([
            'name' => 'Demo User',
            'email' => 'demo@platform.test',
            'client_id' => $demoClient->id,
        ], [
            'password' => bcrypt('demo123'),
        ]);

        // Add taker to group
        $demoGroup->takers()->syncWithoutDetaching([$demoTaker->id => ['taker_code' => 'DEMO001']]);

        // Create DEMO delivery that auto-restarts
        $this->createDemoDelivery($demoExam, $demoGroup, $demoTaker);

        $this->command->info('Demo exam created successfully with 5 different question types!');
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
            'question' => 'Which of the following features does our platform support?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
        ]);

        $answers = [
            ['answer' => 'Multiple choice questions with automatic scoring', 'is_correct' => true],
            ['answer' => 'Essay questions with manual scoring', 'is_correct' => true],
            ['answer' => 'Timer-based examinations', 'is_correct' => true],
            ['answer' => 'Only basic text questions', 'is_correct' => false],
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
            'content' => '<h3>Essay Question</h3><p>This demonstrates our essay question capabilities with rich text support.</p>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'Describe the advantages of using an online examination platform over traditional paper-based exams. Discuss at least 3 key benefits and explain how they improve the examination experience for both instructors and students.',
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
            'content' => '<h3>Interview Question</h3><p>This demonstrates our interview assessment capabilities for comprehensive evaluation.</p>',
            'type' => ItemType::INTERVIEW->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'Practical Assessment: Walk us through how you would implement a secure authentication system for this examination platform. Consider security best practices, user experience, and scalability.',
            'score' => 30,
            'type' => ItemType::INTERVIEW->value,
            'is_random' => false,
        ]);

        return $item;
    }

    private function createImageMultipleChoiceItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Rich Media Support',
            'content' => '<h3>Rich Media Question</h3>
                         <p>Our platform supports rich media including images, videos, and complex formatting.</p>
                         <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 10px 0;">
                             <h4>🎯 Platform Capabilities:</h4>
                             <ul>
                                 <li>✅ HTML content support</li>
                                 <li>✅ Image and media embedding</li>
                                 <li>✅ Custom styling and formatting</li>
                                 <li>✅ Interactive elements</li>
                             </ul>
                         </div>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        $question = Question::create([
            'item_id' => $item->id,
            'question' => 'Based on the capabilities shown above, which statement best describes our platform\'s content support?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
        ]);

        $answers = [
            ['answer' => 'Supports rich HTML content with styling and media', 'is_correct' => true],
            ['answer' => 'Only supports plain text questions', 'is_correct' => false],
            ['answer' => 'Limited to basic formatting only', 'is_correct' => false],
            ['answer' => 'Requires external tools for media content', 'is_correct' => false],
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

    private function createScenarioEssayItem($categoryId)
    {
        $item = Item::create([
            'title' => 'Platform Feature: Complex Scenarios',
            'content' => '<h3>Real-World Scenario Assessment</h3>
                         <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin: 15px 0;">
                             <h4>🏢 Case Study: Educational Institution Migration</h4>
                             <p>A large university with 50,000 students is planning to migrate from paper-based examinations to a digital platform. They have the following requirements:</p>
                         </div>
                         <div style="background: #f8f9ff; padding: 15px; border-left: 4px solid #667eea; margin: 10px 0;">
                             <ul>
                                 <li><strong>Scale:</strong> Support 10,000 concurrent exam takers</li>
                                 <li><strong>Security:</strong> Prevent cheating and ensure data integrity</li>
                                 <li><strong>Flexibility:</strong> Support multiple question types and formats</li>
                                 <li><strong>Analytics:</strong> Detailed performance tracking and reporting</li>
                                 <li><strong>Integration:</strong> Connect with existing student information systems</li>
                             </ul>
                         </div>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);

        $item->categories()->attach($categoryId);

        Question::create([
            'item_id' => $item->id,
            'question' => 'As a platform architect, design a comprehensive solution for this university\'s digital examination needs. Your response should cover:

1. **Technical Architecture**: How would you design the system to handle the scale and performance requirements?

2. **Security Measures**: What security features would you implement to maintain exam integrity?

3. **Integration Strategy**: How would you integrate with their existing systems?

4. **User Experience**: How would you ensure the platform is user-friendly for both students and faculty?

Provide specific technical details and justify your design decisions.',
            'score' => 35,
            'type' => ItemType::ESSAY->value,
            'is_random' => false,
        ]);

        return $item;
    }

    private function createDemoDelivery($exam, $group, $taker)
    {
        // Delete any existing DEMO deliveries to ensure clean restart
        Delivery::where('name', 'LIKE', 'DEMO%')->delete();

        $delivery = Delivery::create([
            'name' => 'DEMO - Auto Restart (' . now()->format('M d, H:i') . ')',
            'exam_id' => $exam->id,
            'group_id' => $group->id,
            'duration' => 5, // 5 minutes
            'automatic_start' => false, // Manual start for immediate access
            'scheduled_at' => now(),
            'last_status' => 'published',
            'client_id' => $exam->client_id,
        ]);

        // Create unique token for DEMO access
        $token = 'DEMO-' . strtoupper(substr(md5(time()), 0, 8));
        
        $delivery->takers()->attach($taker->id, [
            'token' => $token,
            'is_login' => false,
        ]);

        // Create a simple access method - store the token in a known file for easy access
        file_put_contents(
            base_path('demo-token.txt'), 
            "DEMO Token: {$token}\nGenerated: " . now()->format('Y-m-d H:i:s') . "\n"
        );

        $this->command->info("DEMO delivery created with token: {$token}");
        $this->command->info("Token saved to: demo-token.txt");

        return $delivery;
    }
}