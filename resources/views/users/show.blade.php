@extends('layout.new_main')
@section('title', 'Détails de l\'utilisateur')

@push('styles')
<style>
    .profile-header {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .avatar-large {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #0E4561;
    }

    .profile-stats {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
    }

    .stat-item {
        text-align: center;
        padding: 0.5rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #0E4561;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .content-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 1rem;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: #0E4561;
        border-bottom: 3px solid #0E4561;
        background: none;
    }

    .job-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .job-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .portfolio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1rem 0;
    }

    .portfolio-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }

    .portfolio-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .portfolio-item:hover img {
        transform: scale(1.05);
    }

    .rating-stars {
        color: #ffc107;
    }

    .btn-primary {
        background-color: #0E4561;
        border-color: #0E4561;
    }

    .btn-primary:hover {
        background-color: #0a3449;
        border-color: #0a3449;
    }

    .text-primary {
        color: #0E4561 !important;
    }

    .status-badge {
        padding: 0.5em 1em;
        border-radius: 20px;
        font-size: 0.85em;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary">
                    <i class="fas fa-user me-2"></i>
                    Profil de l'utilisateur
                </h1>
                <div>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>

            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        @if($user->profile_photo)
                        <img src="{{ asset('public/storage/' . $user->profile_photo) }}" alt="Avatar" class="avatar-large" style="display: block;margin: 0 auto;width: 150px;border-radius: 50%;">
                        @else
                        <div class="avatar-large d-flex align-items-center justify-content-center bg-secondary text-white" style="margin: 0 auto;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-1">{{ $user->name }}</h2>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                        </p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-phone me-2"></i>{{ $user->phone }}
                        </p>
                        <p class="mb-2">
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'worker' ? 'bg-success' : 'bg-info') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }} ms-2">
                                {{ $user->email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <div class="profile-stats">
                            @if($user->role === 'worker')
                            <div class="row">
                                <div class="col-6 stat-item">
                                    <div class="stat-value">{{ $user->completedJobs->count() }}</div>
                                    <div class="stat-label">Travaux</div>
                                </div>
                                <div class="col-6 stat-item">
                                    <div class="stat-value">
                                        <i class="fas fa-star rating-stars"></i>
                                        {{ number_format($user->rating, 1) }}
                                    </div>
                                    <div class="stat-label">Note</div>
                                </div>
                                <div class="col-12 stat-item mt-2">
                                    <div class="stat-value">
                                        <span class="badge {{ $user->hasActiveSubscription() ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $user->hasActiveSubscription() ? 'Abonné' : 'Non abonné' }}
                                        </span>
                                    </div>
                                    <div class="stat-label">Statut</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#info" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Informations
                        </a>
                    </li>
                    @if($user->role === 'worker')
                    <li class="nav-item">
                        <a class="nav-link" id="jobs-tab" data-bs-toggle="tab" href="#jobs" role="tab">
                            <i class="fas fa-briefcase me-2"></i>Travaux
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="portfolio-tab" data-bs-toggle="tab" href="#portfolio" role="tab">
                            <i class="fas fa-images me-2"></i>Portfolio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab">
                            <i class="fas fa-star me-2"></i>Avis
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-3">Informations personnelles</h4>
                                <table class="table">
                                    <tr>
                                        <th width="30%">Inscrit le</th>
                                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dernière mise à jour</th>
                                        <td>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    @if($user->role === 'worker')
                                    <tr>
                                        <th>Profession</th>
                                        <td>{{ $user->profession ? $user->profession->name : 'Non spécifié' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catégories</th>
                                        <td>
                                            @forelse($user->categories as $category)
                                            <span class="badge bg-info me-1">{{ $category->name }}</span>
                                            @empty
                                            Non spécifié
                                            @endforelse
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            @if($user->role === 'worker')
                            <div class="col-md-6">
                                <h4 class="mb-3">Statistiques détaillées</h4>
                                <table class="table">
                                    <tr>
                                        <th width="30%">Total des travaux</th>
                                        <td>{{ $user->completedJobs->count() }}</td>
                                    </tr>
                                    <tr>
                                        <th>Note moyenne</th>
                                        <td>
                                            <span class="rating-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $user->rating >= $i ? '' : '-half' }}"></i>
                                                    @endfor
                                            </span>
                                            ({{ number_format($user->rating, 1) }}/5)
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Abonnement</th>
                                        <td>
                                            @if($user->hasActiveSubscription())
                                            <span class="badge bg-success">Actif jusqu'au {{ $user->subscription_end_date ? $user->subscription_end_date->format('d/m/Y') : 'N/A' }}</span>
                                            @else
                                            <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($user->role === 'worker')
                    <div class="tab-pane fade" id="jobs" role="tabpanel">
                        <h4 class="mb-3">Travaux réalisés</h4>
                        @forelse($user->completedJobs as $job)
                        <div class="job-card">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-1">{{ $job->title }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ $job->created_at ? $job->created_at->format('d/m/Y') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-check me-1"></i>Terminé
                                    </span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="{{ route('jobs.show', $job->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Voir les détails
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-briefcase fa-3x mb-3"></i>
                            <p>Aucun travail réalisé pour le moment</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="portfolio" role="tabpanel">
                        <h4 class="mb-3">Portfolio</h4>
                        <div class="portfolio-grid">
                            @if($user->workImages)
                            @foreach($user->workImages as $image)
                            <div class="portfolio-item">
                                <img src="{{ asset('public/storage/' . $image->image) }}" alt="Travail"
                                    class="img-fluid" data-bs-toggle="modal"
                                    data-bs-target="#imageModal{{ $image->id }}">
                            </div>
                            @endforeach
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-images fa-3x mb-3"></i>
                                <p>Aucune image dans le portfolio</p>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                
            </div>
        </div>

        <div class="tab-pane fade" id="reviews" role="tabpanel">
            <h4 class="mb-3">Avis des clients</h4>
            @forelse($user->ratings as $rating)
            <div class="job-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="rating-stars mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $rating->rating >= $i ? '' : '-o' }}"></i>
                                @endfor
                        </div>
                        <p class="mb-1">{{ $rating->comment }}</p>
                        <small class="text-muted">
                            Par {{ $rating->client ? $rating->client->name : 'Client inconnu' }} le {{ $rating->created_at ? $rating->created_at->format('d/m/Y') : 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-star fa-3x mb-3"></i>
                <p>Aucun avis pour le moment</p>
            </div>
            @endforelse
        </div>
        @endif
    </div>
</div>
</div>
</div>
</div>

@if($user->role === 'worker')
@foreach($user->workImages as $image)
<div class="modal fade" id="imageModal{{ $image->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image du portfolio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img src="{{ asset('public/storage/' . $image->image) }}" alt="Travail" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection