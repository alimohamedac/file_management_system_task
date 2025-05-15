<?php

namespace App\Repositories;

use App\Models\DocumentWorkflowInstance;
use App\Models\DocumentSlotStatus;
use App\Models\WorkflowSlot;

class DocumentWorkflowRepository
{
    protected $model;

    public function __construct(DocumentWorkflowInstance $model)
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

    public function approveSlot($instanceId, $slotId, $userId, $comments = null)
    {
        $status = DocumentSlotStatus::where('document_workflow_instance_id', $instanceId)
            ->where('workflow_slot_id', $slotId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $status->update([
            'status' => 'approved',
            'signed_at' => now(),
            'comments' => $comments
        ]);

        $this->checkAndUpdateInstanceStatus($instanceId, $slotId);

        return $status;
    }

    public function rejectSlot($instanceId, $slotId, $userId, $comments = null)
    {
        $status = DocumentSlotStatus::where('document_workflow_instance_id', $instanceId)
            ->where('workflow_slot_id', $slotId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $status->update([
            'status' => 'rejected',
            'signed_at' => now(),
            'comments' => $comments
        ]);

        // Update instance status to rejected
        $this->model->findOrFail($instanceId)->update(['status' => 'rejected']);

        return $status;
    }

    public function getCurrentSlot($instanceId)
    {
        $instance = $this->model->with(['slotStatuses.workflowSlot', 'workflow.slots'])
            ->findOrFail($instanceId);

        return $instance->slotStatuses()
            ->whereHas('workflowSlot', function ($query) {
                $query->orderBy('slot_number', 'desc');
            })
            ->first();
    }

    public function checkSlotApprovalStatus($instanceId, $slotId)
    {
        $slot = WorkflowSlot::findOrFail($slotId);

        $statuses = DocumentSlotStatus::where('document_workflow_instance_id', $instanceId)
            ->where('workflow_slot_id', $slotId)
            ->get();

        if ($slot->approval_method === 'single') {
            return $statuses->contains('status', 'approved');
        }

        return $statuses->every(function ($status) {
            return $status->status === 'approved';
        });
    }

    private function checkAndUpdateInstanceStatus($instanceId, $slotId)
    {
        if ($this->checkSlotApprovalStatus($instanceId, $slotId)) {
            $instance = $this->model->findOrFail($instanceId);
            $workflow = $instance->workflow;

            // Check if this is the last slot
            $isLastSlot = $workflow->slots()
                ->where('slot_number', '>', WorkflowSlot::find($slotId)->slot_number)
                ->doesntExist();

            if ($isLastSlot) {
                $instance->update(['status' => 'completed']);
            }
        }
    }
}
