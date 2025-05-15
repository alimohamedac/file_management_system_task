@extends('layouts.main')

@section('title', 'Workflows')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Workflows</h1>
        <a href="{{ route('workflows.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Workflow
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slots</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workflows as $workflow)
                            <tr>
                                <td>{{ $workflow->name }}</td>
                                <td>{{ $workflow->slots->count() }} slots</td>
                                <td>{{ $workflow->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-sm btn-info me-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('workflows.destroy', $workflow) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this workflow?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No workflows found. <a href="{{ route('workflows.create') }}" class="text-blue-600 hover:text-blue-900">Create one now</a>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
