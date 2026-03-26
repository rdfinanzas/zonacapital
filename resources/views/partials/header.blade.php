<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Start Navbar Links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" data-lte-target=".app-sidebar" href="#" role="button"
                    aria-label="Alternar menú">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="{{ route('home') ?? '#' }}" class="nav-link">Inicio</a>
            </li>
        </ul>
        <!-- End Start Navbar Links -->

        <!-- End Navbar Links -->
        <ul class="navbar-nav ms-auto">
            <!-- User Menu Dropdown -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">

                    <span class="d-none d-md-inline">{{ $usuario->Nombre ?? 'Usuario' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!-- User Image -->
                    <li class="user-header text-bg-primary">
                        <img src="{{ asset('adminlte/assets/img/user2-160x160.jpg') }}" class="rounded-circle shadow"
                            alt="User Image" />
                        <p>
                            {{ $usuario->nombre ?? 'Usuario' }}
                        </p>
                    </li>
                    <!-- Menu Footer -->
                    <li class="user-footer">

                        <a href="{{ route('logout') ?? '#' }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="btn btn-default btn-flat float-end">Cerrar sesión</a>
                        <form id="logout-form" action="{{ route('logout') ?? '#' }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
