<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Validation\Rule;

/**
 * A server-paged list's column-header sort: `sort` (one of the list's own
 * keys) and `order` asc | desc. No sort asked → the list's own order.
 *
 *   $filters = $request->validate([...] + ListSort::rules(UserService::SORTS));
 *   ListSort::apply($query, $filters, array_combine(UserService::SORTS, UserService::SORTS));
 */
final class ListSort
{
    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public static function rules(array $keys): array
    {
        return [
            'sort' => ['nullable', 'string', Rule::in($keys)],
            'order' => ['nullable', 'in:asc,desc'],
        ];
    }

    /**
     * @template TQuery of \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     *
     * @param  TQuery  $query
     * @param  array<string, mixed>  $filters
     * @param  array<string, string|Expression>  $columns  sort key → column
     * @return TQuery
     */
    public static function apply($query, array $filters, array $columns, string $key = 'id')
    {
        $sort = $filters['sort'] ?? null;
        if (! $sort || ! isset($columns[$sort])) {
            return $query;
        }
        $direction = ($filters['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->reorder()->orderBy($columns[$sort], $direction)->orderBy($key, $direction);
    }
}
