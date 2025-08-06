<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilter
{
    public function limit(Request $request): int
    {
        $limit = $request->query('limit');
        if ($limit && $limit < 0) {
            $limit = 10;
        }
        return (int)$limit;
    }

    public function search(Builder $builder,Request $request): Builder
    {
        $search = $request->query('search');

        return $builder->when($search, function (Builder $builder) use ($search) {
            return $builder->where('name', 'like', '%' . $search . '%');
        });
    }
}
