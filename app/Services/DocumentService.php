<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\DocumentWorkflowRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DocumentService
{
    protected $documentRepository;
    protected $documentWorkflowRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        DocumentWorkflowRepository $documentWorkflowRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->documentWorkflowRepository = $documentWorkflowRepository;
    }

    public function createDocument($data, $file)
    {
        try {
            DB::beginTransaction();

            // Store the document file in the public disk
            $path = $file->store('documents', 'public');

            // Create document record
            $document = $this->documentRepository->create([
                'subject' => $data['name'],
                'description' => $data['description'] ?? null,
                'doc_path' => $path,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Start workflow if specified
            if (isset($data['workflow_id'])) {
                $this->startWorkflow($document->id, $data['workflow_id']);
            }

            DB::commit();
            return $document;
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($path)) {
                Storage::delete($path);
            }
            throw $e;
        }
    }

    public function startWorkflow($documentId, $workflowId)
    {
        try {
            DB::beginTransaction();

            $instance = $this->documentRepository->startWorkflow($documentId, $workflowId);

            // first slot of the workflow
            $firstSlot = $instance->workflow->slots()
                ->orderBy('slot_number')
                ->first();

            if ($firstSlot) {
                // Create status records for all users in the first slot
                foreach ($firstSlot->users as $user) {
                    $instance->slotStatuses()->create([
                        'workflow_slot_id' => $firstSlot->id,
                        'user_id' => $user->id,
                        'status' => 'pending'
                    ]);
                }

                // Update document status
                $this->documentRepository->update($documentId, ['status' => 'in_progress']);
            }

            DB::commit();
            return $instance;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveDocument($documentId, $slotId, $userId, $comments = null)
    {
        try {
            DB::beginTransaction();

            $document = $this->documentRepository->find($documentId);
            $instance = $document->currentWorkflowInstance;

            $status = $this->documentWorkflowRepository->approveSlot(
                $instance->id,
                $slotId,
                $userId,
                $comments
            );

            // If slot is approved, create status records for next slot
            if ($this->documentWorkflowRepository->checkSlotApprovalStatus($instance->id, $slotId)) {
                $currentSlot = $status->workflowSlot;
                $nextSlot = $instance->workflow->slots()
                    ->where('slot_number', '>', $currentSlot->slot_number)
                    ->orderBy('slot_number')
                    ->first();

                if ($nextSlot) {
                    foreach ($nextSlot->users as $user) {
                        $instance->slotStatuses()->create([
                            'workflow_slot_id' => $nextSlot->id,
                            'user_id' => $user->id,
                            'status' => 'pending'
                        ]);
                    }
                } else {
                    // If no next slot, document is fully approved
                    $document->update(['status' => 'approved']);
                }
            }

            DB::commit();
            return $status;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectDocument($documentId, $slotId, $userId, $comments = null)
    {
        try {
            DB::beginTransaction();

            $document = $this->documentRepository->find($documentId);
            $instance = $document->currentWorkflowInstance;

            $status = $this->documentWorkflowRepository->rejectSlot(
                $instance->id,
                $slotId,
                $userId,
                $comments
            );

            // Update document status
            $document->update(['status' => 'rejected']);

            DB::commit();
            return $status;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPendingApprovals($userId)
    {
        return $this->documentRepository->getPendingApprovals($userId);
    }

    public function getDocumentHistory($id)
    {
        return $this->documentRepository->getDocumentHistory($id);
    }
}
