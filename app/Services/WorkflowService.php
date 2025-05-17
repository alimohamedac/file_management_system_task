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

            $createdSlots = [];

            if (!empty($data['slots'])) {
                foreach ($data['slots'] as $slotData) {
                    $createdSlots[$slotData['slot_number']] = $workflow->slots()->create([
                        'slot_number' => $slotData['slot_number'],
                        'description' => $slotData['description'],
                        'approval_method' => $slotData['approval_method'],
                    ]);
                }

                foreach ($data['slots'] as $slotData) {
                    $slot = $createdSlots[$slotData['slot_number']];
                    $parentSlotNumber = $slotData['parent_slot_number'] ?? null;
                    $parentSlot = $parentSlotNumber ? $createdSlots[$parentSlotNumber] ?? null : null;

                    $slot->update(['parent_slot_id' => $parentSlot?->id]);

                    if (!empty($slotData['users'])) {
                        $slot->users()->sync($slotData['users']);
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

            $existingSlotIds = collect($data['slots'])->pluck('id')->filter()->toArray();
            $workflow->slots()->whereNotIn('id', $existingSlotIds)->delete();

            $savedSlots = [];

            foreach ($data['slots'] as $slotData) {
                $slotId = $slotData['id'] ?? null;

                $slotAttributes = [
                    'slot_number' => $slotData['slot_number'],
                    'description' => $slotData['description'],
                    'approval_method' => $slotData['approval_method'],
                ];

                if ($slotId) {
                    $slot = $workflow->slots()->where('id', $slotId)->firstOrFail();
                    $slot->update($slotAttributes);
                } else {
                    $slot = $workflow->slots()->create($slotAttributes);
                }

                $savedSlots[$slotData['slot_number']] = $slot;
            }

            foreach ($data['slots'] as $slotData) {
                $slot = $savedSlots[$slotData['slot_number']];
                $parentSlotNumber = $slotData['parent_slot_number'] ?? null;
                $parentSlot = $parentSlotNumber ? $savedSlots[$parentSlotNumber] ?? null : null;

                $slot->update(['parent_slot_id' => $parentSlot?->id]);

                if (!empty($slotData['users'])) {
                    $slot->users()->sync($slotData['users']);
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
