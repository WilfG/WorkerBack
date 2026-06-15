@extends('layout.index')

@section('content')

<div class="content-body">

    <!-- Start Top Stats -->
    <div class="col-md-12">
        <ul class="topstats clearfix">
            <li class="arrow"></li>
            <li class="col-xs-6 col-lg-3 stats-card bg-primary">
                <span class="title"><i class="fa fa-dot-circle-o"></i> Nombre total de clients</span>
                <h3>{{$totClients}}</h3>
                <span class="diff"><b class="color-down"><i class="fa fa-caret-down"></i> FCFA</b> Aujourd'hui</span>
            </li>
            <li class="col-xs-6 col-lg-3">
                <span class="title"><i class="fa fa-calendar-o"></i> Nombre total d'artisan</span>
                <h3>{{$totWorkers}}</h3>
                <span class="diff"><b class="color-up"><i class="fa fa-caret-up"></i> FCFA</b> Ce mois seulement</span>
            </li>
            <li class="col-xs-6 col-lg-3">
                <span class="title"><i class="fa fa-dot-circle-o"></i> Recettes du jour (autres)</span>
                <h3>{{$totDayRecettes}}</h3>
                <span class="diff"><b class="color-down"><i class="fa fa-caret-down"></i> FCFA</b> Aujourd'hui</span>
            </li>
            <li class="col-xs-6 col-lg-3">
                <span class="title"><i class="fa fa-calendar-o"></i> Recette totale du mois(autres)</span>
                <h3>{{$totMonthRecettes}}</h3>
                <span class="diff"><b class="color-up"><i class="fa fa-caret-up"></i> FCFA</b> Ce mois seulement</span>
            </li>

           
        </ul>
    </div>
    <div class="col-md-12">
        <li class="col-xs-6 col-lg-6">
            <span class="title"><i class="fa fa-shopping-cart"></i> Tot. Global du mois</span>
            <h2 class="color-down"></h2>
            <span class="diff"><b class="color-up"><i class="fa fa-caret-up"></i> Ce mois seulement</b> </span>
        </li>
        
    </div>
    <!-- End Top Stats -->

    @if (session('errors'))
    <div class="mb-4 font-medium text-sm text-green-600 alert alert-danger">
        {{ session('errors') }}
    </div>
    @endif
    @if (session('success'))
    <div class="mb-4 font-medium text-sm text-green-600 alert alert-success">
        {{ session('success') }}
    </div>
    @endif


</div> <!-- End .row -->

@endsection