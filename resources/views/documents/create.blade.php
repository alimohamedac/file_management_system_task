@extends('layouts.main')

@section('title', 'Upload New Document')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Upload New Document</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="documentForm">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Document Name</label>
                                    <input type="text" name="subject" id="subject"
                                           class="form-control @error('subject') is-invalid @enderror"
                                           value="{{ old('subject') }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="file" class="form-label">Document File</label>
                                    <input type="file" name="file" id="file"
                                           class="form-control @error('file') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.txt" required>
                                    <div class="form-text">Accepted formats: PDF, DOC, DOCX, TXT</div>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="workflow_id" class="form-label">Select Workflow</label>
                            <select name="workflow_id" id="workflow_id"
                                    class="form-select @error('workflow_id') is-invalid @enderror">
                                <option value="">Select a workflow</option>
                                @foreach($workflows as $workflow)
                                    <option value="{{ $workflow->id }}" {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}>
                                        {{ $workflow->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('workflow_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="workflowDetails" class="mb-4 d-none">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Workflow Details</h5>
                                </div>
                                <div class="card-body">
                                    <div id="workflowSlots"></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('documents.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const workflowSelect = document.getElementById('workflow_id');
    const workflowDetails = document.getElementById('workflowDetails');
    const workflowSlots = document.getElementById('workflowSlots');

    workflowSelect.addEventListener('change', async function() {
        const workflowId = this.value;
        workflowDetails.classList.add('d-none');
        workflowSlots.innerHTML = '';

        if (!workflowId) return;

        try {
            const response = await fetch(`/workflows/${workflowId}/details`);
            if (response.ok) {
                const workflow = await response.json();
                if (workflow.slots && workflow.slots.length > 0) {
                    workflowDetails.classList.remove('d-none');
                    workflow.slots.forEach(slot => {
                        const slotHtml = `
                            <div class="slot-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">Slot ${slot.slot_number}</h6>
                                        <p class="text-muted mb-0">${slot.description}</p>
                                    </div>
                                    <span class="badge ${slot.approval_method === 'single' ? 'bg-info' : 'bg-warning'}">
                                        ${slot.approval_method === 'single' ? 'Single' : 'Multiple'} Approval
                                    </span>
                                </div>
                                ${slot.parent_slot_id ? `
                                    <div class="mb-2">
                                        <small class="text-muted">Parent Slot: ${slot.parent_slot_id}</small>
                                    </div>
                                ` : ''}
                                <div>
                                    <small class="text-muted d-block mb-1">Assigned Users:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        ${slot.users.map(user => `
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-user me-1"></i>
                                                ${user.name}
                                            </span>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                        workflowSlots.insertAdjacentHTML('beforeend', slotHtml);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading workflow details:', error);
        }
    });

    // If there's a previously selected workflow (e.g., from validation error), trigger change
    if (workflowSelect.value) {
        workflowSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection

@section('styles')
<style>
.slot-item {
    transition: all 0.3s ease;
}
.slot-item:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endsection
