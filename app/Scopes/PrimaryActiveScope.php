<?php
/**
 * Created by PhpStorm.
 * User: KeanMattingly
 * Date: 7/26/17
 * Time: 8:54 AM
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PrimaryActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('is_active', 1)->where('is_primary', 1);
    }
}