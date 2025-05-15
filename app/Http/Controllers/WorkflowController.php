<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\WorkflowRepository;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    protected $workflowService;
    protected $workflowRepository;

    public function __construct(WorkflowService $workflowService, WorkflowRepository $workflowRepository)
    {
        $this->workflowService = $workflowService;
        $this->workflowRepository = $workflowRepository;
        $this->middleware('auth');
    }

    public function index()
    {
        $workflows = $this->workflowRepository->all();
        return view('workflows.index', compact('workflows'));
    }

    public function create()
    {
        $users = User::all();
        return view('workflows.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slots' => 'required|array',
            'slots.*.slot_number' => 'required|string',
            'slots.*.description' => 'required|string',
            'slots.*.approval_method' => 'required|in:single,multi',
            'slots.*.parent_slot_id' => 'nullable|string',
            'slots.*.users' => 'required|array',
            'slots.*.users.*' => 'exists:users,id'
        ]);

        $workflow = $this->workflowService->createWorkflow($validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow created successfully.');
    }

    public function show($id)
    {
        $workflow = $this->workflowService->getWorkflowWithSlots($id);

        return view('workflows.show', compact('workflow'));
    }

    public function getDetails($id)
    {
        $workflow = $this->workflowService->getWorkflowWithSlots($id);

        return response()->json([
            'id' => $workflow->id,
            'name' => $workflow->name,
            'slots' => $workflow->slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'slot_number' => $slot->slot_number,
                    'description' => $slot->description,
                    'approval_method' => $slot->approval_method,
                    'parent_slot_id' => $slot->parent_slot_id,
                    'users' => $slot->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name
                        ];
                    })
                ];
            })
        ]);
    }

    public function edit($id)
    {
        $workflow = $this->workflowService->getWorkflowWithSlots($id);
        $users = User::all();

        return view('workflows.edit', compact('workflow', 'users'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slots' => 'required|array',
            'slots.*.id' => 'nullable|exists:workflow_slots,id',
            'slots.*.slot_number' => 'required|string',
            'slots.*.description' => 'required|string',
            'slots.*.approval_method' => 'required|in:single,multi',
            'slots.*.parent_slot_id' => 'nullable|exists:workflow_slots,id',
            'slots.*.user_ids' => 'required|array',
            'slots.*.user_ids.*' => 'exists:users,id'
        ]);

        $workflow = $this->workflowService->updateWorkflow($id, $validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow updated successfully.');
    }

    public function destroy($id)
    {
        $this->workflowService->deleteWorkflow($id);

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow deleted successfully.');
    }
}
