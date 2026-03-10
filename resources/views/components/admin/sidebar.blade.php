<aside class="main-sidebar elevation-4 sidebar-light-primary">

    <a href="{{ route('admin.index') }}" class="brand-link bg-primary bg-light bg-gray-light">
        <span class="brand-text font-weight-strong">Cash Manager</span>
    </a>

    <div
        class="sidebar os-host os-host-overflow os-host-overflow-y os-host-resize-disabled os-host-transition os-host-scrollbar-horizontal-hidden os-theme-light">
        <div class="os-resize-observer-host observed">
            <div class="os-resize-observer" style="left: 0px; right: auto;"></div>
        </div>
        <div class="os-size-auto-observer observed" style="height: calc(100% + 1px); float: left;">
            <div class="os-resize-observer"></div>
        </div>
        <div class="os-content-glue" style="margin: 0px -8px; width: 249px; height: 874px;"></div>
        <div class="os-padding mt-1">
            <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
                <div class="os-content" style="padding: 0px 8px; height: 100%; width: 100%;">

                    <div class="form-inline">
                        <div class="input-group" data-widget="sidebar-search">
                            <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                                aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-sidebar">
                                    <i class="fas fa-search fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div class="sidebar-search-results">
                            <div class="list-group"><a href="#" class="list-group-item">
                                    <div class="search-title"><strong class="text-dark"></strong>N<strong
                                            class="text-dark"></strong>o<strong class="text-dark"></strong> <strong
                                            class="text-dark"></strong>e<strong class="text-dark"></strong>l<strong
                                            class="text-dark"></strong>e<strong class="text-dark"></strong>m<strong
                                            class="text-dark"></strong>e<strong class="text-dark"></strong>n<strong
                                            class="text-dark"></strong>t<strong class="text-dark"></strong> <strong
                                            class="text-dark"></strong>f<strong class="text-dark"></strong>o<strong
                                            class="text-dark"></strong>u<strong class="text-dark"></strong>n<strong
                                            class="text-dark"></strong>d<strong class="text-dark"></strong>!<strong
                                            class="text-dark"></strong></div>
                                    <div class="search-path"></div>
                                </a></div>
                        </div>
                    </div>

                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview"
                            role="menu" data-accordion="false">

                            <li class="nav-item">
                                <a href="{{ route('admin.index') }}" class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'admin.index') &&
                                !Illuminate\Support\Str::contains(\Request::route()->getName(), '')
                                    ? 'active'
                                    : '' !!}">
                                    <i class="nav-icon fas fa-home"></i>
                                    <p>
                                        Dashboard
                                    </p>
                                </a>
                            </li>

                            @can('authorization', 'viewCategoryDefault')
                                <li class="nav-item">
                                    <a href="{{ route('admin.categories.index') }}"
                                        class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'categories') ? 'active' : '' !!}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <p>
                                            Categorias
                                        </p>
                                    </a>
                                </li>
                            @endcan

                            @can('authorization', 'viewCurrency')
                                <li class="nav-item">
                                    <a href="{{ route('admin.currencies.index') }}"
                                        class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'currencies') ? 'active' : '' !!}">
                                        <i class="nav-icon fas fa-coins"></i>
                                        <p>
                                            Moedas
                                        </p>
                                    </a>
                                </li>
                            @endcan
                            @can('authorization', 'viewAsset')
                                <li class="nav-item">
                                    <a href="{{ route('admin.assets.index') }}" class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'assets') ? 'active' : '' !!}">
                                        <i class="nav-icon fas fa-chart-line"></i>
                                        <p>
                                            Assets
                                        </p>
                                    </a>
                                </li>
                            @endcan

                            @can('authorization', 'viewUser')
                                <li class="nav-header">UTILIZADORES</li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}" class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'users') ? 'active' : '' !!}">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>
                                            Utilizadores
                                        </p>
                                    </a>
                                </li>
                            @endcan
                            @can('authorization', 'viewSharedRole')
                                <li class="nav-item">
                                    <a href="{{ route('admin.shared-roles.index') }}"
                                        class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared-roles') ? 'active' : '' !!}">
                                        <i class="nav-icon fas fa-user-shield"></i>
                                        <p>
                                            Papeis de partilha
                                        </p>
                                    </a>
                                </li>
                            @endcan

                            @can('authorization', 'viewRole')
                                <li class="nav-item">
                                    <a href="{{ route('admin.roles.index') }}" class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'roles') &&
                                    !Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared')
                                        ? 'active'
                                        : '' !!}">
                                        <i class="nav-icon fas fa-user-shield"></i>
                                        <p>
                                            Papéis
                                        </p>
                                    </a>
                                </li>
                            @endcan



                            @can('authorization', 'superAdmin')
                                <li class="nav-header">SUPER ADMIN</li>
                                <li class="nav-item {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'permissions') ||
                                Illuminate\Support\Str::contains(\Request::route()->getName(), 'languages') ||
                                Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared-permissions')
                                    ? 'menu-open'
                                    : '' !!} ">
                                    <a href="#" class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'permissions') ||
                                    Illuminate\Support\Str::contains(\Request::route()->getName(), 'languages') ||
                                    Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared-permissions')
                                        ? 'active'
                                        : '' !!} ">
                                        <i class="nav-icon fas fa-user-shield"></i>
                                        <p>
                                            Administration
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('admin.permissions.index') }}"
                                                class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'permissions') &&
                                                !Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared')
                                                    ? 'active'
                                                    : '' !!}">
                                                <i class="nav-icon fas fa-user-shield"></i>
                                                <p>
                                                    Permissões
                                                </p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.shared-permissions.index') }}"
                                                class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'shared-permissions') ? 'active' : '' !!}">
                                                <i class="nav-icon fas fa-user-shield"></i>
                                                <p>
                                                    Permissões de Partilha
                                                </p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.languages.index') }}"
                                                class="nav-link {!! Illuminate\Support\Str::contains(\Request::route()->getName(), 'languages') ? 'active' : '' !!}">
                                                <i class="nav-icon fas fa-language"></i>
                                                <p>
                                                    Idiomas
                                                </p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('telescope', 'requests') }}" target="__blank"
                                                class="nav-link">
                                                <i class="nav-icon fas fa-user-shield"></i>
                                                <p>
                                                    Telescope
                                                </p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <div class="os-scrollbar os-scrollbar-horizontal os-scrollbar-unusable os-scrollbar-auto-hidden">
            <div class="os-scrollbar-track">
                <div class="os-scrollbar-handle" style="width: 100%; transform: translate(0px, 0px);"></div>
            </div>
        </div>
        <div class="os-scrollbar os-scrollbar-vertical os-scrollbar-auto-hidden">
            <div class="os-scrollbar-track">
                <div class="os-scrollbar-handle" style="height: 68.680913%; transform: translate(0px, 0px);"></div>
            </div>
        </div>
        <div class="os-scrollbar-corner"></div>
    </div>
</aside>
