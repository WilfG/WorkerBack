<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker - Administration</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="{{asset('public/assets/css/new-style.css')}}" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>Worker</h3>
                <img src="{{asset('public/assets/img/logo.png')}}" alt="Logo" class="logo-small">
            </div>

            <ul class="list-unstyled components">
                <li class="{{Request::is('dashboard') ? 'active' : ''}}">
                    <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="Tableau de bord">
                        <i class="fas fa-home"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="{{Request::is('users*') ? 'active' : ''}}">
                    <a href="{{route('users.index')}}" data-bs-toggle="tooltip" data-bs-placement="right" title="Utilisateurs">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li class="{{Request::is('categories*') ? 'active' : ''}}">
                    <a href="{{route('categories.index')}}" data-bs-toggle="tooltip" data-bs-placement="right" title="Catégories">
                        <i class="fas fa-th-list"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                <li class="{{Request::is('professions*') ? 'active' : ''}}">
                    <a href="{{route('professions.index')}}" data-bs-toggle="tooltip" data-bs-placement="right" title="Professions">
                        <i class="fas fa-briefcase"></i>
                        <span>Professions</span>
                    </a>
                </li>
                <li class="{{Request::is('jobs*') ? 'active' : ''}}">
                    <a href="{{route('jobs.index')}}" data-bs-toggle="tooltip" data-bs-placement="right" title="Travaux">
                        <i class="fas fa-tasks"></i>
                        <span>Travaux</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a href="#" class="sidebar-open-button">
                        <i class="fas fa-bars"></i>
                    </a>
                    <a href="#" class="sidebar-open-button-mobile d-lg-none">
                        <i class="fas fa-bars"></i>
                    </a>

                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                {{ auth()->user()->firstname }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/assets/js/main.js')}}"></script>
    <script>
        $(document).ready(function() {
            // État initial du sidebar (fermé sur mobile, ouvert sur desktop)
            if ($(window).width() <= 991.98) {
                $('body').removeClass('sidebar-open').addClass('sidebar-closed');
            }

            // Toggle sidebar pour desktop
            $('.sidebar-open-button').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-closed');
            });

            // Toggle sidebar pour mobile
            $('.sidebar-open-button-mobile').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-open');
            });

            // Fermer le sidebar au clic en dehors sur mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 991.98) {
                    if (!$(e.target).closest('#sidebar').length && 
                        !$(e.target).closest('.sidebar-open-button-mobile').length && 
                        $('body').hasClass('sidebar-open')) {
                        $('body').removeClass('sidebar-open');
                    }
                }
            });

            // Gérer le redimensionnement de la fenêtre
            $(window).on('resize', function() {
                if ($(window).width() <= 991.98) {
                    $('body').removeClass('sidebar-closed').removeClass('sidebar-open');
                } else {
                    $('body').removeClass('sidebar-open');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
