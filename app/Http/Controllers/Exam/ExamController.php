<?php

namespace App\Http\Controllers\Exam;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Question;
use App\Models\Exams\Answer;
use App\Models\Categories\Category;
use App\Models\Takers\Taker;
use App\Models\Takers\Group;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Knowledge\Exam\Item\ItemType;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;

class ExamController extends Controller
{
    #[Post('/exam', name: 'exam.login')]
    public function login(Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $token = $request->input('token');
        
        // Handle DEMO token specially
        if ($token === 'DEMO') {
            return $this->handleDemoToken($request);
        }
        
        // Rest of login logic...
        $deliveryQuery = Delivery::query()
            ->whereHas('takers', function ($q) use ($token) {
                $q->where('token', $token);
            })
            ->with(['takers' => function ($q) use ($token) {
                $q->where('token', $token);
            }]);
        
        $delivery = $deliveryQuery->first();
        
        if (! $delivery) {
            return redirect()->back()->with('error', 'Invalid token');
        }
        
        $taker = $delivery->takers->first();
        
        Session::forget('exam');
        Session::put('exam', [
            'token' => $token,
            'taker' => $taker,
            'delivery' => $delivery,
            'admin' => null,
        ]);
        
        return redirect('/exam');
    }

    private function handleDemoToken(Request $request)
    {
        \Log::info('DEMO token received');
        
        $client = \App\Models\Client::find(1);
        if (!$client) {
            \Log::error('DEMO: No client with ID 1 found');
            return redirect()->back()->with('error', 'Demo not available - no client');
        }
        
        \Log::info('DEMO: Client found', ['client_id' => $client->id]);

        // Use the same unique token generation as before
        $baseToken = 'DEMO_' . now()->format('Ymd_His');
        $uniqueToken = $baseToken . '_' . uniqid();
        
        DB::beginTransaction();
        
        try {
            // Check/Create taker using firstOrCreate to avoid race conditions
            $taker = Taker::firstOrCreate(
                [
                    'email' => 'demo@example.com',
                    'client_id' => $client->id,
                ],
                [
                    'name' => 'Demo User',
                    'password' => bcrypt('demo123'),
                ]
            );
            
            \Log::info('DEMO: Taker ready', [
                'taker_id' => $taker->id,
                'was_recently_created' => $taker->wasRecentlyCreated
            ]);

            // Check/Create group
            $group = Group::firstOrCreate(
                ['name' => 'Demo Group', 'client_id' => $client->id],
                ['description' => 'Demo testing group']
            );

            // Attach taker to group if not already attached
            if (!$group->takers()->where('takers.id', $taker->id)->exists()) {
                $group->takers()->attach($taker->id);
            }

            // Check/Create exam
            $exam = Exam::firstOrCreate(
                ['name' => 'DEMO Comprehensive Medical Assessment', 'client_id' => $client->id],
                [
                    'code' => 'DEMO_EXAM_' . time(),
                    'description' => 'Interactive demonstration of our advanced medical examination platform features'
                ]
            );
            
            // Create rich demo content
            $this->createRichDemoContent($exam, $client);

            // Check/Create delivery
            $delivery = Delivery::firstOrCreate(
                [
                    'name' => 'Demo Medical Assessment Session',
                    'exam_id' => $exam->id,
                    'group_id' => $group->id,
                    'client_id' => $client->id,
                ],
                [
                    'is_anytime' => true,
                    'scheduled_at' => now()->subMinutes(10),
                    'ended_at' => now()->addHours(24),
                    'duration' => 120,
                    'automatic_start' => false,
                ]
            );

            // Attach taker to delivery with unique token
            $delivery->takers()->attach($taker->id, [
                'token' => $uniqueToken,
                'is_login' => false,
            ]);
            
            DB::commit();
            
            // Reload delivery to get updated status after taker attachment
            $delivery = $delivery->fresh();
            
            // Set session
            Session::forget('exam');
            Session::put('exam', [
                'token' => $uniqueToken,
                'taker' => $taker,
                'delivery' => $delivery,
                'admin' => null,
            ]);
            
            // Force session save
            Session::save();
            
            \Log::info('DEMO: Session set, redirecting to /exam', [
                'taker_id' => $taker->id,
                'delivery_id' => $delivery->id,
                'delivery_status' => $delivery->status,
                'session_id' => Session::getId(),
                'session_data' => Session::has('exam') ? 'exists' : 'missing'
            ]);
            
            return redirect('/exam');
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Demo creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create demo: ' . $e->getMessage());
        }
    }

    private function createRichDemoContent($exam, $client)
    {
        // Check if content already exists
        if ($exam->items()->count() > 0) {
            return;
        }

        // Create Medical Assessment category
        $category = Category::where('name', 'Medical Knowledge Assessment')
            ->where('client_id', $client->id)
            ->first();
            
        if (!$category) {
            $category = new Category();
            $category->name = 'Medical Knowledge Assessment';
            $category->client_id = $client->id;
            $category->description = 'Comprehensive medical knowledge evaluation';
            $category->save();
        }

        // Create 5 rich medical questions
        $items = [];

        // 1. Clinical Case - Multiple Choice
        $items[] = $this->createClinicalCaseMCQ($category->id, $client);
        
        // 2. Diagnostic Imaging Essay
        $items[] = $this->createDiagnosticEssay($category->id, $client);
        
        // 3. Emergency Medicine Scenario
        $items[] = $this->createEmergencyScenario($category->id, $client);
        
        // 4. Pharmacology Complex MCQ
        $items[] = $this->createPharmacologyMCQ($category->id, $client);
        
        // 5. Patient Management Essay
        $items[] = $this->createPatientManagementEssay($category->id, $client);

        // Attach items to exam with order
        $itemData = [];
        foreach ($items as $index => $item) {
            $itemData[$item->id] = ['order' => $index + 1];
        }
        $exam->items()->sync($itemData);
    }

    private function createClinicalCaseMCQ($categoryId, $client)
    {
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Clinical Case: Cardiovascular Assessment',
            'content' => '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: #2c3e50;">Clinical Vignette</h3>
                <p style="line-height: 1.8; color: #34495e;">A 58-year-old male patient presents to the emergency department with acute onset chest pain that began 2 hours ago while climbing stairs. The pain is described as "crushing" and radiates to his left arm and jaw.</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Vital Signs:</h4>
                <ul style="color: #34495e;">
                    <li>Blood Pressure: 165/95 mmHg</li>
                    <li>Heart Rate: 110 bpm, irregular</li>
                    <li>Respiratory Rate: 22 breaths/min</li>
                    <li>Temperature: 37.2°C (99°F)</li>
                    <li>SpO2: 94% on room air</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Past Medical History:</h4>
                <ul style="color: #34495e;">
                    <li>Type 2 Diabetes Mellitus (10 years)</li>
                    <li>Hypertension (15 years)</li>
                    <li>Dyslipidemia</li>
                    <li>30 pack-year smoking history</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Current Medications:</h4>
                <ul style="color: #34495e;">
                    <li>Metformin 1000mg BID</li>
                    <li>Lisinopril 20mg daily</li>
                    <li>Atorvastatin 40mg daily</li>
                </ul>
                
                <p style="line-height: 1.8; color: #34495e; margin-top: 15px;">ECG shows ST-segment elevation in leads II, III, and aVF. Troponin I is elevated at 2.5 ng/mL (normal < 0.04 ng/mL).</p>
            </div>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

        $item->categories()->attach($categoryId);

        $nextQuestionId = DB::table('questions')->max('id') + 1;
        $question = new Question([
            'item_id' => $item->id,
            'question' => 'Based on the clinical presentation, ECG findings, and laboratory results, what is the MOST appropriate immediate management for this patient?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
            'client_id' => $client->id,
        ]);
        $question->id = $nextQuestionId;
        $question->save();

        $answers = [
            ['answer' => 'Immediate percutaneous coronary intervention (PCI) with dual antiplatelet therapy, heparin, and beta-blocker', 'is_correct' => true],
            ['answer' => 'Thrombolytic therapy with tissue plasminogen activator (tPA) and admission to cardiac care unit', 'is_correct' => false],
            ['answer' => 'Conservative management with aspirin, clopidogrel, and cardiac monitoring for 24 hours', 'is_correct' => false],
            ['answer' => 'Urgent cardiac catheterization without antiplatelet therapy due to bleeding risk', 'is_correct' => false],
            ['answer' => 'Immediate coronary artery bypass grafting (CABG) surgery', 'is_correct' => false],
        ];

        foreach ($answers as $answerData) {
            $nextAnswerId = DB::table('answers')->max('id') + 1;
            $answer = new Answer([
                'question_id' => $question->id,
                'answer' => $answerData['answer'],
                'is_correct_answer' => $answerData['is_correct'],
            ]);
            $answer->id = $nextAnswerId;
            $answer->save();
        }

        return $item;
    }

    private function createDiagnosticEssay($categoryId, $client)
    {
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Diagnostic Imaging Interpretation',
            'content' => '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h3 style="color: #2c3e50;">Radiology Case Study</h3>
                <p style="line-height: 1.8; color: #34495e;">A 42-year-old female presents with progressive dyspnea over 3 months, dry cough, and unintentional weight loss of 8 kg.</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Chest X-Ray Findings:</h4>
                <ul style="color: #34495e;">
                    <li>Bilateral hilar lymphadenopathy</li>
                    <li>Reticulonodular infiltrates in upper lobes</li>
                    <li>No pleural effusion</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Laboratory Results:</h4>
                <ul style="color: #34495e;">
                    <li>Serum calcium: 11.2 mg/dL (elevated)</li>
                    <li>ACE level: 85 U/L (elevated)</li>
                    <li>ESR: 45 mm/hr</li>
                </ul>
                
                <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 20px;">
                    <p style="color: #856404; margin: 0;"><strong>Note:</strong> High-resolution CT scan shows perilymphatic nodules with upper lobe predominance and mediastinal lymphadenopathy.</p>
                </div>
            </div>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

        $item->categories()->attach($categoryId);

        $nextQuestionId = DB::table('questions')->max('id') + 1;
        $question = new Question([
            'item_id' => $item->id,
            'question' => 'Based on the clinical presentation, imaging findings, and laboratory results:

1. What is your most likely diagnosis? Provide your differential diagnosis (list at least 3 conditions).

2. Describe the pathophysiology of the most likely condition.

3. What additional diagnostic tests would you recommend to confirm the diagnosis?

4. Outline your initial treatment plan, including both pharmacological and non-pharmacological interventions.

5. What are the potential complications if this condition is left untreated?',
            'score' => 25,
            'type' => ItemType::ESSAY->value,
            'is_random' => false,
            'client_id' => $client->id,
        ]);
        $question->id = $nextQuestionId;
        $question->save();

        return $item;
    }

    private function createEmergencyScenario($categoryId, $client)
    {
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Emergency Medicine: Trauma Management',
            'content' => '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h3 style="color: #dc3545;">🚨 EMERGENCY SCENARIO</h3>
                <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;">
                    <p style="color: #721c24; font-weight: bold;">TIME: 14:32 - Motor Vehicle Accident</p>
                </div>
                
                <p style="line-height: 1.8; color: #34495e;">A 24-year-old male motorcyclist is brought to the trauma bay after a high-speed collision. He was not wearing a helmet.</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Primary Survey Findings:</h4>
                <ul style="color: #34495e;">
                    <li><strong>A (Airway):</strong> Patent, speaking in short sentences</li>
                    <li><strong>B (Breathing):</strong> Decreased breath sounds on right side, RR 28/min</li>
                    <li><strong>C (Circulation):</strong> BP 85/50 mmHg, HR 125 bpm, cool extremities</li>
                    <li><strong>D (Disability):</strong> GCS 13 (E3, V4, M6), pupils equal and reactive</li>
                    <li><strong>E (Exposure):</strong> Large contusion over right chest, deformity of right femur</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">FAST Exam:</h4>
                <p style="color: #34495e;">Positive for free fluid in hepatorenal recess and pelvis</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Chest X-Ray:</h4>
                <ul style="color: #34495e;">
                    <li>Right-sided pneumothorax (30%)</li>
                    <li>Multiple rib fractures (ribs 4-8 on right)</li>
                    <li>No mediastinal widening</li>
                </ul>
            </div>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

        $item->categories()->attach($categoryId);

        $nextQuestionId = DB::table('questions')->max('id') + 1;
        $question = new Question([
            'item_id' => $item->id,
            'question' => 'What is the CORRECT sequence of immediate interventions for this patient?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
            'client_id' => $client->id,
        ]);
        $question->id = $nextQuestionId;
        $question->save();

        $answers = [
            ['answer' => '1. Right chest tube insertion, 2. Two large-bore IV access with crystalloid resuscitation, 3. Type and cross-match blood, 4. Activate massive transfusion protocol, 5. Emergency laparotomy', 'is_correct' => true],
            ['answer' => '1. Immediate CT scan of head/chest/abdomen, 2. Neurosurgical consultation, 3. Chest tube if needed, 4. IV fluid resuscitation', 'is_correct' => false],
            ['answer' => '1. Endotracheal intubation, 2. Central line placement, 3. CT imaging, 4. Orthopedic consultation for femur fracture', 'is_correct' => false],
            ['answer' => '1. Emergency laparotomy, 2. Chest tube insertion during surgery, 3. Blood transfusion, 4. Femur stabilization', 'is_correct' => false],
        ];

        foreach ($answers as $answerData) {
            $nextAnswerId = DB::table('answers')->max('id') + 1;
            $answer = new Answer([
                'question_id' => $question->id,
                'answer' => $answerData['answer'],
                'is_correct_answer' => $answerData['is_correct'],
            ]);
            $answer->id = $nextAnswerId;
            $answer->save();
        }

        return $item;
    }

    private function createPharmacologyMCQ($categoryId, $client)
    {
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Clinical Pharmacology: Drug Interactions',
            'content' => '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h3 style="color: #2c3e50;">Medication Review Case</h3>
                
                <p style="line-height: 1.8; color: #34495e;">A 72-year-old woman with multiple comorbidities presents for medication review. She reports frequent dizziness and has had two falls in the past month.</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Current Medical Conditions:</h4>
                <ul style="color: #34495e;">
                    <li>Atrial fibrillation</li>
                    <li>Heart failure with reduced ejection fraction (EF 35%)</li>
                    <li>Type 2 diabetes mellitus</li>
                    <li>Chronic kidney disease (Stage 3, eGFR 45)</li>
                    <li>Depression</li>
                    <li>Osteoarthritis</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Current Medications:</h4>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #e9ecef;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Medication</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Dose</th>
                            <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Frequency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Warfarin</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">5 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Daily</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Digoxin</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">0.25 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Daily</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Metoprolol</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">50 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">BID</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Furosemide</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">40 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Daily</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Metformin</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">1000 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">BID</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Sertraline</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">100 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Daily</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">Ibuprofen</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">400 mg</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">TID PRN</td>
                        </tr>
                    </tbody>
                </table>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Recent Laboratory Values:</h4>
                <ul style="color: #34495e;">
                    <li>Serum potassium: 3.2 mEq/L (Low)</li>
                    <li>INR: 3.8 (Target 2-3)</li>
                    <li>Digoxin level: 2.1 ng/mL (Therapeutic: 0.5-2.0)</li>
                    <li>Creatinine: 1.8 mg/dL</li>
                </ul>
            </div>',
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_vignette' => true,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

        $item->categories()->attach($categoryId);

        $nextQuestionId = DB::table('questions')->max('id') + 1;
        $question = new Question([
            'item_id' => $item->id,
            'question' => 'Which combination of factors is MOST likely contributing to this patient\'s dizziness and falls?',
            'score' => 20,
            'type' => ItemType::MULTIPLE_CHOICE->value,
            'is_random' => false,
            'client_id' => $client->id,
        ]);
        $question->id = $nextQuestionId;
        $question->save();

        $answers = [
            ['answer' => 'Digoxin toxicity potentiated by hypokalemia and renal impairment, elevated INR from warfarin-NSAID interaction, and orthostatic hypotension from diuretic and beta-blocker combination', 'is_correct' => true],
            ['answer' => 'Metformin-induced lactic acidosis, sertraline-induced hyponatremia, and warfarin overdose', 'is_correct' => false],
            ['answer' => 'Heart failure exacerbation, uncontrolled atrial fibrillation, and diabetic neuropathy', 'is_correct' => false],
            ['answer' => 'Depression-related psychomotor retardation, osteoarthritis limiting mobility, and age-related vestibular dysfunction', 'is_correct' => false],
        ];

        foreach ($answers as $answerData) {
            $nextAnswerId = DB::table('answers')->max('id') + 1;
            $answer = new Answer([
                'question_id' => $question->id,
                'answer' => $answerData['answer'],
                'is_correct_answer' => $answerData['is_correct'],
            ]);
            $answer->id = $nextAnswerId;
            $answer->save();
        }

        return $item;
    }

    private function createPatientManagementEssay($categoryId, $client)
    {
        $nextItemId = DB::table('items')->max('id') + 1;
        $item = new Item([
            'title' => 'Comprehensive Patient Management Plan',
            'content' => '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h3 style="color: #2c3e50;">Complex Patient Management Scenario</h3>
                
                <p style="line-height: 1.8; color: #34495e;">You are the attending physician for a 45-year-old male patient admitted with diabetic ketoacidosis (DKA). This is his third admission in 6 months.</p>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Admission Presentation:</h4>
                <ul style="color: #34495e;">
                    <li>Blood glucose: 485 mg/dL</li>
                    <li>pH: 7.18, HCO3: 12 mEq/L</li>
                    <li>Ketones: 3+ in urine</li>
                    <li>Anion gap: 24</li>
                    <li>Potassium: 5.2 mEq/L</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Past Medical History:</h4>
                <ul style="color: #34495e;">
                    <li>Type 1 Diabetes Mellitus (diagnosed age 12)</li>
                    <li>Diabetic retinopathy</li>
                    <li>Peripheral neuropathy</li>
                    <li>Depression</li>
                    <li>Substance use disorder (alcohol, in recovery)</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Social History:</h4>
                <ul style="color: #34495e;">
                    <li>Recently lost job due to frequent absences</li>
                    <li>Going through divorce proceedings</li>
                    <li>Lives alone</li>
                    <li>Limited family support</li>
                    <li>No health insurance currently</li>
                </ul>
                
                <h4 style="color: #2c3e50; margin-top: 15px;">Current Insulin Regimen (when compliant):</h4>
                <ul style="color: #34495e;">
                    <li>Insulin glargine 24 units at bedtime</li>
                    <li>Insulin lispro sliding scale with meals</li>
                </ul>
                
                <div style="background-color: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin-top: 20px;">
                    <p style="color: #0c5460; margin: 0;"><strong>Nursing Notes:</strong> Patient admits to not checking blood sugars regularly and missing insulin doses due to cost. Has been rationing insulin supplies.</p>
                </div>
            </div>',
            'type' => ItemType::ESSAY->value,
            'is_vignette' => false,
            'is_random' => false,
        ]);
        $item->id = $nextItemId;
        $item->save();

        $item->categories()->attach($categoryId);

        $nextQuestionId = DB::table('questions')->max('id') + 1;
        $question = new Question([
            'item_id' => $item->id,
            'question' => 'Develop a comprehensive management plan for this patient that addresses:

1. **Acute Management** (10 points)
   - Outline your step-by-step approach to treating the current DKA episode
   - Include specific fluid, electrolyte, and insulin management protocols
   - Describe monitoring parameters and frequency

2. **Transition Planning** (5 points)
   - Explain how you will transition from IV to subcutaneous insulin
   - Provide criteria for safe transition

3. **Discharge Planning** (10 points)
   - Design a realistic discharge plan considering the patient\'s social and financial constraints
   - Include specific resources and support systems
   - Address medication access and affordability

4. **Long-term Management Strategy** (10 points)
   - Develop strategies to prevent readmissions
   - Address psychosocial factors affecting compliance
   - Propose a multidisciplinary team approach

5. **Patient Education** (5 points)
   - List key educational points for the patient
   - Describe how you would ensure understanding and retention

Please provide evidence-based recommendations with rationale for your choices.',
            'score' => 40,
            'type' => ItemType::ESSAY->value,
            'is_random' => false,
            'client_id' => $client->id,
        ]);
        $question->id = $nextQuestionId;
        $question->save();

        return $item;
    }

    #[Get('/exam/logout', name: 'exam.logout')]
    public function logout(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        Session::forget('exam');
        
        return redirect('/')->with('success', 'Logged out successfully');
    }
}