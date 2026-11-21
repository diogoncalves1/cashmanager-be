<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ModuleFilter
{
    protected array $allowedOperatorsFields = [];

    protected array $translateFields = [];

    protected array $valueRules = [];

    protected array $aliases = [];

    protected array $operators = ['gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<=', 'eq' => '=', 'ne' => '!='];

    public function filter(Request $request, Relation|EloquentBuilder|QueryBuilder $query): Relation|EloquentBuilder|QueryBuilder
    {
        $input = $request->query();
        foreach ($this->aliases as $alias => [$field, $operator]) {
            if (array_key_exists($alias, $input) && $alias !== $field) {
                $existing = $input[$field] ?? [];
                $input[$field] = is_array($existing) ? $existing : ['eq' => $existing];
                $incoming = is_array($input[$alias]) ? $input[$alias] : [$operator => $input[$alias]];
                $input[$field] += $incoming;
            }
        }

        foreach ($this->allowedOperatorsFields as $field => $allowed) {
            if (! array_key_exists($field, $input) || $input[$field] === null || $input[$field] === '') {
                continue;
            }
            $conditions = is_array($input[$field]) ? $input[$field] : ['eq' => $input[$field]];
            $column = $this->translateFields[$field] ?? $field;
            foreach ($conditions as $operator => $value) {
                if (! in_array($operator, $allowed, true) || ($operator !== 'in' && ! isset($this->operators[$operator]))) {
                    throw ValidationException::withMessages([$field => __('validation.in', ['attribute' => $field])]);
                }
                if ($value === null || $value === '' || ($field === 'status' && $value === 'all')) {
                    continue;
                }
                $rules = $this->valueRules[$field] ?? ['string'];
                if ($operator === 'in') {
                    // Accept real query arrays and the original [a,b] notation.
                    $values = is_string($value) ? explode(',', trim($value, '[]')) : $value;
                    validator([$field => $values], [$field => ['required', 'array', 'list', 'max:100'], "$field.*" => $rules])->validate();
                    $query->whereIn($column, $values);
                } else {
                    validator([$field => $value], [$field => $rules])->validate();
                    $query->where($column, $this->operators[$operator], $value);
                }
            }
        }

        return $query;
    }
}
