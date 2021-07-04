<?php
// 7-7
namespace App\Models;

trait BooleanSoftDeletes
{
    protected $forceDeleting = false;

    // public static function bootSoftDeletes()
    // {
    //     static::addGlobalScope(new SoftDeletingScope);
    // }
    // 需要把名字修改成类对应
    public static function bootBooleanSoftDeletes()
    {
        static::addGlobalScope(new BooleanSoftDeletingScope);
    }

    // 修改完后 字段是一个时间戳 不是布尔值 不需要它
    // public function initializeSoftDeletes()
    // {
    //     $this->dates[] = $this->getDeletedAtColumn();
    // }

    public function forceDelete()
    {
        $this->forceDeleting = true;
        
        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;

            if ($deleted) {
                $this->fireModelEvent('forceDeleted', false);
            }
        });
    }

    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            $this->exists = false;

            return $this->setKeysForSaveQuery($this->newModelQuery())->forceDelete();
        }

        return $this->runSoftDelete();
    }

    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        // $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];
        $columns = [$this->getDeletedAtColumn() => 1];

        // $this->{$this->getDeletedAtColumn()} = $time;
        $this->{$this->getDeletedAtColumn()} = 1 ;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        // $this->{$this->getDeletedAtColumn()} = null;
        $this->{$this->getDeletedAtColumn()} = 0;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    public function trashed()
    {
        // 已近被删除的 
        return $this->{$this->getDeletedAtColumn()} == 1;
        return ! is_null($this->{$this->getDeletedAtColumn()});
    }


    public static function restoring($callback)
    {
        static::registerModelEvent('restoring', $callback);
    }

    public static function restored($callback)
    {
        static::registerModelEvent('restored', $callback);
    }

    public static function forceDeleted($callback)
    {
        static::registerModelEvent('forceDeleted', $callback);
    }

    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }

    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted';
        // return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }

    public function getQualifiedDeletedAtColumn()
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
}
