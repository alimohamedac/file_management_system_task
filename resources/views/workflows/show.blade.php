@extends('layouts.main')

@section('title', $workflow->name)

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">{{ $workflow->name }}</h1>
                <div>
                    <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-secondary me-2">
                        <i class="fas fa-edit"></i> Edit Workflow
                    </a>
                    <a href="{{ route('workflows.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Workflows
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Workflow Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong class="text-muted">Created:</strong>
                                <span>{{ $workflow->created_at->format('M d, Y H:i') }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong class="text-muted">Last Updated:</strong>
                                <span>{{ $workflow->updated_at->format('M d, Y H:i') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Workflow Slots</h5>
                </div>
                <div class="card-body">
                    @forelse ($workflow->slots->sortBy('slot_number') as $slot)
                        <div class="slot-item mb-4 p-3 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Slot {{ $slot->slot_number }}</h5>
                                    <p class="text-muted mb-0">{{ $slot->description }}</p>
                                </div>
                                <span class="badge {{ $slot->approval_method === 'single' ? 'bg-info' : 'bg-warning' }}">
                                    {{ ucfirst($slot->approval_method) }} Approval
                                </span>
                            </div>

                            @if($slot->parentSlot)
                                <div class="mb-3">
                                    <small class="text-muted">Parent Slot:</small>
                                    <span class="ms-2">{{ $slot->parentSlot->slot_number }}</span>
                                </div>
                            @endif

                            <div>
                                <h6 class="mb-2">Assigned Users</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($slot->users as $user)
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $user->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No slots defined for this workflow.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .slot-item {
        transition: all 0.3s ease;
    }
    .slot-item:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .badge {
        font-size: 0.875rem;
    }
</style>
@endsection
