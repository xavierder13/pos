<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * The app's records that shared features attach to — file attachments and
 * approval workflows — by a short type key used in URLs (`/attachments/user/5`).
 *
 * - model:      the Eloquent class
 * - label:      the record's name in the UI
 * - link:       its SPA view page (`{id}` is replaced)
 * - title:      the attribute that names one record
 * - approvals:  whether an approval flow can be set up for it
 *
 * The key is also the permission prefix: viewing needs `<key>-list`, adding /
 * removing attachments or submitting for approval needs `<key>-edit` (the
 * Administrator passes every check). `php artisan make:module` adds new
 * modules at the marker.
 */
final class RecordTypes
{
    public const TYPES = [
        'user' => ['model' => User::class, 'label' => 'User', 'link' => '/administration/users/{id}', 'title' => 'name', 'approvals' => true],
        'location' => ['model' => Location::class, 'label' => 'Location', 'link' => '/locations/{id}', 'title' => 'name', 'approvals' => true],
        'category' => ['model' => Category::class, 'label' => 'Category', 'link' => '/categories/{id}', 'title' => 'name', 'approvals' => true],
        'product' => ['model' => Product::class, 'label' => 'Product', 'link' => '/products/{id}', 'title' => 'name', 'approvals' => true],
        // [make:module record-types]
    ];

    /**
     * @return array{model: class-string<Model>, label: string, link: string, title: string, approvals: bool}
     */
    public static function get(string $type): array
    {
        return self::TYPES[$type] ?? throw new InvalidArgumentException("Unknown record type [{$type}].");
    }

    public static function exists(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function find(string $type, int|string $id): Model
    {
        return self::get($type)['model']::query()->findOrFail($id);
    }

    // the type key of a model instance (null when it isn't registered)
    public static function typeOf(Model $record): ?string
    {
        foreach (self::TYPES as $key => $def) {
            if ($record instanceof $def['model']) {
                return $key;
            }
        }

        return null;
    }

    /**
     * What the SPA shows for a record: { type, label, id, title, link }.
     *
     * @return array<string, mixed>
     */
    public static function describe(?Model $record, ?string $type = null): ?array
    {
        if (! $record) {
            return null;
        }
        $type ??= self::typeOf($record);
        $def = self::get($type);

        return [
            'type' => $type,
            'label' => $def['label'],
            'id' => $record->getKey(),
            'title' => (string) ($record->{$def['title']} ?? "#{$record->getKey()}"),
            'link' => str_replace('{id}', (string) $record->getKey(), $def['link']),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function approvalOptions(): array
    {
        return collect(self::TYPES)->filter(fn ($d) => $d['approvals'])
            ->map(fn ($d, $key) => ['value' => $key, 'label' => $d['label']])->values()->all();
    }
}
