@extends('layouts.main')

@section('title', 'Documents')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Documents</h1>
                <a href="{{ route('documents.create') }}" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Upload New Document
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Workflow</th>
                                    <th>Current Status</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $document)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $document->subject }}</div>
                                            <div class="text-muted small">{{ $document->description }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $document->currentWorkflowInstance?->workflow->name ?? 'No workflow' }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $status = $document->currentWorkflowInstance?->status ?? 'draft';
                                                $statusClass = match($status) {
                                                    'completed' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'pending' => 'bg-warning',
                                                    'in_progress' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                        </td>
                                        <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{--<a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-info" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>--}}
                                                @if(!$document->currentWorkflowInstance)
                                                    <form action="{{ route('documents.destroy', $document) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                                onclick="return confirm('Are you sure you want to delete this document?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No documents found. <a href="{{ route('documents.create') }}" class="text-primary">Upload one now</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{--@if($documents->hasPages())
                        <div class="mt-4">
                            {{ $documents->links() }}
                        </div>
                    @endif--}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    .btn-group > .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@endsection
