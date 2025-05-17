@extends('layouts.main')

@section('title', 'Create Workflow')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    .select2-container {
        width: 100% !important;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create New Workflow</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('workflows.store') }}" method="POST" id="workflowForm">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Workflow Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
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
                            <!-- Slots will be added here dynamically -->
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('workflows.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Workflow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let slotCount = 0;
        const users = @json($users);

        function initializeSelect2ForSlot(slotIndex) {
            const select = $(`#slot-${slotIndex} select[multiple]`);
            select.select2({
                placeholder: 'Select users',
                width: '100%'
            });
        }

        function createSlotForm(index) {
            let userOptions = '';
            users.forEach(user => {
                userOptions += `<option value="${user.id}">${user.name}</option>`;
            });
            return `
                <div class="slot-form position-relative mb-3" id="slot-${index}">
                    <button type="button" class="btn btn-danger btn-sm remove-slot" data-slot="${index}">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slot Number</label>
                            <input type="text" name="slots[${index}][slot_number]"
                                   class="form-control" required
                                   placeholder="e.g., 1.0, 1.1, 2.0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="slots[${index}][description]"
                                   class="form-control" required
                                   placeholder="e.g., Manager Approval">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Slot (Optional)</label>
                            <input type="text" name="slots[${index}][parent_slot_number]"
                                   class="form-control"
                                   placeholder="e.g., 1.0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Approval Method</label>
                            <select name="slots[${index}][approval_method]" class="form-select" required>
                                <option value="single">Single Approval</option>
                                <option value="multi">Multiple Approvals</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Users</label>
                        <select name="slots[${index}][users][]" class="form-select" multiple required>
                            ${userOptions}
                        </select>
                    </div>
                </div>
            `;
        }

        document.getElementById('addSlot').addEventListener('click', function() {
            const container = document.getElementById('slotsContainer');
            container.insertAdjacentHTML('beforeend', createSlotForm(slotCount++));
            $(`#slot-${slotCount-1} select[multiple]`).select2({placeholder: 'Select users'});
        });

        document.getElementById('slotsContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-slot')) {
                const button = e.target.closest('.remove-slot');
                const slotId = button.dataset.slot;
                document.getElementById(`slot-${slotId}`).remove();
            }
        });

        // Add initial slot
        document.getElementById('addSlot').click();
    });
</script>
@endsection
