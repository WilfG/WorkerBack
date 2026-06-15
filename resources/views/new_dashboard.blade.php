@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 mb-3">Tableau de bord</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="title">Total Clients</div>
                <div class="value">{{ $totClients }}</div>
                <div class="trend text-success">
                    <!-- <i class="fas fa-arrow-up"></i> +5.27% -->
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <div class="title">Total Artisans</div>
                <div class="value">{{ $totWorkers }}</div>
                <div class="trend text-success">
                    <!-- <i class="fas fa-arrow-up"></i> +3.5% -->
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="title">Recettes du jour</div>
                <div class="value">{{ number_format($totDayRecettes, 0, ',', ' ') }} FCFA</div>
                <div class="trend text-danger">
                    <!-- <i class="fas fa-arrow-down"></i> -2.4% -->
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="title">Recettes du mois</div>
                <div class="value">{{ number_format($totMonthRecettes, 0, ',', ' ') }} FCFA</div>
                <div class="trend text-success">
                    <!-- <i class="fas fa-arrow-up"></i> +8.4% -->
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-calendar-days"></i>
                </div>
                <div class="title">Recettes 3 derniers mois</div>
                <div class="value">{{ number_format($totThreeMonthsRecettes, 0, ',', ' ') }} FCFA</div>
                <div class="trend text-info">
                    <!-- <i class="fas fa-arrow-up"></i> -->
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-year"></i>
                </div>
                <div class="title">Recettes année en cours</div>
                <div class="value">{{ number_format($totYearRecettes, 0, ',', ' ') }} FCFA</div>
                <div class="trend text-success">
                    <!-- <i class="fas fa-arrow-up"></i> -->
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="title">Recettes de tous les temps</div>
                <div class="value">{{ number_format($totAllTimeRecettes, 0, ',', ' ') }} FCFA</div>
                <div class="trend text-warning">
                    <!-- <i class="fas fa-arrow-up"></i> -->
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Derniers travaux</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Artisan</th>
                                    <th>Service</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Add your jobs data here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Professions Populaires</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($professionsPopulaires as $profession)
                            <li>{{ $profession->name }} ({{ $profession->jobs_count }} jobs)</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle sidebar
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar, #content').toggleClass('active');
        });
    });
</script>
@endpush
