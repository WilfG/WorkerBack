<div id="top" class="clearfix">

    <!-- Start App Logo -->
    <div class="applogo bg-primary">
        <a href="/dashboard" class="logo text-light">Worker</a>
    </div>
    <!-- End App Logo -->

    <!-- Start Sidebar Show Hide Button -->
    <a href="#" class="sidebar-open-button"><i class="fa fa-bars"></i></a>
    <a href="#" class="sidebar-open-button-mobile"><i class="fa fa-bars"></i></a>
    <!-- End Sidebar Show Hide Button -->

    <!-- Start Searchbox -->
    <!-- <form class="searchform">
        <input type="text" class="searchbox" id="searchbox" placeholder="Search">
        <span class="searchbutton"><i class="fa fa-search"></i></span>
    </form> -->
    <!-- End Searchbox -->

    <!-- Start Top Menu -->
    <!-- <ul class="topmenu">
        <li><a href="#">Files</a></li>
        <li><a href="#">Authors</a></li>
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle">My Files <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="#">Videos</a></li>
                <li><a href="#">Pictures</a></li>
                <li><a href="#">Blog Posts</a></li>
            </ul>
        </li>
    </ul> -->
    <!-- End Top Menu -->

    <!-- Start Sidepanel Show-Hide Button -->
    <!-- <a href="#sidepanel" class="sidepanel-open-button"><i class="fa fa-outdent"></i></a> -->
    <!-- End Sidepanel Show-Hide Button -->

    <!-- Start Top Right -->
    <ul class="top-right">

        <li class="dropdown link">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle hdbutton">Mes modules <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-list">
                <li><a href="{{url('/dashboard')}}"><i class="fa falist fa-money"></i>Tableau de bord</a></li>
               <li><a href="{{route('users.index')}}"><i class="fa falist fa-user"></i>Gestion des utilisateurs</a></li>
            </ul>
        </li>

            <!-- <li class="link">
                <a href="#" class="notifications">6</a>
            </li> -->

        <li class="dropdown link">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle profilebox"><img src="/storage/{{auth()->user()->photo}}" alt="img"><b>{{auth()->user()->firstname . ' '.auth()->user()->lastname }}</b><span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-list dropdown-menu-right">
                <li role="presentation" class="dropdown-header">Profile</li>
                <!-- <li><a href="#"><i class="fa falist fa-inbox"></i>Inbox<span class="badge label-danger">4</span></a></li> -->
                <!-- <li><a href="#"><i class="fa falist fa-file-o"></i>Files</a></li>
                <li><a href="#"><i class="fa falist fa-wrench"></i>Settings</a></li> -->
                <!-- <li><a href="#"><i class="fa falist fa-lock"></i> Lockscreen</a></li> -->
                <li class="divider"></li>
                <li>
                    <a href="#" onclick="document.getElementById('logout-form').submit()">
                        <i class="fa fa-lock"></i>
                        Deconnexion
                        <form action="/logout" method="POST" id="logout-form">
                            @csrf
                        </form>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
    <!-- End Top Right -->

</div>