<?php

namespace Database\Seeders;

use App\Models\Exams\Item;
use App\Models\Exams\Answer;
use App\Models\Exams\Question;
use Illuminate\Database\Seeder;
use App\Models\Categories\Category;

/**
 * Items table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class ItemsTableSeeder extends Seeder
{
    /**
     * Seeder data.
     *
     * @var array
     */
    protected $data = [
        [
            'title' => 'Radionucleide(Tc) bone scanning',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Radionucleide(Tc) bone scanning is most useful in: ',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Avascular necrosis',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Malignancy',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Rheumatoid arthritis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Stress fractures',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Acute osteomyelitis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following is incorrect about dislocation of lunate',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following is <strong>incorrect</strong> about dislocation of lunate:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Dislocated lunate appears triangular instead of rectangular on A.P. x-ray',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Dislocation is most easily recognised on lateral view x-ray',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Avascular necrosis is common following dislocation ',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Lunate dislocates posteriorly',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Median nerve compression can occur',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Earliest laboratory finding in a case of fat embolism',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following is the earliest laboratory finding in a case of fat embolism:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased serum cholestrol',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased serum lipase',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased serum fatty acids',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Lipuria',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased alkaline phosphatase.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Regarding high tibia osteotomy (HTO), which statement is most appropriate?',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Regarding high tibia osteotomy (HTO), which statement is most appropriate?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'These days, closed HTO is more popular compared to open HTO',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Open HTO, the cutting is from lateral',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'HTO procedure usually is followed with supracondylar osteotomy of femur',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Open HTO is better for future total knee procedure, compared to closed HTO',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'HTO is usually indicated for the treatment of valgus knee for the younger patient',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'The discoid meniscus of the knee',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'The discoid meniscus of the knee:',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'The lateral discoid meniscus is more common compared to medial discoid meniscus',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'The discoid meniscus is bigger and stronger compared to normal meniscus',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Asymptomatic discoid meniscus needs reshaping mimicking a normal meniscus',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Usually found in older people',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Easy to diagnose without MRI',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 23 female sprinter complaint about pain at her plantar pedis. The orthopaedic surgeon make plantar fasciitis as diagnosis',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A 23 female sprinter complaint about pain at her plantar pedis. The orthopaedic surgeon make plantar fasciitis as diagnosis',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'There is no role for ESWT (electro shock wave therapy)',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Surgery is the gold standard',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'She may not run in the future',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Usually magnetic resonance imaging is important to make the diagnosis',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'The athlete should be encourage to wear soft base shoes',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'This is a procedure in shoulder arthroscopy of the right shoulder.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'This is a procedure in shoulder arthroscopy of the right shoulder.<br><img src="/example/figure-1.jpg"  alt="figure-1"/><br>The most appropriate statement:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'This is a rotator cuff repair',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'This is a surgery for impingement syndrome that cannot be treated conservativery',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'This is a Bankart repair',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'This is a frozen shoulder surgery after capsular release',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'This is a reconstruction of glenoid avulsion',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Figure A is the radiograph of a previously active patient with pain in her lower lumbar spine region and lateral hip 6 months after a cementless hip arthroplasty.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Figure A is the radiograph of a previously active patient with pain in her lower lumbar spine region and lateral hip 6 months after a cementless hip arthroplasty. What is the most likely cause of this patient’s symptoms?<br><table><tbody><tr><td><img src="/example/figure-2.jpg"></td><td><img src="/example/figure-3.jpg"></td></tr><tr><td class="text-center">Figure A</td><td class="text-center">Figure B</td></tr></tbody></table>',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased hip joint offset',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased leg length ',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Increased hip joint offset and leg length',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Increased leg length but no increase in offset ',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Figure a through d are the radiograph and CT scans of a 45-year-old man who fell 10 feet from a ladder and sustained to the right knee.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Figure a through d are the radiograph and CT scans of a 45-year-old man who fell 10 feet from a ladder and sustained to the right knee. Examination reveals no open wounds and the skin was in good condition with moderate swelling and no fracture blisters. The patient is neurovascularly intact. What is the most appropriate treatment?<br><table><tbody><tr><td><img src="/example/figure-4.jpg"></td><td><img src="/example/figure-5.jpg"></td><td><img src="/example/figure-6.jpg"></td><td><img src="/example/figure-7.jpg"></td></tr><tr><td class="text-center">Figure A</td><td class="text-center">Figure B</td><td class="text-center">Figure C</td><td class="text-center">Figure D</td></tr></tbody></table>',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Hinged knee brace and non-weight-bearing for 6 weeks',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Percutaneous screw fixation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Open reduction and internal fixation with a laterally applied nonlocking plate',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Open reduction and internal fixation with posteromedial and lateral  plates via one anterior approach',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Open reduction and internal fixation with posteromedial and lateral plates via dual incisions.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 42-year-old woman has a 3-weeks history of acute lower back pain with radiation into the left lower extremity.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A 42-year-old woman has a 3-weeks history of acute lower back pain with radiation into the left lower extremity. There is no history of trauma and no systemic symptoms are noted. Examination reveals a positive straight leg test at 25 degrees on the left side. Motor testing reveals mild weakness of the fluteus maximus and weakness of the gastrocnemius at 3/5. Sensory examination reveals decreased sensation along the lateral aspect of the foot. Knee reflex is intact; however, the ankle reflex is absent. MRI scans show a posterolateral disk herniation. The diagnosis at this time is consistent with a herniated nucleus pulposus at what level?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'L1-2',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'L2-3',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'L3-4',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'L4-5',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'L5-S1',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 32-year-old man sustains an illiac wing fracture and a Bilateral femur fracture.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A 32-year-old man sustains an illiac wing fracture and a Bilateral femur fracture. 4-hours later he has shortness of breath with hypoxic, and confusion. A chest radiograph is normal. What is the most likely diagnosis?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Fat emboli syndrome',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Adult respiratory distress syndrome',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Pulmonary embolism',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Tension pneumothorax',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Sepsis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A patient with a left-sided C6-7 herniated nucleous pulposus would likely have which of the following constellation of findings?',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A patient with a left-sided C6-7 herniated nucleous pulposus would likely have which of the following constellation of findings?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Pain into the thumb, triceps weakness, and loss of triceps reflex',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Middle finger numbness, wrist extensor weakness and diminished brachioradialis reflex.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Thumb numbness, wrist extensor weakness, and diminished brachioradialis reflex.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Middle finger numbness, triceps weakness, and loss of biceps reflex.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Middle finger numbness, triceps weakness, and loss of triceps reflex.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following is the most common primary soft tissue malignancy of the foot?',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following is the most common primary soft tissue malignancy of the foot?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Malignant melanoma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Osteosarcoma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Squamous cell carcinoma.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Synovial cell sarcoma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Chondrosarcoma.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 55-year-old male is diagnosed with a dedifferentiated chondrosarcoma of the femur which appears on MRI to have a significant extraosseous component.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A 55-year-old male is diagnosed with a dedifferentiated chondrosarcoma of the femur which appears on MRI to have a significant extraosseous component. Distant staging has not revealed any metastases. What surgical stage would be assigned to this tumour according to the system of the Musculoskeletal Tumour Society (MSTS)?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'IA.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'IB.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'IIA.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'IIB.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'IIIB.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following lesions is least likely to affect the epiphysis?',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following lesions is least likely to affect the epiphysis?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Clear cell chondrosarcoma.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Chondromyxoid fibroma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Giant cell tumour.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Osteomyelitis.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Chondroblastoma.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 70-year-old male presents with weight loss, fatigue and back pain.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'A 70-year-old male presents with weight loss, fatigue and back pain. Examination reveals hepatomegaly and axillary lymphadenopathy. Blood tests show a raised calcium and erythrocyte sedimentation rate. Serum protein electrophoresis reveals an M-protein spike, with an elevated serum IgM level. Urinary Bence-Jones proteins are detected. Radiographs show lytic lesions in the T6, T8 and T9 vertebrae. These are cold on bone scan. Biopsy reveals intensely eosinophilic plasma cells. Which of the following is the most likely diagnosis?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Multiple myeloma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Plasmacytoma.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'POEMS (polyneuropathy, organomegaly, endocrinopathy, monoclonal gammopathy and skin changes) syndrome.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Waldenström macroglobulinaemia.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Monoclonal gammopathy of undetermined significance.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following is true regarding Ollier’s disease?',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following is true regarding Ollier’s disease?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Autosomal dominant.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Sarcomatous degeneration occurs in 30% of patients.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Hand involvement is an uncommon feature.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Incidence of conversion to chondrosarcoma is similar to Mafucci’s syndrome.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Onset is usually in adulthood.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following is commonest material used to make Orthopaedic implant',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following is commonest material used to make Orthopaedic implant:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Titanium',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Stainless steel',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Polyethylene (UHMWPE)',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Methyl-methacrylate.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Carbon.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Fracture disease can be prevented by:',
            'content' => null,
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Fracture disease can be prevented by:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Plaster immobilization of fracture',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Cast brace treatment of fracture',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Internal fixation of fracture',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'External fixation of fracture.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Physiotherapy (Early Mobilization).',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'An avid golfer presents to your clinic with sudden onset of pain at the base of his hypothenar eminence when he accident ally struck the ground mid swing.',
            'content' => 'An avid golfer presents to your clinic with sudden onset of pain at the base of his hypothenar eminence when he accident ally struck the ground mid swing. You suspect a fracture of the hook of the hamate. Routine PA and lateral x-rays do not reveal any fracture.',
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following views would you then ask for?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Notch view',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Grip view',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Carpal tunnel view',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Ulnar -deviated PA view.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Roberts view.',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'X-rays are still inconclusive. Which of the following imaging modalities is most sensitive in detecting acute fractures?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'MRI',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'CT scan',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'CT arthrogram',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Ultrasound scan.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'X-ray tomography.',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'A fracture of the hook of hamate is confirmed. You decide to proceed with open reduction and internal fixation with a headless screw. Which structure is at risk of injury during surgery?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Motor branch of median nerve',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Deep branch of ulnar nerve',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Nerve of Henle',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Common digital nerve to the fourth web.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Palmar cutaneous branch of the median nerve.',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'Which of the following structures is principally inserted into the hook of the hamate?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Capitato-hamate ligament',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Ligament of Testut',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Naviculo-hamate ligament',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Piso-hamate ligament',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Hamato-lunate ligament',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 17-year-old male sustained an injury to his middle finger while catching a football.',
            'content' => 'A 17-year-old male sustained an injury to his middle finger while catching a football. When examined by his coach on the field, it was felt to be a sprain, and he continued playing the game. After he finished the game, he was noted to have a finger bent at the distal inter phalangeal joint, and he was unable to straighten it (Fig.). He was seen in the emergency room where x-ray showed no fracture. He was then splinted and asked to see you in consultation<br><img src="/example/figure-8.jpg">',
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'The most likely diagnosis in this situation is:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'DIP joint dislocation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Flexor profundus avulsion',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Mallet finger',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Sprain of the DIP joint.',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'The most appropriate management at this time would be which of the following?',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'Application of a tip protector splint maintaining the DIP joint at neutral and the PIP joint free',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Closed reduction and percutaneous pin fixation of the DIP joint',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Open repair of the extensor mechanism',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Open repair and pinning of the DIP joint.',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'You are the sole provider in a small rural hospital when a 56-year -old female is brought in after a Motor Vehicle rollover.',
            'content' => 'You are the sole provider in a small rural hospital when a 56-year -old female is brought in after a Motor Vehicle rollover. The patient is brought on a backboard in a cervical collar by Emergency Medical Service who reports a prolonged extrication. She has an obviously deformed left thigh and is bleeding from a scalp wound.',
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'What is the first step in assessing the patient upon arrival?:',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Put direct pressure on the scalp wound',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Have someone apply direct in-line traction to the left lower extremity to stabilize the fracture',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Call the closest trauma center to prepare for transport of the patient.',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Ask the patient to state her name.',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'What amount of blood loss has she most likely sustained?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => '10%',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => '25%',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => '40%',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => '55%',
                        ],
                    ],
                ], [
                    'type' => 'multi-choice',
                    'question' => 'What is the most appropriate fluid resuscitation to initiate at this time?',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Give 1 L of crystalloid at 200 cc/hr',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Administer 2 Litre of crystalloid as a fluid bolus. Alert the blood bank for possible transfusion needs',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Give 1 unit PRBCs now, alert the blood bank to start bringing platelets and plasma',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'D.	Monitor her urine output and administer fluid to keep UOP at 1 to 2 cc/kg/hr',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A one year old baby came to your clinic with a polydactyly in his hand',
            'content' => 'A one year old baby came to your clinic with a polydactyly in his hand<br><table><tbody><tr><td><img src="/example/figure-9.jpg"></td><td><img src="/example/figure-10.jpg"></td></tr></tbody></table>',
            'questions' => [
                [
                    'type' => 'multi-choice',
                    'question' => 'What is your classification',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Bayne and Klug II',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Bayne and Klug IV',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Wassel II',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Wassel IV',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Wassel V',
                        ],
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'How do you manage this deformity',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Excise the ulnar thumb with collateral ligament reconstruction',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Bilhaut Cloquet',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Modified Bilhaut Cloquet',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Excise the radial thumb with collateral ligament reconstruction',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Excise the ulnar thumb and arthrodesis',
                    ],
                ],
            ],
        ], [
            'title' => 'Radiograph of a 30-year-old male fell while he was out for a run.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'question' => '<img src="/example/essay-1.jpg"><br><br>Radiograph of a 30-year-old male fell while he was out for a run. He landed awkwardly on his left thenar eminence sustaining an injury to this area.<br><br>1. The most likely diagnosis is?<br>2. What is the deforming forces on the metacarpal?<br>3. List 3 option of treatment?',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Female/ 11 yo with chief complaint severe painfull mass on right distal femur with ulcer since 4 months ago, and, no history of trauma',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'question' => '<img src="/example/essay-2a.jpg"><br><img src="/example/essay-2b.jpg"><br><img src="/example/essay-2c.jpg"><br><img src="/example/essay-2d.jpg"><br>Female/ 11 yo with chief complaint severe painfull mass on right distal femur with ulcer since 4 months ago, and, no history of trauma. Laboratory: Hb 11,4; WBC 15,1; LED 50; SGOT/SGPT 83/33; BUN/SK 7/0,5; ALP 345; Ca 9,4; P 4,2. Radiology and biopsy already done.<br><br>1.Please describe what you find from clinical and/or radiological picture! <br>2. Please describe what you find from histopathological picture! <br>3. What is your complete diagnosis? <br>4. How do you manage this patient?',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Burst fracture: (BIOMECHANIC)',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'question' => '<img src="/example/essay-3.jpg"><br>Burst fracture: (BIOMECHANIC)<br><br>1.When is considered as stable fracture ?  <br>2. When you consider  the burst fracture are unstable ?  <br>3. What is the best treatment of choice for stable burst fracture?',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'A 3 and a half  year old boy came with a complain of right lower extremity bow legged which has been worsened over years.',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'question' => '<img src="/example/essay-4.jpg"><br>A 3 and a half  year old boy came with a complain of right lower extremity bow legged which has been worsened over years. There is no sign inflamation or significant trauma to the area. The boy has also a problem with his body weight. He was an early walker as well.<br><br>1.What is your clinical diagnosis ?  <br>2. What classification do you use for this disease and how many type ?  <br>3. How do you manage this problem?',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => '(untitled)',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'question' => '<img src="/example/essay-5.jpg"><br><br>1. Described the term of Stress? <br>2. Described the term of Strain?  <br>3. Describe the definition of Yield strength? <br> 4. Describe the definition of Elastic modulus',
                    'answers' => [],
                ],
            ],
        ],
    ];

    /**
     * Run seeder.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::query()->where('name', '=', 'Unspecified')->get()->keyBy('id');
        $ids = $categories->keys()->all();

        foreach ($this->data as $datum) {
            $item = new Item();
            $item->fill([
                'title' => $datum['title'],
                'content' => $datum['content'],
            ]);
            $item->save();
            $item->categories()->sync($ids);

            foreach ($datum['questions'] as $_question) {
                $question = new Question();
                $question->fill([
                    'item_id' => $item->id,
                    'type' => $_question['type'],
                    'question' => $_question['question'],
                ]);

                $question->save();

                foreach ($_question['answers'] as $_answer) {
                    $answer = new Answer();
                    $answer->fill([
                        'question_id' => $question->id,
                        'is_correct_answer' => $_answer['is_correct_answer'],
                        'answer' => $_answer['answer'],
                    ]);
                    $answer->save();
                }
            }
        }
    }
}
