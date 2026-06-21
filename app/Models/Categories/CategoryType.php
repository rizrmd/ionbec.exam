<?php

namespace App\Models\Categories;

use App\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Per-client configurable category type.
 *
 * The `key` (slug, e.g. "disease-group") is the value stored on
 * categories.type and MUST stay stable once created. `label` and `color`
 * are freely editable per client.
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $key
 * @property string $label
 * @property string $color
 * @property int $order
 */
class CategoryType extends Model
{
    use BelongsToClient;
    use HashableId;

    protected $table = 'category_types';

    protected $fillable = ['client_id', 'key', 'label', 'color', 'order'];

    protected $appends = ['hash'];

    protected $hidden = ['id'];

    /**
     * The default types every client starts with. Also used as a safe
     * fallback when a client has no rows yet.
     */
    public const DEFAULTS = [
        ['key' => 'disease-group', 'label' => 'Disease Group', 'color' => 'red', 'order' => 1],
        ['key' => 'region-group', 'label' => 'Region Group', 'color' => 'green', 'order' => 2],
        ['key' => 'specific-part', 'label' => 'Specific Part', 'color' => 'blue', 'order' => 3],
        ['key' => 'typical-group', 'label' => 'Typical Group', 'color' => 'yellow', 'order' => 4],
    ];

    /**
     * Ordered collection of the current client's types, falling back to the
     * defaults (as transient models) when the client has none.
     */
    public static function forCurrentClient()
    {
        $rows = static::query()->orderBy('order')->orderBy('id')->get();

        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return collect(self::DEFAULTS)->map(fn ($default) => new static($default));
    }

    /**
     * [key => label] for the current client. Mirrors the old enum getOptions().
     */
    public static function getOptions(): array
    {
        return static::forCurrentClient()
            ->mapWithKeys(fn ($type) => [$type->key => $type->label])
            ->toArray();
    }

    /**
     * [key => color] for the current client.
     */
    public static function getColorMap(): array
    {
        return static::forCurrentClient()
            ->mapWithKeys(fn ($type) => [$type->key => $type->color])
            ->toArray();
    }

    /**
     * Seed the default types for a freshly created client (idempotent).
     */
    public static function seedDefaultsForClient(int $clientId): void
    {
        foreach (self::DEFAULTS as $default) {
            static::withoutClientScope()->firstOrCreate(
                ['client_id' => $clientId, 'key' => $default['key']],
                [
                    'label' => $default['label'],
                    'color' => $default['color'],
                    'order' => $default['order'],
                ],
            );
        }
    }
}
