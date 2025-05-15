<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Models\WorkflowSlot;

class WorkflowRepository
{
    protected $model;

    public function __construct(Workflow $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);

        return $record;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getWithSlots($id)
    {
        return $this->model->with(['slots' => function ($query) {
            $query->orderBy('slot_number');
        }, 'slots.users'])->findOrFail($id);
    }

    public function attachUsersToSlot($slotId, array $userIds)
    {
        $slot = WorkflowSlot::findOrFail($slotId);
        $slot->users()->syncWithoutDetaching($userIds);

        return $slot;
    }

    public function detachUsersFromSlot($slotId, array $userIds)
    {
        $slot = WorkflowSlot::findOrFail($slotId);
        $slot->users()->detach($userIds);

        return $slot;
    }
}
