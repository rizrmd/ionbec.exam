<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default category types seeded for every client so existing behaviour
     * stays identical while each client can later customise its own types.
     */
    private array $defaults = [
        ['key' => 'disease-group', 'label' => 'Disease Group', 'color' => 'red', 'order' => 1],
        ['key' => 'region-group', 'label' => 'Region Group', 'color' => 'green', 'order' => 2],
        ['key' => 'specific-part', 'label' => 'Specific Part', 'color' => 'blue', 'order' => 3],
        ['key' => 'typical-group', 'label' => 'Typical Group', 'color' => 'yellow', 'order' => 4],
    ];

    public function up(): void
    {
        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('key');
            $table->string('label');
            $table->string('color')->default('gray');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->index('client_id');
            $table->unique(['client_id', 'key']);
        });

        // Seed the four default types for every existing client so nothing
        // changes for them. New types remain fully editable per client.
        // Use a plain timestamp string (not now()) to stay independent of the
        // Carbon/PHP version on whichever host runs the migration.
        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach (DB::table('clients')->pluck('id') as $clientId) {
            foreach ($this->defaults as $default) {
                $rows[] = array_merge($default, [
                    'client_id' => $clientId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! empty($rows)) {
            DB::table('category_types')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_types');
    }
};
