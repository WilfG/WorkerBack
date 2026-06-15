@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">Gestion des Professions</h1>
                <a href="{{ route('professions.create') }}" class="btn btn-accent">
                    <i class="fas fa-plus"></i> Nouvelle Profession
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Liste des Professions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($professions as $profession)
                        <tr>
                            <td>{{ $profession->name }}</td>
                            <td>{{ $profession->category->name }}</td>
                            <td>{{ Str::limit($profession->description, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $profession->status ? 'success' : 'danger' }}">
                                    {{ $profession->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('professions.edit', $profession->id) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('professions.destroy', $profession->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette profession ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($professions->hasPages())
            <div class="mt-4">
                {{ $professions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
