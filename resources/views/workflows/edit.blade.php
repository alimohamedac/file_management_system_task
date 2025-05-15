@extends('layouts.main')

@section('title', 'Edit Workflow')

@section('styles')
<style>
    .slot-form {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .slot-form .remove-slot {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Workflow: {{ $workflow->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('workflows.update', $workflow) }}" method="POST" id="workflowForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Workflow Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $workflow->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Workflow Slots</h6>
                            <button type="button" class="btn btn-secondary" id="addSlot">
                                <i class="fas fa-plus"></i> Add Slot
                            </button>
                        </div>
                        
                        <div id="slotsContainer">
                            <!-- Existing slots will be loaded here -->
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Workflow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let slotCount = 0;
        const existingSlots = @json($workflow->slots->map(function($slot) {
            return [
                'id' => $slot->id,
                'slot_number' => $slot->slot_number,
                'description' => $slot->description,
                'approval_method' => $slot->approval_method,
                'parent_slot_id' => $slot->parent_slot_id,
                'users' => $slot->users->pluck('id')->toArray()
            ];
        }));

        function createSlotForm(index, slot = null) {
            return `
                <div class="slot-form position-relative mb-3" id="slot-${index}">
                    <button type="button" class="btn btn-danger btn-sm remove-slot" data-slot="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    ${slot ? `<input type="hidden" name="slots[${index}][id]" value="${slot.id}">` : ''}
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slot Number</label>
                            <input type="text" name="slots[${index}][slot_number]" 
                                   class="form-control" required 
                                   value="${slot ? slot.slot_number : ''}" 
                                   placeholder="e.g., 1.0, 1.1, 2.0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="slots[${index}][description]" 
                                   class="form-control" required 
                                   value="${slot ? slot.description : ''}" 
                                   placeholder="e.g., Manager Approval">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Slot (Optional)</label>
                            <input type="text" name="slots[${index}][parent_slot_id]" 
                                   class="form-control" 
                                   value="${slot ? (slot.parent_slot_id || '') : ''}" 
                                   placeholder="e.g., 1.0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Approval Method</label>
                            <select name="slots[${index}][approval_method]" class="form-select" required>
                                <option value="single" ${slot && slot.approval_method === 'single' ? 'selected' : ''}>Single Approval</option>
                                <option value="multi" ${slot && slot.approval_method === 'multi' ? 'selected' : ''}>Multiple Approvals</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Users</label>
                        <select name="slots[${index}][users][]" class="form-select" multiple required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" ${slot && slot.users.includes({{ $user->id }}) ? 'selected' : ''}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `;
        }

        document.getElementById('addSlot').addEventListener('click', function() {
            const container = document.getElementById('slotsContainer');
            container.insertAdjacentHTML('beforeend', createSlotForm(slotCount++));
        });

        document.getElementById('slotsContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-slot')) {
                const button = e.target.closest('.remove-slot');
                const slotId = button.dataset.slot;
                document.getElementById(`slot-${slotId}`).remove();
            }
        });

        // Load existing slots
        existingSlots.forEach(slot => {
            const container = document.getElementById('slotsContainer');
            container.insertAdjacentHTML('beforeend', createSlotForm(slotCount++, slot));
        });

        // Add an empty slot if no slots exist
        if (existingSlots.length === 0) {
            document.getElementById('addSlot').click();
        }
    });
</script>
@endsection
