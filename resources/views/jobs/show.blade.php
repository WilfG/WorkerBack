@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">Détails du Travail</h1>
                <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du travail</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Titre</label>
                            <p>{{ $job->title }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prix</label>
                            <p>{{ number_format($job->price, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Client</label>
                            <p>{{ $job->client ? ($job->client->firstname ?? '') . ' ' . ($job->client->lastname ?? '') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Artisan</label>
                            <p>{{ $job->worker ? ($job->worker->firstname ?? '') . ' ' . ($job->worker->lastname ?? '') : 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <p>
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
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de création</label>
                            <p>{{ $job->created_at ? $job->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <p>{{ $job->description }}</p>
                    </div>
                </div>
            </div>

            @if($job->images->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Images du travail</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($job->images as $image)
                        <div class="col-md-4 mb-3">
                            <img src="{{ asset('public/storage/' . $image->path) }}" alt="Image du travail" class="img-fluid rounded">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Localisation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Adresse</label>
                        <p>{{ $job->address }}</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('jobs.destroy', $job->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 mb-2" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce travail ?')">
                            <i class="fas fa-trash"></i> Supprimer le travail
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
