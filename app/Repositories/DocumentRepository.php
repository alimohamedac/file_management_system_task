<?php

namespace App\Repositories;

use App\Models\Document;
use App\Models\DocumentWorkflowInstance;

class DocumentRepository
{
    protected $model;

    public function __construct(Document $model)
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

    public function getPendingApprovals($userId)
    {
        return $this->model->whereHas('workflowInstances.slotStatuses', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending');
        })->with(['workflowInstances.workflow', 'workflowInstances.slotStatuses.workflowSlot'])
          ->get();
    }

    public function getDocumentHistory($id)
    {
        return $this->model->with([
            'creator',
            'currentWorkflowInstance.workflow.slots.users',
            'currentWorkflowInstance.slotStatuses.workflowSlot',
            'currentWorkflowInstance.slotStatuses.user',
            'workflowInstances.workflow',
            'workflowInstances.slotStatuses' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'workflowInstances.slotStatuses.workflowSlot',
            'workflowInstances.slotStatuses.user'
        ])->findOrFail($id);
    }

    public function startWorkflow($documentId, $workflowId)
    {
       // $document = $this->find($documentId);

        return DocumentWorkflowInstance::create([
            'document_id' => $documentId,
            'workflow_id' => $workflowId,
            'status' => 'pending',
            'sent_at' => now()
        ]);
    }

    public function getCurrentWorkflowStatus($documentId)
    {
        return $this->model->with([
            'workflowInstances' => function ($query) {
                $query->latest();
            },
            'workflowInstances.workflow',
            'workflowInstances.slotStatuses.workflowSlot',
            'workflowInstances.slotStatuses.user'
        ])->findOrFail($documentId);
    }
}
