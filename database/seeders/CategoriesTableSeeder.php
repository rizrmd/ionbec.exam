<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categories\Category;

/**
 * Categories table seeder.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class CategoriesTableSeeder extends Seeder
{
    /**
     * Seeder data.
     *
     * @var array
     */
    protected $data = [
        'disease-group' => [
            'Unspecified',
            'Congenital',
            'Infection',
            'Tumor',
            'Injury/Trauma',
            'Metabolic',
            'Inflammatory',
            'Degenerative',
            'Neuromuscular',
            'Basic Science',
        ],
        'region-group' => [
            'Unspecified',
            'Cervical',
            'Thoracal',
            'Lumbar',
            'Sacrococcygeal',
            'Shoulder Joint',
            'Arm',
            'Elbow Joint',
            'Forearm',
            'Wrist Joint',
            'Hand',
            'Pelvic',
            'Hip Joint',
            'Thigh',
            'Knee Joint',
            'Lower Leg',
            'Ankle Joint',
            'Foot',
            'No Region',
            'Multiple Region',
        ],
        'specific-part' => [
            'Unspecified',
            'Pathogenesis',
            'Diagnosis/Investigation',
            'Treatment/Management',
            'Prognosis and Complication',
        ],
        'typical-group' => [
            'Unspecified',
            'Analysis',
            'Recall Type',
        ],
    ];

    /**
     * Run the seeder command.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $type => $datum) {
            foreach ($datum as $value) {
                $category = new Category();
                $category->fill([
                    'code' => null,
                    'type' => $type,
                    'name' => $value,
                ]);

                $category->save();
            }
        }
    }
}
