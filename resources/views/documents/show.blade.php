@extends('layouts.main')

@section('title', $document->name)

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">{{ $document->name }}</h1>
                <div>
                    {{--<a href="{{ route('documents.download', $document) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-download me-1"></i> Download Document
                    </a>--}}
                    <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Document Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Document Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Created By</h6>
                            <p class="mb-0">{{ $document->creator->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Created At</h6>
                            <p class="mb-0">{{ $document->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Status</h6>
                            <div>
                                @php
                                    $status = $document->status ?? 'draft';
                                    $statusClass = match($status) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'in_progress' => 'bg-warning',
                                        'pending' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                            </div>
                        </div>

                        @if($document->description)
                            <div class="col-12 mt-3">
                                <h6 class="text-muted mb-1">Description</h6>
                                <p class="mb-0">{{ $document->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Current Workflow Status -->
            @if($document->currentWorkflowInstance)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Current Workflow: {{ $document->currentWorkflowInstance->workflow?->name }}
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="workflow-slots">
                            @php
                                $slots = $document->currentWorkflowInstance->workflow?->slots ?? collect();
                                $sortedSlots = $slots->sortBy('slot_number');
                            @endphp
                            @foreach($sortedSlots as $slot)
                                @php
                                    $slotStatuses = $document->currentWorkflowInstance->slotStatuses ?? collect();
                                    $slotStatus = $slotStatuses->where('workflow_slot_id', $slot->id)->first();
                                @endphp
                                <div class="slot-item card bg-light mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="card-title mb-1">Slot {{ $slot->slot_number }}</h6>
                                                <p class="text-muted mb-0">{{ $slot->description }}</p>
                                            </div>
                                            <div class="text-end">
                                                <div class="mb-1">
                                                    <span class="badge bg-info">
                                                        {{ $slot->approval_method == 'single' ? 'Single Approval' : 'Multi Approval' }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-muted small">Assigned Users:</span>
                                                    @php
                                                        $users = $slot->users ?? collect();
                                                        $userNames = $users->pluck('name')->join(', ');
                                                    @endphp
                                                    <span class="ms-1 small">{{ $userNames }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $statusClass = match($slotStatus?->status ?? 'pending') {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $statusClass }}">
                                                {{ $slotStatus ? ucfirst($slotStatus->status) : 'Pending' }}
                                            </span>
                                            @if($slotStatus && $slotStatus->signed_at)
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($slotStatus->signed_at)->format('M d, Y H:i') }}
                                                </small>
                                            @endif
                                        </div>

                                        @if($slotStatus && $slotStatus->user)
                                            <div class="mt-3 pt-3 border-top">
                                                {{--<div class="d-flex align-items-center text-muted small">
                                                    <i class="fas fa-user-check me-2"></i>
                                                    <span>Action by {{ $slotStatus->user?->name }}</span>
                                                    <i class="fas fa-clock ms-3 me-2"></i>
                                                    <span>{{ $slotStatus->updated_at?->format('M d, Y H:i') }}</span>
                                                </div>--}}

                                                @if($slotStatus->comment)
                                                    <div class="mt-2">
                                                        <small class="text-muted">Comment:</small>
                                                        <p class="mb-0 mt-1">{{ $slotStatus->comment }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @php
                                            $isCompleted = $document->currentWorkflowInstance?->isCompleted() ?? false;
                                            $canShowActions = $slot->isCurrentSlotForUser(Illuminate\Support\Facades\Auth::user()) && 
                                                            ($slotStatus === null || $slotStatus->signed_at === null) && 
                                                            !$isCompleted;
                                        @endphp
                                        @if($canShowActions)
                                            <div class="mt-3 pt-3 border-top">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <form action="{{ route('documents.approve', $document) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                                                            <div class="mb-3">
                                                                <label for="comment" class="form-label">Comment (Optional)</label>
                                                                <textarea name="comments" id="comment" rows="2"
                                                                          class="form-control @error('comments') is-invalid @enderror"></textarea>
                                                                @error('comments')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <button type="submit" class="btn btn-success w-100">
                                                                <i class="fas fa-check me-1"></i> Approve
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <form action="{{ route('documents.reject', $document) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                                                            <div class="mb-3">
                                                                <label for="reject_comment" class="form-label">Rejection Reason</label>
                                                                <textarea name="comments" id="reject_comment" rows="2"
                                                                          class="form-control @error('comments') is-invalid @enderror" required></textarea>
                                                                @error('comments')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <button type="submit" class="btn btn-danger w-100">
                                                                <i class="fas fa-times me-1"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Workflow History -->
            @if(($document->workflowInstances ?? collect())->count() > 1)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Workflow History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @php
                                $instances = $document->workflowInstances ?? collect();
                                $sortedInstances = $instances->sortByDesc('created_at')->skip(1);
                            @endphp
                            @foreach($sortedInstances as $instance)
                                <div class="timeline-item pb-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="me-3">
                                            <h6 class="mb-1">{{ $instance->workflow?->name }}</h6>
                                            <div class="text-muted small">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Started: {{ $instance->created_at?->format('M d, Y H:i') }}
                                            </div>
                                        </div>
                                        @php
                                            $statusClass = match($instance->status) {
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($instance->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-left: 1.5rem;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -0.4375rem;
    top: 0.25rem;
    width: 0.875rem;
    height: 0.875rem;
    border-radius: 50%;
    border: 2px solid #adb5bd;
    background: #fff;
}
.slot-item {
    transition: all 0.3s ease;
}
.slot-item:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endsection
