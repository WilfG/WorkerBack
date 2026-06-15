<div class="sidebar clearfix bg-primary">

    <ul class="sidebar-panel nav">
        <li class="sidetitle text-secondary">MENU PRINCIPAL</li>
        @if (Request::is('dashboard/*'))
        <li><a href="/dashboard"><span class="icon color5"><i class="fa fa-home"></i></span>Dashboard<span class="label label-default">2</span></a></li>
        @endif
        <!-- <li><a href="mailbox.html"><span class="icon color6"><i class="fa fa-envelope-o"></i></span>Mailbox<span class="label label-default">19</span></a></li> -->

        @if (Request::is('facturation_gestion_financiere/*'))
        <li>
            <a href="#"><span class="icon color7"><i class="fa fa-money"></i></span>Dépenses<span class="caret"></span></a>
            <ul>
                <li><a href="{{route('expenses.index')}}">Liste des dépenses</a></li>
                <li><a href="{{route('categ_expenses.index')}}">Liste des catégories de dépenses</a></li>
                <li>
                    <a href="{{route('expenses_requests.index')}}">Liste des requêtes de dépense</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="{{route('recettes.index')}}"><span class="icon color7"><i class="fa fa-money"></i></span>Recettes</a>
        </li>
        @endif

        @if (Request::is('gestion_stock/*'))
        <li>
            <a href="#"><span class="icon color7"><i class="fa fa-money"></i></span>Gestion des médicaments<span class="caret"></span></a>
            <ul>
                <li><a href="{{route('drugs.index')}}">Liste des médicaments</a></li>
                <li><a href="{{route('purchases.index')}}">Approvisionnement </a></li>
                
                <li><a href="{{route('sales.index')}}">--Ventes / Sorties Médicaments</a></li>
                <li><a href="{{route('stockmovements')}}">Fiche de stock médicaments</a></li>
            </ul>
        </li>
        <li>
            <a href="#"><span class="icon color7"><i class="fa fa-money"></i></span>Gestion des matériels<span class="caret"></span></a>
            <ul>
                <li><a href="{{route('materiels.index')}}">Liste des matériels</a></li>
                <li><a href="{{route('purchases_mat.index')}}">Approvisionnement </a></li>
                <li><a href="{{route('usages.index')}}">--Sorties Matériel </a></li>
                <li><a href="{{route('stockmovementsMats')}}">Fiche de stock matériels</a></li>
                <li><a href="{{route('magasins.index')}}">Mouvement magasin</a></li>
            </ul>
        </li>
        
        @endif

        @if (Request::is('gestion_utilisateur/*'))
        <li>
            <a href="#"><span class="icon color7"><i class="fa fa-money"></i></span>Gestion des utilisateurs<span class="caret"></span></a>
            <ul>
                <li><a href="{{route('users.index')}}">Liste des utilisateurs</a></li>
            </ul>

        </li>
        @endif

        
</div>