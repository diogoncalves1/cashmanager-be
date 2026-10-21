<?php
namespace App\Filters;

use DeepCopy\Exception\PropertyException;
use Exception;
use Illuminate\Http\Request;

class Filter
{

    protected array $allowedOperatorsFields = [];

    protected array $translateOperatorsFields = [
        'gt'  => '>',
        'gte' => '>=',
        'lt'  => '<',
        'lte' => '<=',
        'eq'  => '=',
        'ne'  => '!=',
        'in'  => 'in',
    ];

    protected array $translateFields = [];

    public function filter(Request $request, \Illuminate\Database\Eloquent\Builder $query)
    {
        $where   = [];
        $whereIn = [];

        if (empty($this->allowedOperatorsFields)) {
            throw new PropertyException("Property allowedOperatorsFields is empty");
        }

        foreach ($this->allowedOperatorsFields as $param => $operators) {
            $queryOperator = $request->query($param);
            if ($queryOperator) {
                foreach ($queryOperator as $operator => $value) {
                    if (! in_array($operator, $operators)) {
                        throw new Exception("{$param} does not have {$operator} operator");
                    }

                    if (! empty($this->translateFields) && array_key_exists($param, $this->translateFields)) {
                        $param = $this->translateFields[$param];
                    }
                    if (str_contains($value, '[')) {
                        $whereIn[] = [$param, explode(',', str_replace(['[', ']'], ['', ''], $value))];
                    } else {
                        $where[] = [$param, $this->translateOperatorsFields[$operator], $value];
                    }
                }
            }
        }

        $filter = [
            'where'   => $where,
            'whereIn' => $whereIn,
        ];

        if (empty($filter)) {
            return $query;
        }

        if (! empty($whereIn)) {
            foreach ($whereIn as $value) {
                $query->whereIn($value[0], $value[1]);
            }
        }

        $query->where($where);

        return $query;
    }
}
