<?php

namespace App\Services;

use App\Repositories\WorkflowRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class WorkflowService
{
    protected $workflowRepository;

    public function __construct(WorkflowRepository $workflowRepository)
    {
        $this->workflowRepository = $workflowRepository;
    }

    public function createWorkflow($data)
    {
        try {
            DB::beginTransaction();

            $workflow = $this->workflowRepository->create([
                'name' => $data['name']
            ]);

            if (isset($data['slots']) && is_array($data['slots'])) {
                foreach ($data['slots'] as $slotData) {
                    $slot = $workflow->slots()->create([
                        'slot_number' => $slotData['slot_number'],
                        'description' => $slotData['description'],
                        'approval_method' => $slotData['approval_method'],
                        'parent_slot_id' => $slotData['parent_slot_id'] ?? null
                    ]);

                    if (isset($slotData['users']) && is_array($slotData['users'])) {
                        $this->workflowRepository->attachUsersToSlot($slot->id, $slotData['users']);
                    }
                }
            }

            DB::commit();
            return $workflow;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWorkflow($id, $data)
    {
        try {
            DB::beginTransaction();

            $workflow = $this->workflowRepository->update($id, [
                'name' => $data['name']
            ]);

            if (isset($data['slots']) && is_array($data['slots'])) {
                // Remove existing slots not in the update data
                $existingSlotIds = collect($data['slots'])->pluck('id')->filter();
                $workflow->slots()->whereNotIn('id', $existingSlotIds)->delete();

                foreach ($data['slots'] as $slotData) {
                    $slotId = $slotData['id'] ?? null;
                    $slotAttributes = [
                        'slot_number' => $slotData['slot_number'],
                        'description' => $slotData['description'],
                        'approval_method' => $slotData['approval_method'],
                        'parent_slot_id' => $slotData['parent_slot_id'] ?? null
                    ];

                    if ($slotId) {
                        $slot = $workflow->slots()->findOrFail($slotId);
                        $slot->update($slotAttributes);
                    } else {
                        $slot = $workflow->slots()->create($slotAttributes);
                    }

                    if (isset($slotData['user_ids']) && is_array($slotData['user_ids'])) {
                        $slot->users()->sync($slotData['user_ids']);
                    }
                }
            }

            DB::commit();
            return $workflow;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getWorkflowWithSlots($id)
    {
        return $this->workflowRepository->getWithSlots($id);
    }

    public function deleteWorkflow($id)
    {
        try {
            DB::beginTransaction();

            $workflow = $this->workflowRepository->find($id);
            $workflow->slots()->delete();
            $this->workflowRepository->delete($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
