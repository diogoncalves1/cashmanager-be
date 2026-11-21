<?php

namespace App\Searches;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class ModuleSearch
{
    protected array $fields = [];

    protected array $relations = [];

    public function search(Request $request, Builder|Relation $query): Builder|Relation
    {
        $validated = validator($request->query(), ['search' => ['nullable', 'string', 'max:200']])->validate();
        $term = trim($validated['search'] ?? '');
        if ($term === '' || ($this->fields === [] && $this->relations === [])) {
            return $query;
        }
        // A literal contains search. ! is the portable SQL LIKE escape character.
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        return $query->where(function (Builder $group) use ($pattern, $request) {
            foreach ($this->fields as $field) {
                $this->contains($group, $field, $pattern);
            }
            foreach ($this->relations as $relation => $fields) {
                $group->orWhereHas($relation, function (Builder $related) use ($fields, $pattern, $request, $relation) {
                    $this->constrainRelation($related, $request, $relation);
                    $related->where(function (Builder $columns) use ($fields, $pattern) {
                        foreach ($fields as $field) {
                            $this->contains($columns, $field, $pattern);
                        }
                    });
                });
            }
        });
    }

    protected function contains(Builder $query, string $field, string $pattern): void
    {
        $column = $query->getQuery()->getGrammar()->wrap($field);
        $query->orWhereRaw("$column LIKE ? ESCAPE '!'", [$pattern]);
    }

    protected function constrainRelation(Builder $query, Request $request, string $relation): void {}
}
