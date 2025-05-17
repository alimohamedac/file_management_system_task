@extends('layouts.main')

@section('title', 'Pending Approvals')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Pending Approvals</h1>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Workflow</th>
                            <th>Current Slot</th>
                            <th>Waiting Since</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>
                                    <div>{{ $document->subject }}</div>
                                    <small class="text-muted">By: {{ $document->creator->name }}</small>
                                </td>
                                <td>
                                    {{ $document->currentWorkflowInstance->workflow->name }}
                                </td>
                                <td>
                                    @php
                                        $uniqueSlots = $document->currentWorkflowInstance->slotStatuses
                                            ->unique('workflow_slot_id')
                                            ->values();
                                    @endphp
                                    @foreach($uniqueSlots as $slotStatus)
                                        <div>Slot {{ $slotStatus->workflowSlot->slot_number }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    {{ $document->currentWorkflowInstance->created_at->diffForHumans() }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No documents pending your approval.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{--@if($documents->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $documents->links() }}
                </div>
            @endif--}}
        </div>
    </div>
</div>
@endsection
