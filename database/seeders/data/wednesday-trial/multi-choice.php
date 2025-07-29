<?php

namespace Database\Seeders;

/*
 * Multi choice question.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
return [
    [
        'title' => 'A 21-year-old rugby player has had the sensation of shoulder instability while making tackles for 3 years.',
        'content' => 'A 21-year-old rugby player has had the sensation of shoulder instability while making tackles for 3 years. Two years ago, he had an arthroscopic Bankart repair and capsulorrhaphy that used 3 suture anchors after dislocating his shoulder while making a tackle. This procedure required an emergency department sedated reduction. After this dislocation, he had paresthesias in his arm and a sense of weakness. His numbness eventually resolved. He did well after surgery until 2 weeks ago, when he again felt his shoulder dislocate while tackling and had an emergency department reduction.',
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'What caused his recurrent instability?',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'The use of suture anchors in his repair',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'The physical therapy program after surgery',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'His age at the time of first surgery',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'His activity levels after surgery',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Hipoplasia of humereal head',
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'Numbness after his first dislocation was related to',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Intrasurgical traction on the musculocutaneous nerve.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Residual interscalene blockade',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Ulnar neuropathy after sling use.',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Sensory axillary nerve palsy from his dislocation.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Compression of median nerve after reduction',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A 13 year old injures his leg at football, presents three weeks later with a painful lump on the medial aspect of the lower end of the femur.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'A 13 year old injures his leg at football, presents three weeks later with a painful lump on the medial aspect of the lower end of the femur.  Xray shows elevated periosteum and some new bone formation.  The most probably diagnosis is:',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Osteochondroma',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Osteosarcoma',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Osteoclastoma',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Haematoma',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Osteomyelitis',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'The sign causing the most concern in a patient with fat embolism is:',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'The sign causing the most concern in a patient with fat embolism is:',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Dyspnoea',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Fat globules in the urine',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Confusion',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Petechial rash over the upper chest and shoulders',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Fat in sputum',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A patient with a central dislocation of the hip following a motor car accident is noted to be shocked on admission, one hour after the accident.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'A patient with a central dislocation of the hip following a motor car accident is noted to be shocked on admission, one hour after the accident. Most likely cause is:',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Ruptured bladder',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Fat embolism',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Ruptured urethra',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Neurogenic shock',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Deep vein thrombosis',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'All are the features of  Nail-patella syndrome, except:',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'All are the features of  Nail-patella syndrome, except:All are the features of  Nail-patella syndrome, except:',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Narrow spinal canal.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Renal dysplasia',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Iliac horns.',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Short 5th metacarpal.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Cubitus valgus',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A 21-year -old male was involved in a fighting in which he struck a hard object after missing his opponent.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'path' => '/data/wednesday-trial/multi-choice/case-1.html',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Avascular necrosis of the metacarpal head',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Instability of metacarpophalangeal joint',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Malrotation',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Dorsal angulation at the apex of the fracture site',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Neurovascular rupture',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A 17-year-old male sustained an injury to his middle finger while catching a football.',
        'path' => '/data/wednesday-trial/multi-choice/case-2.html',
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'The most likely diagnosis in this situation is: ',
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
                        'answer' => 'Sprain of the DIP joint',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Rheumatoid arthritis',
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'The most appropriate management at this time would be which of the following?',
                'answers' => [
                    [
                        'is_correct_answer' => 1,
                        'answer' => '(Application of a tip protector splint maintaining the DIP joint at neutral and the PIP joint free)',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Closed reduction and percutaneous pin fixation of the DIP joint',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Open repair of the extensor mechanism',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Open repair and pinning of the DIP joint',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Arthodesis',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'An 8-year -old girl is brought to the ER by her mother after she fell from the monkey bars during recess at school.',
        'path' => '/data/wednesday-trial/multi-choice/case-3.html',
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'On examination, the patient is cooperative, but not able to move her fingers in all the ways that you ask her to do. The most likely nerve injury with this fracture is:',
                'answers' => [
                    [
                        'is_correct_answer' => 1,
                        'answer' => 'Radial nerve',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Axillary nerve',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Median nerve',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Ulnar nerve',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Anterior interosseus nerve',
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'The patient is taken to the operating room and a reduction is successfully obtained via closed means. Three lateral pins are placed to hold the reduction. Following surgery, the nerve is still not functioning. The most appropriate next step would be:',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Immediately return to OR for nerve exploration.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Observe overnight and take back to OR the following day if the finger s are not moving normally.',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Observe for a week',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Observe for 6 weeks and then obtain EMG/Nerve conduction studies if nerve function has not recovered',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Observe for 3 to 6 months and then obtain EMG/Nerve conduction studies if nerve has not recovered',
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'This patient was treated with three lateral pins. Had a medial wire been used for a crossed-pin technique, which nerve would have been at risk?',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Radial and ulnar',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Median and ulnar',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Ulnar',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Median',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Radial and median',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A 68 year old lady underwent left total knee replacement three years ago.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'A 68 year old lady underwent left total knee replacement three years ago. She had a extraction of the tooth due to infection one month before. She came to your clinic with swollen left knee, a prominent fistel. CRP 80, ESR 60, leucocytes 14,000. What is your plan?',
                'answers' => [
                    [
                        'is_correct_answer' => 1,
                        'answer' => 'Remove all of the TKR implant, debridement, put antibiotic bone cement spacer',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Give antibiotic intravenous, evaluate after one week',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Remove the implants, debridement, perform knee arthrodesis',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Give antibiotic intravenous, debridement adequately, continue with intravenous antibiotic for 6 weeks',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Give antibiotic intravenous, remove implants, perform knee arthrodesis',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'Figure a through c show the radiographs, including a stress radiograph, of a 58-year-old-woman who twisted her ankle on a step.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'path' => '/data/wednesday-trial/multi-choice/case-4.html',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'MRI scan of the ankle',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Non-weight bearing cast for 6 weeks',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Removable walking boot and progressive weight bearing',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Open reduction and internal fixation of the fibula',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Open reduction and internal fixation of the fibula with medial ligament repair',
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
                'path' => '/data/wednesday-trial/multi-choice/case-5.html',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Increased hip joint offset ',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Increased leg length ',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Increased hip joint offset and leg length ',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Increased leg length but no increase in offset',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'None of above',
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
                'path' => '/data/wednesday-trial/multi-choice/case-6.html',
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
                        'answer' => 'Open reduction and internal fixation with posteromedial and lateral plates via dual incisions. ',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A 4 year old boy fell from a monkey bar at a playground. His forearm was swollen and tender.',
        'content' => null,
        'questions' => [
            [
                'type' => 'multi-choice',
                'path' => '/data/wednesday-trial/multi-choice/case-7.html',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Order physiotherapy to perform ROM exercise',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Closed reduction of the ulna and casting with forearm netral pronation supination and elbow 90 degree flexion ',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Closed reduction of the ulna and casting with forearm pronated and elbow 90 degree flexion',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Closed reduction of the ulna and casting with forearm pronated and elbow 110 degree flexion',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Open reduction and internal fixation of the ulna, open reduction and pinning radiocapitellar joint',
                    ],
                ],
            ],
        ],
    ], [
        'title' => 'A one and a half year old boy was brought to your outpatient clinic with a problem on his forearm and hand.',
        'path' => '/data/wednesday-trial/multi-choice/case-8.html',
        'questions' => [
            [
                'type' => 'multi-choice',
                'question' => 'What is your diagnosis and classification',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Radial clubhand grade III',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Radial clubhand grade IV',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Radial Clubhand grade V',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Ulnar Clubhand grade III',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Ulnar Clubhand grade IV',
                    ],
                ],
            ],
            [
                'type' => 'multi-choice',
                'question' => 'Patient beforehand had already had serial plaster when he was born. The management at the current time is',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Radialization',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'Centralization',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Lengthening of the ulna',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Soft tissure release',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Wrist arthrodesis',
                    ],
                ],
            ], [
                'type' => 'multi-choice',
                'question' => 'This disease entity is frequently associated with',
                'answers' => [
                    [
                        'is_correct_answer' => 0,
                        'answer' => 'Polland Syndrome',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Apert Syndrome',
                    ], [
                        'is_correct_answer' => 1,
                        'answer' => 'VATER Syndrome',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Down Syndrome',
                    ], [
                        'is_correct_answer' => 0,
                        'answer' => 'Arnold Chiari Syndrome',
                    ],
                ],
            ],
        ],
    ],
];
