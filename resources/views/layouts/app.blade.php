<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Kerala Vision Enterprise</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Select2 CSS for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Tom Select CSS for Inline Search Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom Kerala Vision CSS with Cache Busting -->
    <link rel="stylesheet" href="{{ asset('css/kerala-vision.css') }}?v={{ time() }}">

    <!-- Inline Critical Mobile App CSS Override -->
    <style>
        @media (max-width: 991.98px) {
            .app-sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                width: 280px !important;
                transform: translateX(-100%) !important;
                -webkit-transform: translateX(-100%) !important;
                z-index: 1045 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            .app-sidebar.show {
                transform: translateX(0) !important;
                -webkit-transform: translateX(0) !important;
            }

            .sidebar-backdrop {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(15, 23, 42, 0.6) !important;
                backdrop-filter: blur(4px) !important;
                -webkit-backdrop-filter: blur(4px) !important;
                z-index: 1035 !important;
                opacity: 0 !important;
                visibility: hidden !important;
                transition: all 0.3s ease !important;
            }

            .sidebar-backdrop.show {
                opacity: 1 !important;
                visibility: visible !important;
            }

            .app-header {
                left: 0 !important;
                width: 100% !important;
                padding: 0 0.85rem !important;
                height: 60px !important;
            }

            .main-content {
                margin-left: 0 !important;
                margin-top: 60px !important;
                width: 100% !important;
                padding: 1rem 0.85rem 5.5rem 0.85rem !important;
            }

            .global-search-trigger {
                width: 150px !important;
                padding: 0.35rem 0.65rem !important;
                font-size: 0.76rem !important;
            }

            .kbd-shortcut {
                display: none !important;
            }

            .mobile-bottom-nav {
                display: flex !important;
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 62px !important;
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border-top: 1px solid #e2e8f0 !important;
                z-index: 1050 !important;
                align-items: center !important;
                justify-content: space-around !important;
                padding: 0 0.25rem !important;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08) !important;
            }

            [data-theme="dark"] .mobile-bottom-nav {
                background: rgba(17, 24, 39, 0.95) !important;
                border-top-color: #374151 !important;
            }
        }

        /* Sub-menu styling for vertical layout & active state */
        .nav-link-sub {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0.38rem 0.65rem !important;
            color: #64748b !important;
            text-decoration: none !important;
            border-radius: 6px !important;
            font-size: 0.81rem !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        .nav-link-sub:hover {
            color: #0d6efd !important;
            background: rgba(13, 110, 253, 0.08) !important;
        }

        .nav-link-sub.active-sub {
            color: #0d6efd !important;
            background: rgba(13, 110, 253, 0.12) !important;
            font-weight: 700 !important;
        }

        [data-theme="dark"] .nav-link-sub {
            color: #94a3b8 !important;
        }

        [data-theme="dark"] .nav-link-sub:hover,
        [data-theme="dark"] .nav-link-sub.active-sub {
            color: #60a5fa !important;
            background: rgba(59, 130, 246, 0.2) !important;
        }

        .transition-transform {
            transition: transform 0.2s ease !important;
        }

        a[aria-expanded="true"] .transition-transform {
            transform: rotate(180deg) !important;
        }
    </style>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- HTML5 QRCode Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Backdrop Overlay for Mobile -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Offcanvas Mobile Drawer / Sidebar -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-header justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-logo-icon">KV</div>
                    <div>
                        <div class="brand-title">Kerala Vision</div>
                        <div class="brand-subtitle">Service Manager</div>
                    </div>
                </div>
                <!-- Close Button for Mobile -->
                <button class="btn btn-sm btn-icon d-lg-none border-0" id="closeSidebarBtn">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>

            <div class="sidebar-menu">
                <div class="menu-category">General</div>
                <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isFrontOffice())
                <div class="menu-category">Master Data</div>
                <a href="{{ route('operators.index') }}" class="nav-link-custom {{ request()->routeIs('operators.*') ? 'active' : '' }}">
                    <i class="bi bi-buildings-fill"></i>
                    <span>Cable Operators</span>
                </a>
                <a href="{{ route('items.index') }}" class="nav-link-custom {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Item Creation</span>
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('staff.index') }}" class="nav-link-custom {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Create Staff</span>
                </a>
                @endif
                <a href="{{ route('box-models.index') }}" class="nav-link-custom {{ request()->routeIs('box-models.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>STB Box Models</span>
                </a>
                <a href="{{ route('set-top-boxes.index') }}" class="nav-link-custom {{ request()->routeIs('set-top-boxes.*') ? 'active' : '' }}">
                    <i class="bi bi-tv-fill"></i>
                    <span>Add Set Top Box</span>
                </a>

                <div class="menu-category">Transactions</div>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('add-stock.index') }}" class="nav-link-custom {{ request()->routeIs('add-stock.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus-fill"></i>
                    <span>Add Stock (Main)</span>
                </a>
                @endif
                <a href="{{ route('stock-transfer.index') }}" class="nav-link-custom {{ request()->routeIs('stock-transfer.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Stock Transfer</span>
                </a>
                @endif

                <div class="menu-category">Operations</div>
                @if((!Auth::user()->isService() && !Auth::user()->isQc()) || Auth::user()->isAdmin())
                <a href="{{ route('stb-checkin.index') }}" class="nav-link-custom {{ request()->routeIs('stb-checkin.*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-in-down text-primary"></i>
                    <span>STB Checkin</span>
                </a>
                <a href="{{ route('stb-checkout.index') }}" class="nav-link-custom {{ request()->routeIs('stb-checkout.*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-up-right text-success"></i>
                    <span>STB Checkout</span>
                </a>
                @endif

                @if((Auth::user()->isAdmin() || Auth::user()->isService()) && !Auth::user()->isFrontOfficeOnly())
                <a href="{{ route('service.index') }}" class="nav-link-custom {{ request()->routeIs('service.*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i>
                    <span>Service Section</span>
                </a>
                @endif

                @if(!Auth::user()->isFrontOfficeOnly())
                <a href="{{ route('qc.index') }}" class="nav-link-custom {{ request()->routeIs('qc.*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check-fill text-success"></i>
                    <span>QC Testing</span>
                </a>
                @endif

                <div class="menu-category">Analytics & System</div>
                
                <!-- Report Main Menu with Vertical Sub-menus -->
                <div class="nav-item-dropdown mb-1">
                    <a href="#reportsSubmenu" data-bs-toggle="collapse" class="nav-link-custom d-flex align-items-center justify-content-between {{ request()->routeIs('reports.*') ? 'active' : '' }}" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-bar-graph-fill text-primary"></i>
                            <span>Report</span>
                        </div>
                        <i class="bi bi-chevron-down small transition-transform"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }} ms-3 my-1 border-start border-2 border-primary border-opacity-25 ps-2" id="reportsSubmenu">
                        <div class="d-flex flex-column gap-1 w-100">
                            <a href="{{ route('reports.stock') }}" class="nav-link-sub {{ (request()->routeIs('reports.stock') || (request()->routeIs('reports.index') && request('type') !== 'service')) ? 'active-sub' : '' }}">
                                <i class="bi bi-box-seam text-primary me-2"></i>
                                <span>Stock Report</span>
                            </a>
                            <a href="{{ route('reports.service') }}" class="nav-link-sub {{ (request()->routeIs('reports.service') || (request()->routeIs('reports.index') && request('type') === 'service')) ? 'active-sub' : '' }}">
                                <i class="bi bi-tools text-warning me-2"></i>
                                <span>Service Report</span>
                            </a>
                            <a href="{{ route('reports.stb-history') }}" class="nav-link-sub {{ request()->routeIs('reports.stb-history*') ? 'active-sub' : '' }}">
                                <i class="bi bi-clock-history text-info me-2"></i>
                                <span>STB Box History</span>
                            </a>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" class="nav-link-custom {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>User Accounts</span>
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link-custom {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
                @endif
                <a href="{{ route('profile.index') }}" class="nav-link-custom {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            </div>

            <div class="p-2 border-top border-secondary-subtle">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm rounded-3 py-1">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Fixed Header -->
        <header class="app-header">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-icon d-lg-none" id="mobileSidebarToggle" title="Open Menu">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <!-- Global Search Lens Icon Trigger -->
                <button class="btn btn-icon rounded-circle shadow-sm border" 
                        data-bs-toggle="modal" 
                        data-bs-target="#globalSearchModal" 
                        title="Search items, barcodes, staff... (Ctrl+K)"
                        style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; background: var(--bg-surface); color: var(--text-main);">
                    <i class="bi bi-search fs-6"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Theme Toggle Button -->
                <button class="btn btn-icon rounded-circle" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                    <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn d-flex align-items-center gap-2 p-1 border-0" type="button" data-bs-toggle="dropdown">
                        <div class="brand-logo-icon rounded-circle" style="width: 38px; height: 38px; font-size: 1rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-start d-none d-md-block">
                            <div class="fw-bold fs-7 leading-tight" style="font-size: 0.88rem;">{{ Auth::user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ strtoupper(Auth::user()->role) }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 border-0 mt-2">
                        <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="bi bi-person me-2"></i> Profile Settings</a></li>
                        @if(Auth::user()->isAdmin())
                        <li><a class="dropdown-item py-2" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-2"></i> System Settings</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Flash Toast Alerts -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i> Please correct the following errors:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Mobile App Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav d-lg-none">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Home</span>
        </a>

        @if((!Auth::user()->isService() && !Auth::user()->isQc()) || Auth::user()->isAdmin())
        <a href="{{ route('stb-checkin.index') }}" class="mobile-nav-item {{ request()->routeIs('stb-checkin.*') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-down text-primary"></i>
            <span>Checkin</span>
        </a>
        @elseif(Auth::user()->isQc())
        <a href="{{ route('qc.index') }}" class="mobile-nav-item {{ request()->routeIs('qc.*') ? 'active' : '' }}">
            <i class="bi bi-patch-check-fill text-success"></i>
            <span>QC Testing</span>
        </a>
        @else
        <a href="{{ route('service.index') }}" class="mobile-nav-item {{ request()->routeIs('service.*') ? 'active' : '' }}">
            <i class="bi bi-tools text-primary"></i>
            <span>Service</span>
        </a>
        @endif

        <!-- Floating Action Button for Service Section -->
        @if(!Auth::user()->isFrontOfficeOnly())
        <a href="{{ route('service.index') }}" class="mobile-nav-item mobile-nav-item-accent" title="New Service Ticket">
            <i class="bi bi-tools"></i>
        </a>
        @endif

        @if(!Auth::user()->isFrontOfficeOnly())
        <a href="{{ route('qc.index') }}" class="mobile-nav-item {{ request()->routeIs('qc.*') ? 'active' : '' }}">
            <i class="bi bi-patch-check-fill text-success"></i>
            <span>QC</span>
        </a>
        @endif

        <a href="javascript:void(0)" class="mobile-nav-item" id="mobileMenuBtn">
            <i class="bi bi-list"></i>
            <span>Menu</span>
        </a>
    </nav>

    <!-- Global Live Search Modal (Ctrl+K) -->
    <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 fs-5"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="globalSearchInput" class="form-control border-0 shadow-none fs-5" placeholder="Type item name, barcode, staff, operator..." autofocus>
                    </div>
                </div>
                <div class="modal-body p-4" style="max-height: 450px; overflow-y: auto;" id="globalSearchResults">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-search fs-1 opacity-25 d-block mb-2"></i>
                        Start typing to search across Kerala Vision database...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Barcode Reader Modal -->
    <div class="modal fade" id="cameraScannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="bi bi-camera-fill me-2 text-primary"></i> Scan STB Barcode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="stopScannerBtn"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <div id="reader" style="width: 100%; min-height: 280px;" class="rounded-3 overflow-hidden border"></div>
                    <div class="text-muted mt-2 small"><i class="bi bi-info-circle me-1"></i> Point phone or webcam camera at the Set Top Box barcode</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Tom Select JS for Searchable Inline Dropdowns -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        // Dark / Light Theme Toggle Engine
        const currentTheme = localStorage.getItem('kv_theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        $('#themeToggleBtn').on('click', function() {
            let theme = document.documentElement.getAttribute('data-theme');
            let newTheme = (theme === 'dark') ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('kv_theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                $('#themeIcon').removeClass('bi-moon-stars-fill').addClass('bi-sun-fill text-warning');
            } else {
                $('#themeIcon').removeClass('bi-sun-fill text-warning').addClass('bi-moon-stars-fill');
            }
        }

        // Mobile Sidebar Offcanvas Drawer Handlers
        function openMobileSidebar() {
            $('#appSidebar').addClass('show');
            $('#sidebarBackdrop').addClass('show');
            $('body').css('overflow', 'hidden');
        }

        function closeMobileSidebar() {
            $('#appSidebar').removeClass('show');
            $('#sidebarBackdrop').removeClass('show');
            $('body').css('overflow', '');
        }

        $('#mobileSidebarToggle, #mobileMenuBtn').on('click', function(e) {
            e.preventDefault();
            openMobileSidebar();
        });

        $('#closeSidebarBtn, #sidebarBackdrop, .sidebar-menu .nav-link-custom').on('click', function() {
            if ($(window).width() < 992) {
                closeMobileSidebar();
            }
        });

        // Shortcut Ctrl+K
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#globalSearchModal').modal('show');
            }
        });

        // Global AJAX Live Search
        $('#globalSearchInput').on('keyup', function() {
            let q = $(this).val().trim();
            if (q.length < 2) {
                $('#globalSearchResults').html('<div class="text-center text-muted py-4"><i class="bi bi-search fs-1 opacity-25 d-block mb-2"></i>Type at least 2 characters to search...</div>');
                return;
            }

            $.ajax({
                url: "{{ route('global.search') }}",
                data: { q: q },
                success: function(res) {
                    let html = '';
                    
                    if (res.items.length > 0) {
                        html += '<h6 class="text-uppercase fw-bold text-muted fs-8 mb-2"><i class="bi bi-box me-1"></i> Items</h6>';
                        res.items.forEach(i => {
                            html += `<div class="p-2 hover-bg rounded-2 d-flex justify-content-between align-items-center mb-1">
                                <div><strong class="text-primary">${i.item_name}</strong> <span class="badge bg-secondary ms-1">${i.item_code}</span></div>
                                <span class="fw-bold">₹${i.sales_price}</span>
                            </div>`;
                        });
                    }

                    if (res.boxes.length > 0) {
                        html += '<h6 class="text-uppercase fw-bold text-muted fs-8 mt-3 mb-2"><i class="bi bi-tv me-1"></i> Set Top Boxes</h6>';
                        const stbHistoryBaseUrl = "{{ route('reports.stb-history') }}";
                        res.boxes.forEach(b => {
                            let targetUrl = `${stbHistoryBaseUrl}?stb_id=${b.id}`;
                            html += `<div class="p-2 hover-bg rounded-2 d-flex justify-content-between align-items-center mb-1" onclick="window.location.href='${targetUrl}'" style="cursor: pointer;">
                                <div><strong class="text-dark">${b.box_name}</strong> <code class="ms-1 text-danger fw-bold fs-7">${b.barcode_number}</code></div>
                                <a href="${targetUrl}" class="btn btn-sm btn-primary py-1 px-2 fw-bold text-white shadow-sm" style="font-size: 0.78rem;" onclick="event.stopPropagation();">
                                    <i class="bi bi-clock-history me-1"></i> History
                                </a>
                            </div>`;
                        });
                    }

                    if (res.staff.length > 0) {
                        html += '<h6 class="text-uppercase fw-bold text-muted fs-8 mt-3 mb-2"><i class="bi bi-person me-1"></i> Staff</h6>';
                        res.staff.forEach(s => {
                            html += `<div class="p-2 hover-bg rounded-2 d-flex justify-content-between align-items-center mb-1">
                                <div><strong>${s.name}</strong> <span class="text-muted small">(${s.designation})</span></div>
                                <span class="text-muted small">${s.mobile}</span>
                            </div>`;
                        });
                    }

                    if (!html) {
                        html = '<div class="text-center text-muted py-4">No results found for "'+q+'"</div>';
                    }

                    $('#globalSearchResults').html(html);
                }
            });
        });

        // Global Camera Scanner Helper
        let html5QrCodeScanner = null;
        let activeBarcodeTargetInput = null;

        window.startCameraScanner = function(targetInputId) {
            activeBarcodeTargetInput = targetInputId;
            $('#cameraScannerModal').modal('show');

            setTimeout(() => {
                if (html5QrCodeScanner) {
                    html5QrCodeScanner.clear();
                }
                html5QrCodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                html5QrCodeScanner.render((decodedText) => {
                    if (activeBarcodeTargetInput) {
                        $(activeBarcodeTargetInput).val(decodedText).trigger('change');
                    }
                    html5QrCodeScanner.clear();
                    $('#cameraScannerModal').modal('hide');
                }, (error) => {
                    // scanning...
                });
            }, 300);
        };

        $('#stopScannerBtn, #cameraScannerModal').on('hidden.bs.modal', function () {
            if (html5QrCodeScanner) {
                html5QrCodeScanner.clear().catch(error => {});
            }
        });

        // Global Select2 Initialization for Searchable Dropdowns
        $(document).ready(function() {
            $('.select2-searchable').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Type to search...',
                allowClear: true
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
