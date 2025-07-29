<?php

namespace Database\Seeders;

use App\Models\Exams\Exam;
use App\Models\Exams\Item;
use App\Models\Exams\Answer;
use App\Models\Exams\Question;
use Illuminate\Database\Seeder;
use App\Models\Categories\Category;
use App\Models\Deliveries\Delivery;

/**
 * Second trial data seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class SecondTrialDataSeeder extends Seeder
{
    protected $mcq = [
        [
            'title' => 'An absolute contraindication to glenoid resurfacing when performing shoulder arthroplasty',
            'content' => null,
            'questions' => [
                [
                    'question' => 'An absolute contraindication to glenoid resurfacing when performing shoulder arthroplasty is',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'patients is less than or equal to 50 years of age',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'presence of a small supraspinatus tear',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'insufficient bone stock',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'presence of osteonecrosis of the humeral head',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'presence of an inflammatory arthropathy',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Apex posterior malalignment of a femur fracture is poorly tolerated because',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Apex posterior malalignment of a femur fracture is poorly tolerated because',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'the bone   has less remodelling capacity in the anterior/posterior plane',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Apex posterior malaligment creates hyperextension in the knee at heel strike, which creates pain with ambulation ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'The risk of overgrowth is greater in patients with an apex posterior malalignment than other angular malalignments',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Apex posterior malalignment follwoing a femoral shaft fracture can create a traction injury to the sciatic nerve',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following criteria is not included in the Mangled Extremity Severity Score (MESS)?',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following criteria is not included in the Mangled Extremity Severity Score (MESS)?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Skeletal/soft tissue injury',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Limb ischemia',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Fracture pattern  ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Shock',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Age',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'All of the following can lead to loss of motion following anterior cruciate ligament (ACL) reconstruction except',
            'content' => null,
            'questions' => [
                [
                    'question' => 'All of the following can lead to loss of motion following anterior cruciate ligament (ACL) reconstruction except',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'non-anatomic graft placement',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'concomitant ligament surgery',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'intercondylar notch scaning',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'use of allograft patellar tendon',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'immobilization',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Contraindications to retgrade nailing of a femur fracture include which one of the following',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Contraindications to retgrade nailing of a femur fracture include which one of the following',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'skeletal immaturity',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Pregnancy',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Morbid obesity',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Ipsilateral tibia fracture',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Ipsilateral hip fracture',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following anatomic landmarks of the knee represents the contact area between the lateral femoral condyle and the anterior hom of the lateral meniscus when the knee is in full extension',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following anatomic landmarks of the knee represents the contact area between the lateral femoral condyle and the anterior hom of the lateral meniscus when the knee is in full extension',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Outerbridge’s ridge',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Blumensatt’s line ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Notch of Grant',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'David’s point',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Sulcus terminalis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following lessions would display a low to moderate signal on T1 weighted images and high signal on T2 weighted images',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following lessions would display a low to moderate signal on T1 weighted images and high signal on T2 weighted images',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'lipomas',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Subcutaneous fat',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Ganglions',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Malignant fiborus histiocytoma',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Tendons',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following describes the typical appearance of metastatic bone disease in the spine',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following describes the typical appearance of metastatic bone disease in the spine',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'expansite, destructive lession in the posterior elements',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'A 10 mm lytic area with surrounding sclerosis in the posterior elements',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Diffuse osteopenia in the vertebral body',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Completely coliapsed vertebral body with increased width',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Destruction of a pedicle on one side',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following statements best characterizes the natural history of metatarsus adductus in a newborn',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following statements best characterizes the natural history of metatarsus adductus in a newborn',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'metatarsus adductus is likely to become fixed if not treated with casts',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'metatarsus adductus is likely to become fixed if not treated by 6 months',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'metatarsus adductus is likely to become fixed if not surgically corrected',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'metatarsus adductus is likely to later develop hind foot equinus',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'most infants will improve spontaneously',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Laboratory findings found in rickets and/or osteomalacia, include the following except',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Laboratory findings found in rickets and/or osteomalacia, include the following except :',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'decreased urinary calcium',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'normal or decreased serum calcium',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'decreased serum phosphorous',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'increased parathyroid hormone',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'decreased alkaline phosphatase',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 9-year-old child presents one year after a supracondylar humerus fracture is healed. The elbow is in 15<sup>o</sup> more varus than the other side. Which of the following statements to the family is true ',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 9-year-old child presents one year after a supracondylar humerus fracture is healed. The elbow is in 15<sup>o</sup> more varus than the other side. Which of the following statements to the family is true ',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'this is likely to be due to growth plate damage in the distal humerus',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'this is likely to correct fully before the end of growth',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'the deformity is probably due to hyperemia and overgrowth of the capitellum',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'the deformity is likely due to malposition of the fracture during healing ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'the varus sill likely lead to an increased likelihood of degerative joint disease',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 50-year-old woman presents with pain in the second toe. She describes this as burning and notes swelling of the toe for the past month. Upon examination, there appears to be instability of the toe with a possitive dorsal subluxation stress test.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 50-year-old woman presents with pain in the second toe. She describes this as burning and notes swelling of the toe for the past month. Upon examination, there appears to be instability of the toe with a possitive dorsal subluxation stress test. The anatomic structure which is responsible for this patient’s symptoms is',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'the deep transverse metatarsal ligament',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'the second common digital nerve',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'the medial collateral ligament of the second metatarsophalangeal joint ',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'the plantar plate',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'the flexor digitorum brevis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 22-year-old patient presents for treatment of a painful foot deformity. On examination, a flexible cavovarus deformity is present.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 22-year-old patient presents for treatment of a painful foot deformity. On examination, a flexible cavovarus deformity is present. The patient has good dorsiflexion foot strength, and eversion strength is weak. A possible tendon transfer that can be used to correct this deformity is',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'anterior tibial to middle cuneiform',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posterior tibial to peroneous longus',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'peroneus longus to peroneus brevis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'flexor digitorum to posterior tibial',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posterior tibial to lateral cuneiform',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 28-year-old male runner presents for treatment of a painful lesion under the first metatarsal heal (located more toward  the medial aspect of the metatarsophalangeal joint).',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 28-year-old male runner presents for treatment of a painful lesion under the first metatarsal heal (located more toward  the medial aspect of the metatarsophalangeal joint). The lesion is associated with painful callosity to palpation, normal hallux function, and normal position of the first metatarsal. Attempts to relieve the pressure with orthoses and shoe modifications have not been successful. The ideal treatment is ',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'arthrodesis of the first metatarsophalangeal joint',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'dorsal wedge osteotomy of the distal first metatarsal ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'resection of the tibial sesamoid',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'plantar shaving of the tibial sesamoid',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'proximal first metatarsal osteotomy',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Six months ago, an 11-year-old premenarchal girl with adolescent idiopathic scoliosis had a right thoracic curve from T5 to T12 measuring 20<sup>o</sup>.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Six months ago, an 11-year-old premenarchal girl with adolescent idiopathic scoliosis had a right thoracic curve from T5 to T12 measuring 20<sup>o</sup>. Her physical examination was normal. She returned to the office and a standing posteroanterior radiograph demonstrates a 28<sup>o</sup> right thoracic curve from T5 to T12, she is Risser stage 0. A lateral radiograph showns a thoracic kyphosis of 10<sup>o</sup>. At this time, you recommend',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'repeat radiograph in 6 months',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'thoracic flexibility exercises',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'full-time use of a thoracolumbosacral orthosis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'electrical stimulation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posterior spinal fusion with instrumentation',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A right hand dominant 15-year-old baseball player who felt a pop when swinging a bat suffered from fracture of first rib.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A right hand dominant 15-year-old baseball player who felt a pop when swinging a bat suffered from fracture of first rib. The is pain in the upper portion of the first rib. Recommended treatment should consist of',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'immobilization in a shoulder spica cast',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'immobilization in  a sling',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'open reduction internal fixation with bone graft',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'open biopsy',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'obseration',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'The most common osseous abnormality in neurofibromatosis 1 (NF1) is',
            'content' => null,
            'questions' => [
                [
                    'question' => 'The most common osseous abnormality in neurofibromatosis 1 (NF1) is',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'congenital tibial dysplasia',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Scoliosis ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Valgus deformity of the ankle',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Macrodactyly',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Dysplasia of posterior cranial fossa',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'After landing awkwardly on his felxed knee, a 22-year-old basketball player has immediate onset of pain and difficulty bearing weight.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'After landing awkwardly on his felxed knee, a 22-year-old basketball player has immediate onset of pain and difficulty bearing weight. With  the knee flexed 300, examination reveals increased varus, external rotation, and posterior translation which decreases when the knee is flexed to 900. The patient most likely has injured what structure (s) :',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'posterolateral complex',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posterolateral complex and posterior cruciate ligament ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posterior cruciate ligament',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'lateral collateral ligament',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'posteriro cruciate ligament and medial collateral ligament',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'In which of the following tumors is the bone scan the least sensitive',
            'content' => null,
            'questions' => [
                [
                    'question' => 'In which of the following tumors is the bone scan the least sensitive',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'metastatic breast cancer',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'metastatic lung cancer',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'osteosarcoma',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'multiple myeloma',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'osteoid osteoma',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following best describes the current treatment of osteosarcoma in adolescents',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following best describes the current treatment of osteosarcoma in adolescents',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'wide resection alone',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'wide resection and multi-agent chemotherapy ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'wide resection, multi-agent chemotherapy, and radiation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'wide resection and radiation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'interferon and radiation',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'The earliest and most objective sign of compartment syndrome is',
            'content' => null,
            'questions' => [
                [
                    'question' => 'The earliest and most objective sign of compartment syndrome is',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'Pain with passive stretching of the muscles in the involved compartment',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Paresthesia ',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'A tense compartment',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Hypoesthesia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Paralysis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Examination of a 12-year-old girl with bilateral anterior knee pain reveals excessive femoral anteversion and excessive external tibial torsion.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Examination of a 12-year-old girl with bilateral anterior knee pain reveals excessive femoral anteversion and excessive external tibial torsion. The patient has no patellofemoral instability. Nonsurgical management consisting of muscle strengthening and nonsteroidal medication has failed to relieve the patient’s pain. Treatment should now consist of',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'Examination of a 12-year-old girl with bilateral anterior knee pain reveals excessive femoral anteversion and excessive external tibial torsion.',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'external rotation of the distal part of the tibia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'internal rotation of the distal part of the femur',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'arthroscopic retinacular release',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'a patellar realignment procedure',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following  patients with cerebral palsy is considered the ideal candidate for a selective dorsal rhizotomy?',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following  patients with cerebral palsy is considered the ideal candidate for a selective dorsal rhizotomy?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'an ambulatory 6-year old patient with spastic diplegia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'an ambulatory 10-year old patient with spastic right hemiplegia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'an ambulatory 16-year old patient with spastic diplegia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'an ambulatory 8-year old patient with spastic quadriplegia',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'an ambulatory 18-year old patient with rigid quadriplegia',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 3-year-old boy sustains a complete paralysis following a high thoracic spinal cord  injury consistent with a SCIWORA-type injury (spinal cord injury without radiographic abnormality).',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 3-year-old boy sustains a complete paralysis following a high thoracic spinal cord  injury consistent with a SCIWORA-type injury (spinal cord injury without radiographic abnormality). Subsequent progressive spinal deformity will develop in what percent of patient with this injury?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => '10%',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => '25%',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => '50%',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => '75%',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'Greater than 75%',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A12-year-old girl has progressive development of cavus feet. Examination reveals slightly diminished vibratory sensation on the bottom of the foot.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A12-year-old girl has progressive development of cavus feet. Examination reveals slightly diminished vibratory sensation on the bottom of the foot. Reflexes are 1+ at the knees and ankles. Motor examination shows that all muscles are 5/5 in the foot, except the peroneal and anterior tibial muscles are rated as 4+/5. Which of the following studies is considered most diagnostic?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'nerve conduction velocity studies',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'biopsy of the quadriceps femoris muscle',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'biopsy of the sural nerve',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'DNA testing',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Chromosomal analysis',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 12-year-old girl has had lower back pain for the past 6 months that interferes with her ability to participate in sports',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 12-year-old girl has had lower back pain for the past 6 months that interferes with her ability to participate in sports. She denies any history of radicular symptoms, sensory changes, or bowel or bladder dysfunction. Examination reveals a shuffling gait, restriction of forward bending, and tight hamstrings. Radiographs show a grade III spondylolisthesis of L5 on S1, with a slip angle of 20<sup>o</sup>. Management should consist of',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'brace treatment',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'laminectomy, nerve root decompression, and in situ fusion of L4 to the sacrum',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'in situ fusion of L4 to the sacrum',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'excision of the L5 lamina',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'physical therapy',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'Which of the following types of illiac osteotomy provides the greatest potential for increased coverage?',
            'content' => null,
            'questions' => [
                [
                    'question' => 'Which of the following types of illiac osteotomy provides the greatest potential for increased coverage?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'Ganz periacetabular',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Pemberton innominate',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Salter innominate',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Sutherland double innominate',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'Steele triple innominate',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'What is the recommended treatment of a skeletally immature 12-year-old boy who has an anterior cruciate ligament-deficit knee?',
            'content' => null,
            'questions' => [
                [
                    'question' => 'What is the recommended treatment of a skeletally immature 12-year-old boy who has an anterior cruciate ligament-deficit knee?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 1,
                            'answer' => 'reduced activity, rehabilitation excercises, and functional braching until the patient is near skeletal maturity',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'bone-patellar tendon-bone autograft reconstruction',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'allograft reconstruction',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'hamstring tendon intra-articular repair using a centrally placed tibial tunnel and an over-the-top femoral attachment',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'extra-artcular repair',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 12-year-old boy who has had a  1-month history of right thigh pain and a limp reports worsening of the pain after a fall, and he can no longer walk or bear weight on the involved extremity.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 12-year-old boy who has had a  1-month history of right thigh pain and a limp reports worsening of the pain after a fall, and he can no longer walk or bear weight on the involved extremity.  Radiographs of the pelvis reveal a slipped capital femoral epiphysis for screw fixation, partial reduction of the slip is achieved. No further reduction maneuvers are attempted, and the epiphysis is stabilized with a single cannulated   screw. What complication is most likely to develop following this procedure?',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'infection',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'chondrolysis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'nonunion',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'osteonecrosis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'epiphyseal arrest',
                        ],
                    ],
                ],
            ],
        ], [
            'title' => 'A 12-year-old boy who has had a  1-month history of right thigh pain and a limp reports worsening of the pain after a fall, and he can no longer walk or bear weight on the involved extremity.',
            'content' => null,
            'questions' => [
                [
                    'question' => 'A 4-year-old  boy  sustained a nondisplaced, but complete, fracture of the left proximal tibial metaphysis 1 year ago. The fracture healed uneventfully in an anatomic position. Examination of the injuried extremity now reveals 18<sup>o</sup> of valgus compared with 30 of valgus on the opposite side. Management should now include',
                    'type' => 'multi-choice',
                    'answers' => [
                        [
                            'is_correct_answer' => 0,
                            'answer' => 'observation',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'a nee-ankle-foot orthosis',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'a medial proximal tibial epiphyseodesis',
                        ], [
                            'is_correct_answer' => 1,
                            'answer' => 'a proximal tibial osteotomy',
                        ], [
                            'is_correct_answer' => 0,
                            'answer' => 'lateral proximal tibial stapling',
                        ],
                    ],
                ],
            ],
        ],
    ];

    protected $essay = [
        [
            'title' => 'Essay: Case 1',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'path' => '/data/second-trial/case-1.html',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Essay: Case 2',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'path' => '/data/second-trial/case-2.html',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Essay: Case 3',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'path' => '/data/second-trial/case-3.html',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Essay: Case 4',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'path' => '/data/second-trial/case-4.html',
                    'answers' => [],
                ],
            ],
        ], [
            'title' => 'Essay: Case 5',
            'content' => null,
            'questions' => [
                [
                    'type' => 'essay',
                    'path' => '/data/second-trial/case-5.html',
                    'answers' => [],
                ],
            ],
        ],
    ];

    public function run()
    {
        $categories = Category::where('name', '=', 'Unspecified')->get()->keyBy('id');
        $ids = $categories->keys()->all();
        $mcqIds = [];
        $essayIds = [];

        foreach ($this->mcq as $_mcq) {
            $mcq = $this->createQuestionItem($_mcq);
            $mcq->categories()->sync($ids);
            $mcqIds[] = $mcq->id;
        }

        foreach ($this->essay as $_essay) {
            $essay = $this->createQuestionItem($_essay);
            $essay->categories()->sync($ids);
            $essayIds[] = $essay->id;
        }

        /*
        // create new exam
        $examMcq = new Exam();
        $examMcq->fill([
        'code' => 'TRIAL-MCQ-2',
        'name' => 'MCQ 2nd Trial',
        ]);
        $examMcq->save();
        $examMcq->items()->sync($mcqIds);

        $examEssay = new Exam();
        $examEssay->fill([
        'code' => 'TRIAL-ESSAY-2',
        'name' => 'Essay 2nd Trial',
        ]);
        $examEssay->save();
        $examEssay->items()->sync($essayIds);

        // create new deliveries
        $takerIds = [1,2,3,4,5,6,7,8,9];

        $deliveryMcq = new Delivery();
        $deliveryMcq->fill([
        'exam_id' => $examMcq->id,
        'scheduled_at' => '2017-06-19 18:00:00',
        'duration' => 25,
        ]);
        $deliveryMcq->save();

        foreach ($takerIds as $id) {
        $deliveryMcq->takers()->attach($id, [
        'token' => \Illuminate\Support\Str::random(5),
        ]);
        }

        $deliveryEssay = new Delivery();
        $deliveryEssay->fill([
        'exam_id' => $examEssay->id,
        'scheduled_at' => '2017-06-18 12:00:00',
        'duration' => 60,
        ]);
        $deliveryEssay->save();

        foreach ($takerIds as $id) {
        $deliveryEssay->takers()->attach($id, [
        'token' => \Illuminate\Support\Str::random(5),
        ]);
        }
         */
    }

    protected function createQuestionItem($inputs = [])
    {
        $item = new Item();
        $item->fill([
            'title' => $inputs['title'],
            'content' => $inputs['content'],
        ]);
        $item->save();
        $this->command->info('Item created:`'.$item->title.'`');

        foreach ($inputs['questions'] as $_question) {
            $question = new Question();
            $question->fill([
                'item_id' => $item->id,
                'type' => $_question['type'],
                'question' => isset($_question['path']) ? file_get_contents(__DIR__.$_question['path']) : $_question['question'],
            ]);
            $question->save();
            $this->command->info('Question created: `'.$question->type?->toJson().' #'.$question->id.'`');

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

        return $item;
    }
}
