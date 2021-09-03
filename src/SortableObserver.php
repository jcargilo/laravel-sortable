<?php

namespace Ninoman\LaravelSortable;

class SortableObserver
{
    public function creating($model): void
    {
        if ($model->getSetSortingIndexOnCreating()) {
            $model->{$model->getSortIndexColumn()} = $model->getNextSortIndex();
        }
    }

    public function deleted($model): void
    {
        $model->sameParentChild()->where($model->getSortIndexColumn(), '>', $model->{$model->getSortIndexColumn()})->decrement($model->getSortIndexColumn());
    }

    public function updating($model): void
    {
        if ($model->isDirty($model->getSortingParentColumn()) && $model->resortOthers) {
            if ($model->{$model->getSortIndexColumn()} > $model->getNextSortIndex()) {
                $model->{$model->getSortIndexColumn()} = $model->getNextSortIndex();
            }

            $this->resortOriginalParentSortIndex($model);
            $this->resortNewParentSortIndex($model);
        } else if ($model->isDirty($model->getSortIndexColumn()) && $model->resortOthers) {
            if ($model->{$model->getSortIndexColumn()}> $model->getOriginal($model->getSortIndexColumn())) {
                $this->decrementSortIndex($model);
            } else {
                $this->incrementSortIndex($model);
            }
        }
    }

    private function incrementSortIndex($model): void
    {
        $model->sameParentChild()
            ->where($model->getSortIndexColumn(), '<', $model->getOriginal($model->getSortIndexColumn()))
            ->where($model->getSortIndexColumn(), '>=', $model->{$model->getSortIndexColumn()})
            ->increment($model->getSortIndexColumn());
    }

    private function decrementSortIndex($model): void
    {
        $model->sameParentChild()
            ->where($model->getSortIndexColumn(), '>', $model->getOriginal($model->getSortIndexColumn()))
            ->where($model->getSortIndexColumn(), '<=', $model->{$model->getSortIndexColumn()})
            ->decrement($model->getSortIndexColumn());
    }

    private function resortOriginalParentSortIndex($model): void
    {
        $model->where($model->getSortingParentColumn(), '=', $model->getOriginal($model->getSortingParentColumn()))
            ->where($model->getSortIndexColumn(), '>', $model->getOriginal($model->getSortIndexColumn()))
            ->decrement($model->getSortIndexColumn());
    }

    private function resortNewParentSortIndex($model): void
    {
        $model->sameParentChild()
            ->where($model->getSortIndexColumn(), '>=', $model->{$model->getSortIndexColumn()})
            ->increment($model->getSortIndexColumn());
    }
}
