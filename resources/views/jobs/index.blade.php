@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">Gestion des Travaux</h1>
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
            <h5 class="card-title mb-0">Liste des Travaux</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Artisan</th>
                            <th>Service</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                        <tr>
                             <td>{{ $job->client ? $job->client->name : 'N/A' }}</td>
                            <td>{{ $job->worker ? $job->worker->name : 'N/A' }}</td>
                             <td>{{ $job->title }}</td>
                            <td>{{ number_format($job->price, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge bg-{{ $job->status == 'completed' ? 'success' : ($job->status == 'in_progress' ? 'warning' : 'info') }}">
                                    @switch($job->status)
                                        @case('pending')
                                            En attente
                                            @break
                                        @case('in_progress')
                                            En cours
                                            @break
                                        @case('completed')
                                            Terminé
                                            @break
                                        @default
                                            {{ $job->status }}
                                    @endswitch
                                </span>
                            </td>
                            <td>{{ $job->created_at ? $job->created_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('jobs.destroy', $job->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce travail ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($jobs->hasPages())
            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
