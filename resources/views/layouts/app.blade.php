<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Habilitar View Transitions nativo (Chrome 126+ y navegadores modernos) -->
    <meta name="view-transition" content="same-origin">
    
    <title>@yield('title', 'Sistema de Rentas')</title>
    <style>
        :root {
            --bg: #f2f5fb;
            --surface: #ffffff;
            --surface-soft: #f7f9fc;
            --text: #182230;
            --muted: #66768a;
            --border: #dfe6f1;
            --primary: #2063cf;
            --primary-dark: #194ea4;
            --danger-bg: #ffe6e6;
            --danger: #a13434;
            --success-bg: #e9fbe8;
            --success: #1b7b39;
            --sidebar: #0f1b2e;
            --sidebar-soft: #142844;
            --sidebar-text: #c8d4e4;
            --sidebar-active-bg: #243b5f;
            --sidebar-active-text: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(circle at 10% -15%, #dce7ff 0%, transparent 35%),
                radial-gradient(circle at 100% 0%, #e8f3ff 0%, transparent 30%),
                var(--bg);
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            min-width: 260px;
            padding: 1.1rem 0.95rem;
            background: linear-gradient(180deg, var(--sidebar) 0%, var(--sidebar-soft) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--sidebar-text);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand {
            font-size: 1.2rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.1rem;
            letter-spacing: 0.4px;
        }

        .brand-subtitle {
            margin: 0 0 1.2rem;
            color: #9fb4ce;
            font-size: 0.86rem;
            line-height: 1.35;
        }

        .sidebar-nav {
            display: grid;
            gap: 0.55rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: var(--sidebar-text);
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 0.56rem 0.62rem;
            font-size: 0.92rem;
            transition: 0.2s ease;
        }

        .nav-link:hover {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }

        .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
        }

        .nav-icon {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #e7f1ff;
            background: rgba(255, 255, 255, 0.09);
        }

        .nav-icon svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .nav-group {
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            overflow: hidden;
        }

        .nav-group summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.58rem;
            color: #d8e4f3;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .nav-group summary::-webkit-details-marker {
            display: none;
        }

        .nav-group summary:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .group-title {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .group-chevron {
            width: 15px;
            height: 15px;
            opacity: 0.8;
            transition: transform 0.18s ease;
        }

        .nav-group[open] .group-chevron {
            transform: rotate(90deg);
        }

        .group-links {
            display: grid;
            gap: 0.35rem;
            padding: 0.45rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-foot {
            margin-top: 0.95rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.11);
            color: #9fb4ce;
            font-size: 0.8rem;
            line-height: 1.35;
        }

        @keyframes pageFadeIn {
            from {
                opacity: 0;
                transform: translateX(15px); /* Deslizamiento lateral más notorio */
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .content-shell {
            flex: 1;
            padding: 1.5rem 2rem;
            animation: pageFadeIn 0.45s ease-out forwards;
        }

        .container {
            max-width: 100%;
            margin: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(3px);
        }

        .topbar-label {
            margin: 0;
            font-size: 0.78rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .topbar h2 {
            margin: 0.2rem 0 0;
            font-size: 1.15rem;
        }

        .topbar-date {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.9rem;
        }

        h1 {
            margin: 0;
            font-size: 1.58rem;
        }

        h3 {
            margin-top: 0;
            margin-bottom: 0.9rem;
            font-size: 1.05rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 20px rgba(15, 45, 100, 0.05);
            min-width: 0;
            overflow-wrap: break-word;
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        @media (max-width: 640px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        @media (min-width: 992px) {
            .grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }
            .span-2 {
                grid-column: span 2;
            }
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .metric {
            font-size: 1.75rem;
            margin-top: 0.35rem;
            font-weight: 800;
            color: #153464;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 0.65rem;
            text-align: left;
            font-size: 0.92rem;
            border-bottom: 1px solid #e9eef6;
            vertical-align: top;
        }

        th {
            background: var(--surface-soft);
            font-weight: 700;
            color: #2a3f5d;
        }

        tbody tr:hover {
            background: #fafcff;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 0.45rem 0.78rem;
            text-decoration: none;
            font-size: 0.88rem;
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-light {
            background: #e8effa;
            color: #1a3b6d;
        }

        .btn-light:hover {
            background: #dbe7f8;
        }

        .btn-danger {
            background: var(--danger-bg);
            color: var(--danger);
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 0.22rem 0.55rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .badge-ok {
            background: #dbf8e1;
            color: #19703a;
        }

        .badge-warn {
            background: #fdf1c9;
            color: #8a5a0b;
        }

        .badge-bad {
            background: #ffe3e3;
            color: #9b2020;
        }

        /* ── Paginación ─────────────────────────────── */
        .pag-wrap {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-top: 1rem;
        }

        .pag-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.32rem 0.65rem;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
            color: var(--text);
            font-size: 0.85rem;
            text-decoration: none;
            transition: background 0.12s, border-color 0.12s;
            cursor: pointer;
        }

        .pag-btn:hover {
            background: #eef2fb;
            border-color: #b5c8ee;
        }

        .pag-active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            font-weight: 700;
            cursor: default;
        }

        .pag-active:hover {
            background: var(--primary);
        }

        .pag-disabled {
            color: #b0bac8;
            cursor: default;
            background: #f6f8fc;
        }

        .pag-dots {
            border: none;
            background: transparent;
            color: var(--muted);
            cursor: default;
        }

        .alert {
            padding: 0.72rem 0.9rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid transparent;
        }

        .alert-ok {
            background: var(--success-bg);
            border-color: #beeccb;
            color: var(--success);
        }

        .alert-bad {
            background: #fff2f2;
            border-color: #ffd1d1;
            color: var(--danger);
        }

        .muted {
            color: var(--muted);
            font-size: 0.87rem;
        }

        .form-grid {
            display: grid;
            gap: 0.9rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .field-span-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
            color: #384658;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 0.53rem;
            border: 1px solid #ccd7e6;
            border-radius: 8px;
            font-size: 0.91rem;
            background: #fff;
        }

        textarea {
            min-height: 92px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 0;
            border-color: #7aa2e8;
            box-shadow: 0 0 0 3px rgba(32, 99, 207, 0.13);
        }

        .form-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        body.modal-open {
            overflow: hidden;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: none;
            align-items: flex-start; /* Changed from center to allow scrolling */
            justify-content: center;
            padding: 2rem 1rem; /* Added padding top for space when scrolling */
            background: rgba(8, 18, 34, 0.58);
            overflow-y: auto; /* Enable scrolling on the overlay */
        }

        .modal-overlay.is-open {
            display: flex;
        }

        .modal-dialog {
            width: min(900px, 100%);
            margin: auto; /* Needed for flex-start centering */
            max-height: calc(100vh - 4rem); /* Limit height to viewport minus padding */
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 14px 30px rgba(8, 18, 34, 0.28);
            animation: modal-in 0.15s ease-out;
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface-soft);
        }

        .modal-title {
            margin: 0;
            font-size: 1rem;
            color: #173763;
        }

        .modal-body {
            padding: 1rem;
            overflow-y: auto; /* Scroll only inside the body */
        }

        .modal-close {
            border: 1px solid var(--border);
            background: #fff;
            color: #35527a;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
        }

        .modal-note {
            margin: 0 0 0.8rem;
            color: var(--muted);
            font-size: 0.86rem;
        }

        @keyframes modal-in {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pagination {
            margin-top: 0.85rem;
        }

        .pagination nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
        }

        .pagination span,
        .pagination a {
            font-size: 0.86rem;
            border: 1px solid var(--border);
            padding: 0.35rem 0.55rem;
            border-radius: 8px;
            text-decoration: none;
            color: #2b4467;
            background: #fff;
        }

        form.inline {
            display: inline;
        }

        .menu-toggle {
            display: none;
            background: #f0f4f8;
            border: 1px solid var(--border);
            color: var(--text);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .mobile-close-sidebar {
            display: none;
            background: none;
            border: none;
            color: #d1d5db;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            margin-right: -0.5rem;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(8, 18, 34, 0.58);
            z-index: 1500;
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.is-open {
            display: block;
            opacity: 1;
        }

        @media (max-width: 1100px) {
            .sidebar {
                width: 280px;
                min-width: 280px;
                height: 100vh;
                position: fixed;
                top: 0;
                left: -300px; /* Hidden offcanvas */
                z-index: 2000;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar.is-open {
                left: 0;
            }

            .mobile-close-sidebar {
                display: block;
            }

            .menu-toggle {
                display: block;
            }

            .nav-group summary {
                font-size: 0.72rem;
            }

            .content-shell {
                padding: 1.2rem 1rem;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 720px) {
            .page-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .card-table-wrap {
                display: none;
            }
        }

        /* ── Lease Cards (Mobile Optimization) ──────────────── */
        .lease-cards-grid {
            display: none;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        @media (max-width: 720px) {
            .lease-cards-grid {
                display: grid;
            }
        }

        .lease-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            animation: card-appear 0.4s ease-out backwards;
        }

        @keyframes card-appear {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lease-card:active {
            transform: scale(0.98);
        }

        .lease-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .lease-card-folio {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--primary);
        }

        .lease-card-unit {
            font-size: 0.9rem;
            color: var(--muted);
            margin-top: 0.2rem;
        }

        .lease-card-tenant {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--text);
        }

        .lease-card-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            padding: 0.8rem 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .lease-card-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            margin-bottom: 0.15rem;
        }

        .lease-card-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2a3f5d;
        }

        .lease-card-amount {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--success);
        }

        .lease-card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }

        .lease-card-actions .btn {
            flex: 1;
            text-align: center;
            font-weight: 600;
            padding: 0.6rem;
        }
        @media print {
            .sidebar, 
            .topbar, 
            .alert,
            .no-print,
            .btn,
            [title="Volver"] {
                display: none !important;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .content-shell {
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                margin-bottom: 2rem !important;
                break-inside: avoid;
            }
            
            .metric {
                color: #000 !important;
            }
        }

        /* ── Payment Cards (Mobile Optimization) ──────────────── */
        .payment-cards-grid {
            display: none;
            grid-template-columns: 1fr;
            gap: 0.85rem;
            margin-top: 1rem;
        }

        @media (max-width: 720px) {
            .payment-cards-grid {
                display: grid;
            }
            .payment-table-wrap {
                display: none;
            }
        }

        .payment-card {
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            position: relative;
        }

        .payment-card-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .payment-card-period {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--muted);
        }

        .payment-card-dates {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
        }

        .payment-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .payment-card-type {
            font-size: 0.72rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            background: #fff;
            border: 1px solid var(--border);
        }

        .payment-card-amount {
            font-weight: 800;
            font-size: 1.1rem;
            color: #173763;
        }
        }
    </style>
    @stack('styles')
    @stack('head')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-shell">
        <aside class="sidebar" id="appSidebar">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                <div>
                    <div class="brand">RentAscencio</div>
                    <p class="brand-subtitle">Control de locales comerciales y propiedades.</p>
                </div>
                <button class="mobile-close-sidebar" id="closeSidebarBtn">&times;</button>
            </div>

            @php
                $catalogOpen   = true;
                $operationOpen = true;
            @endphp

            <nav class="sidebar-nav">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3z"></path>
                            <path d="M13 21h8v-6h-8z"></path>
                            <path d="M13 3h8v8h-8z"></path>
                            <path d="M3 21h8v-4H3z"></path>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>

                <details class="nav-group" @if ($catalogOpen) open @endif>
                    <summary>
                        <span class="group-title">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 6h7v12H3z"></path>
                                    <path d="M14 4h7v16h-7z"></path>
                                    <path d="M3 12h7"></path>
                                    <path d="M14 10h7"></path>
                                </svg>
                            </span>
                            <span>Catalogos</span>
                        </span>
                        <svg class="group-chevron" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" stroke="currentColor" fill="none" stroke-width="2"></path>
                        </svg>
                    </summary>
                    <div class="group-links">
                        <a class="nav-link {{ request()->routeIs('properties.*') ? 'active' : '' }}" href="{{ route('properties.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 20V8l8-5 8 5v12"></path>
                                    <path d="M9 20v-6h6v6"></path>
                                </svg>
                            </span>
                            <span>Propiedades</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 3h8v8H3z"></path>
                                    <path d="M13 3h8v8h-8z"></path>
                                    <path d="M3 13h8v8H3z"></path>
                                    <path d="M13 13h8v8h-8z"></path>
                                </svg>
                            </span>
                            <span>Unidades</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}" href="{{ route('tenants.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.85"></path>
                                    <path d="M16 3.15a4 4 0 0 1 0 7.7"></path>
                                </svg>
                            </span>
                            <span>Inquilinos</span>
                        </a>
                    </div>
                </details>

                <details class="nav-group" @if ($operationOpen) open @endif>
                    <summary>
                        <span class="group-title">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 4h16v16H4z"></path>
                                    <path d="M8 2v4"></path>
                                    <path d="M16 2v4"></path>
                                    <path d="M4 10h16"></path>
                                </svg>
                            </span>
                            <span>Operacion</span>
                        </span>
                        <svg class="group-chevron" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" stroke="currentColor" fill="none" stroke-width="2"></path>
                        </svg>
                    </summary>
                    <div class="group-links">
                        <a class="nav-link {{ request()->routeIs('leases.*') ? 'active' : '' }}" href="{{ route('leases.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V8z"></path>
                                    <path d="M14 2v6h6"></path>
                                    <path d="M8 12h8"></path>
                                    <path d="M8 16h5"></path>
                                </svg>
                            </span>
                            <span>Contratos</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                    <path d="M2 10h20"></path>
                                    <path d="M7 14h3"></path>
                                </svg>
                            </span>
                            <span>Pagos</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('reports.income') ? 'active' : '' }}"
                           href="{{ route('reports.income') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 16l4-4 4 4 4-8"/>
                                </svg>
                            </span>
                            <span>Reporte Ingresos</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('reports.matrix') ? 'active' : '' }}"
                           href="{{ route('reports.matrix') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                    <line x1="3" y1="15" x2="21" y2="15"></line>
                                    <line x1="9" y1="3" x2="9" y2="21"></line>
                                    <line x1="15" y1="3" x2="15" y2="21"></line>
                                </svg>
                            </span>
                            <span>Matriz de Pagos</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                           href="{{ route('users.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </span>
                            <span>Usuarios</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}"
                           href="{{ route('expenses.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </span>
                            <span>Gastos</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                            <span class="nav-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            </span>
                            <span>Notificaciones</span>
                        </a>
                    </div>
                </details>
            </nav>

            <div class="sidebar-foot">
                <div style="margin-bottom:0.55rem;color:#c8d4e4;font-size:0.82rem;">
                    <strong style="color:#fff;">{{ auth()->user()->name }}</strong><br>
                    {{ auth()->user()->email }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="width:100%;background:rgba(255,80,80,0.15);border:1px solid rgba(255,100,100,0.25);color:#ffaaaa;border-radius:8px;padding:0.42rem 0.6rem;font-size:0.84rem;cursor:pointer;text-align:left;transition:0.15s ease;">
                        ⎋ Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="content-shell">
            <div class="container">
                <header class="topbar">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <button class="menu-toggle" id="menuToggleBtn" aria-label="Abrir menú">
                            <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                        <div>
                            <p class="topbar-label">Panel Administrativo</p>
                            <h2>@yield('title', 'Sistema de Rentas')</h2>
                        </div>
                    </div>
                    <p class="topbar-date">{{ now()->format('d/m/Y') }}</p>
                </header>

                @if (session('success'))
                    <div class="alert alert-ok">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-bad">
                        <strong>Hay errores en el formulario.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Modal Stack -->
    @stack('modals')
    
    <script>
        (() => {
            const overlays = document.querySelectorAll('.modal-overlay');
            const openButtons = document.querySelectorAll('[data-modal-target]');
            const closeButtons = document.querySelectorAll('[data-modal-close]');

            const closeModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.classList.remove('is-open');
                document.body.classList.remove('modal-open');
            };

            const openModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-modal-target');
                    openModal(document.querySelector(target));
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    closeModal(button.closest('.modal-overlay'));
                });
            });

            overlays.forEach((overlay) => {
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closeModal(overlay);
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                const opened = document.querySelector('.modal-overlay.is-open');
                closeModal(opened);
            });

            if (document.querySelector('.alert-bad')) {
                const modalToReopen = document.querySelector('.modal-overlay[data-modal-auto-open=\"true\"]');
                openModal(modalToReopen);
            }

            // Mobile Sidebar Toggle Logic
            const menuToggleBtn = document.getElementById('menuToggleBtn');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');
            const appSidebar = document.getElementById('appSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            const toggleSidebar = () => {
                const isOpen = appSidebar.classList.contains('is-open');
                if (isOpen) {
                    appSidebar.classList.remove('is-open');
                    sidebarOverlay.classList.remove('is-open');
                    document.body.classList.remove('modal-open');
                } else {
                    appSidebar.classList.add('is-open');
                    sidebarOverlay.classList.add('is-open');
                    document.body.classList.add('modal-open');
                }
            };

            if (menuToggleBtn && closeSidebarBtn && sidebarOverlay) {
                menuToggleBtn.addEventListener('click', toggleSidebar);
                closeSidebarBtn.addEventListener('click', toggleSidebar);
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
