<?php

// 7-7
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BooleanSoftDeletingScope implements Scope
{
    protected $extensions = ['Restore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

    public function apply(Builder $builder, Model $model)
    {
        // whereNull 即 判断数据是否存在
        $builder->where($model->getQualifiedDeletedAtColumn(), 0);
        // $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }

    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);

            // 获取到软删除后 更新时间戳 
            return $builder->update([
                // $column => $builder->getModel()->freshTimestampString(),
                $column => 1,
            ]);
        });
    }

    protected function getDeletedAtColumn(Builder $builder)
    {
        if (count((array) $builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        }

        return $builder->getModel()->getDeletedAtColumn();
    }

    protected function addRestore(Builder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            // return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
            return $builder->update([$builder->getModel()->getDeletedAtColumn() => 0]);
        });
    }

    protected function addWithTrashed(Builder $builder)
    {
        $builder->macro('withTrashed', function (Builder $builder, $withTrashed = true) {
            if (! $withTrashed) {
                return $builder->withoutTrashed();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    protected function addWithoutTrashed(Builder $builder)
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            // $builder->withoutGlobalScope($this)->whereNull(
            //     $model->getQualifiedDeletedAtColumn()
            // );
            $builder->withoutGlobalScope($this)->where(
                $model->getQualifiedDeletedAtColumn(),
                0
            );

            return $builder;
        });
    }

    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            // $builder->withoutGlobalScope($this)->whereNotNull(
            //     $model->getQualifiedDeletedAtColumn()
            // );
            $builder->withoutGlobalScope($this)->where(
                $model->getQualifiedDeletedAtColumn(),
                1
            );

            return $builder;
        });
    }
}
