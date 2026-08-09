<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#32000F" />
    <title>@yield('title', 'UM Taekwondo and Karatedo Attendance')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        :root {
            color-scheme: dark;
            --um-maroon: #800000;
            --um-dark-maroon: #5A001A;
            --um-deep-maroon: #35000F;
            --um-gold: #D4AF37;
            --um-bright-gold: #F4C542;
            --accent-gradient: linear-gradient(90deg, #F4C542 0%, #D4AF37 60%);
            --um-white: #FFFFFF;
            --um-offwhite: #F7F5F2;
            --um-dark: #1D0A10;
            --um-charcoal: #F5F2EE;
            --um-gray: #DDD4C8;
            --surface: rgba(56, 8, 18, 0.95);
            --surface-soft: rgba(60, 10, 22, 0.88);
            --surface-strong: rgba(40, 6, 14, 0.96);
            --shadow: 0 28px 90px rgba(0, 0, 0, 0.45);
            --radius: 24px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #23060b 0%, #300712 45%, #20050d 100%);
            color: var(--um-white);
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 300px;
            background: radial-gradient(circle at center, rgba(212, 175, 55, 0.16), transparent 34%);
            pointer-events: none;
            z-index: 0;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button,
        a,
        input,
        select,
        textarea {
            touch-action: manipulation;
        }

        button {
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img,
        svg {
            display: block;
            max-width: 100%;
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            position: relative;
            z-index: 1;
            overflow-x: hidden;
        }

        .app-shell > main.hero-page {
            width: 100%;
            max-width: 100%;
            flex: 1;
            margin: 0;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 20;
            width: 320px;
            min-width: 320px;
            padding: 28px 22px 20px;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, rgba(128, 0, 0, 0.98), rgba(90, 0, 26, 0.98));
            border-right: 1px solid rgba(212, 175, 55, 0.18);
            box-shadow: 8px 0 40px rgba(0, 0, 0, 0.18);
            overflow-y: auto;
            transform: translateX(0);
            transition: width 0.28s ease, transform 0.28s ease;
        }

        .sidebar.closed {
            transform: translateX(-100%);
        }

        .sidebar.collapsed {
            width: 92px;
            min-width: 92px;
        }

        .sidebar.closed ~ .main-area {
            margin-left: 0;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 28px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: var(--um-gold);
            color: var(--um-dark);
            font-weight: 800;
            font-size: 1.2rem;
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.15);
        }

        .brand-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .brand-copy strong {
            font-size: 0.96rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--um-white);
        }

        .brand-copy span {
            display: block;
            color: var(--um-gray);
            font-size: 0.86rem;
            line-height: 1.5;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 16px;
        }

        .nav-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 18px;
            color: var(--um-offwhite);
            border: 1px solid transparent;
            transition: transform 0.22s ease, border-color 0.22s ease, background 0.22s ease, box-shadow 0.22s ease;
        }

        .nav-item:hover {
            transform: translateX(4px);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(212, 175, 55, 0.18);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
        }

        .nav-item.active {
            background: rgba(212, 175, 55, 0.14);
            border-color: rgba(212, 175, 55, 0.3);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1), 0 16px 35px rgba(0, 0, 0, 0.14);
        }

        .nav-icon {
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--um-gold);
            font-size: 1.4rem;
        }

        .nav-label {
            white-space: nowrap;
        }

        .nav-fill {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-footer {
            margin-top: 28px;
            padding: 18px 16px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--um-gray);
            font-size: 0.88rem;
        }

        .sidebar-footer strong {
            color: var(--um-white);
            display: block;
            margin-bottom: 6px;
        }

        .main-area {
            margin-left: 320px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.28s ease;
            position: relative;
            z-index: 10;
        }

        .hero-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 0;
            width: 100vw;
            max-width: 100vw;
            margin: 0;
        }

        .hero-page.landing-page {
            padding: 0;
            min-height: 100vh;
            place-items: stretch;
            width: 100vw !important;
            max-width: 100vw !important;
        }

        .hero-page.landing-page > .landing-hero {
            min-height: 100vh;
            width: 100vw !important;
            margin: 0 auto !important;
            max-width: 100vw !important;
            border-radius: 0;
            padding: 72px 32px;
            background: linear-gradient(135deg, rgba(35, 8, 14, 0.98), rgba(18, 4, 10, 0.99));
            border: none;
            box-shadow: none;
            overflow: hidden;
        }

        .landing-hero-inner {
            width: min(980px, 100%);
            margin: 0 auto;
        }

        .hero-panel.landing-panel .landing-hero::before {
            inset: 0;
        }

        @media (max-width: 900px) {
            .hero-panel.landing-panel .landing-hero {
                padding: 56px 24px;
            }
        }

        @media (max-width: 640px) {
            .hero-panel.landing-panel .landing-hero {
                padding: 42px 18px;
            }
        }

        .sidebar.collapsed ~ .main-area {
            margin-left: 92px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 12;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 32px;
            background: rgba(90, 0, 26, 0.92);
            border-bottom: 1px solid rgba(212, 175, 55, 0.18);
            backdrop-filter: blur(18px);
        }

        .topbar-left {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .topbar-title {
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: -0.03em;
            font-weight: 700;
            color: var(--um-white);
        }

        .topbar-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: var(--um-gray);
            font-size: 0.96rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .icon-btn {
            position: relative;
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--um-white);
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .icon-btn i {
            font-size: 1.4rem;
        }

        .icon-btn:hover {
            background: rgba(212, 175, 55, 0.16);
            transform: translateY(-1px);
        }

        .notification-btn {
            overflow: visible;
        }

        .notification-btn .icon-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: #d4af37;
            color: #111;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
        }

        .page-notice {
            display: flex;
            gap: 16px;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 18px 20px;
            border-radius: 20px;
            color: var(--um-white);
            margin-top: 20px;
        }

        .notification-card {
            border: 1px solid rgba(212, 175, 55, 0.16);
            background: rgba(255, 255, 255, 0.04);
        }

        .page-notice-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #d4af37;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .notification-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            color: var(--um-white);
            font-size: 0.93rem;
        }

        .notification-checkbox input {
            width: 18px;
            height: 18px;
        }

        .notification-list {
            margin: 12px 0 0;
            padding-left: 18px;
            color: var(--um-offwhite);
        }

        .notification-list li {
            margin-bottom: 8px;
        }

        .profile-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 18px;
            background: var(--surface-soft);
            border: 1px solid rgba(212, 175, 55, 0.18);
            color: var(--um-charcoal);
        }

        .profile-chip .avatar,
        .profile-chip .avatar-image {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            object-fit: cover;
            display: grid;
            place-items: center;
            background: rgba(212, 175, 55, 0.2);
            color: var(--um-dark);
            font-weight: 700;
            border: 1px solid rgba(212, 175, 55, 0.18);
        }

        .profile-picture-preview .avatar,
        .profile-picture-preview .avatar-image {
            width: 128px;
            height: 128px;
            border-radius: 28px;
            object-fit: cover;
        }

        .page-content {
            flex: 1;
            padding: 30px 42px 42px;
            position: relative;
            min-width: 0;
        }

        .content-wrapper {
            max-width: 1360px;
            margin: 0 auto;
            animation: fadeInUp 0.45s ease both;
            min-width: 0;
        }

        .page-title {
            margin: 0;
            font-size: clamp(2rem, 2.5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.02;
            letter-spacing: -0.04em;
            overflow-wrap: anywhere;
            background-image: var(--accent-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
            animation: titleEntrance 700ms ease both;
        }

        .page-title::after {
            content: '';
            display: block;
            margin-top: 14px;
            width: 100%;
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(244,197,66,0.95), rgba(212,175,55,0.9));
            box-shadow: 0 8px 24px rgba(212,175,55,0.12);
            opacity: 0.95;
        }

        .hero-copy {
            margin: 0;
            color: var(--um-offwhite);
            font-size: 1rem;
            line-height: 1.8;
        }

        .hero-panel {
            position: relative;
            width: min(1160px, 100%);
            min-height: 620px;
            padding: 64px 72px;
            border-radius: 36px;
            overflow: hidden;
            background: var(--surface-soft);
            border: 1px solid rgba(212, 175, 55, 0.16);
            box-shadow: var(--shadow);
        }

        .hero-panel::before,
        .hero-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .hero-panel::before {
            background: radial-gradient(circle at top left, rgba(212, 175, 55, 0.12), transparent 26%), radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 22%);
        }

        .hero-panel::after {
            width: 100%;
            height: 100%;
            background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 120px 120px;
            opacity: 0.16;
        }

        .hero-copy-strong {
            font-size: clamp(1.05rem, 1.1vw, 1.2rem);
            color: var(--um-gray);
            margin-top: 12px;
            max-width: 680px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 32px;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-top: 44px;
        }

        .hero-stat {
            padding: 28px 24px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--um-white);
        }

        /* Roster scroll container to keep long team lists usable */
        .roster-scroll {
            max-height: calc(100vh - 340px);
            overflow-y: auto;
            padding-right: 8px;
        }

        .roster-scroll::-webkit-scrollbar {
            width: 10px;
        }
        .roster-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(212,175,55,0.22), rgba(212,175,55,0.12));
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.08);
        }

        .hero-stat h3 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: var(--um-gray);
        }

        .hero-stat strong {
            display: block;
            font-size: 2rem;
            line-height: 1;
            color: var(--um-white);
        }

        .toast-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: var(--um-offwhite);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
        }

        .toast-success {
            border-color: rgba(212, 175, 55, 0.28);
            background: rgba(212, 175, 55, 0.12);
        }

        .toast-error {
            border-color: rgba(229, 81, 81, 0.28);
            background: rgba(229, 81, 81, 0.12);
        }

        .toast-icon {
            font-size: 1.1rem;
            color: var(--um-gold);
            margin-top: 2px;
        }

        .toast-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }

        .toast-content strong {
            color: var(--um-white);
        }

        .toast-content ul {
            margin: 0;
            padding-left: 18px;
            color: var(--um-offwhite);
        }

        .button, button {
            transition: transform 0.22s ease, background 0.22s ease, box-shadow 0.22s ease;
            border-radius: 20px;
        }

        .button:active, button:active { transform: translateY(1px) scale(0.996); }

        @keyframes titleEntrance {
            from { transform: translateY(-6px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes floatIn {
            0% { transform: translateY(6px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .button-primary, button.button-primary {
            background: linear-gradient(135deg, var(--um-maroon), var(--um-dark-maroon));
            color: var(--um-white);
            box-shadow: 0 22px 52px rgba(128, 0, 0, 0.22);
        }

        .button-primary:hover, button.button-primary:hover {
            background: linear-gradient(135deg, var(--um-bright-gold), var(--um-gold));
            color: var(--um-charcoal);
            transform: translateY(-1px);
        }

        .button-secondary, button.button-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--um-white);
            border: 1px solid rgba(255, 255, 255, 0.16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .button-secondary:hover, button.button-secondary:hover {
            background: rgba(212, 175, 55, 0.16);
            border-color: rgba(212, 175, 55, 0.3);
            color: var(--um-white);
            transform: translateY(-1px);
        }

        /* Livelier action tile styles: gradients, animated highlights */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .button-present, button.button-present {
            background: linear-gradient(135deg, #34d399 0%, #16a34a 50%, #059669 100%);
            background-size: 200% 200%;
            color: var(--um-dark);
            box-shadow: 0 18px 44px rgba(22, 163, 74, 0.22), inset 0 -6px 24px rgba(6, 86, 45, 0.12);
            animation: gradientShift 6s ease infinite;
            font-weight: 700;
        }

        .button-late, button.button-late {
            background: linear-gradient(135deg, #ffd27a 0%, #f59e0b 50%, #f97316 100%);
            background-size: 200% 200%;
            color: var(--um-dark);
            box-shadow: 0 18px 44px rgba(245, 157, 24, 0.2), inset 0 -6px 24px rgba(153, 64, 0, 0.06);
            animation: gradientShift 7s ease infinite;
            font-weight: 700;
        }

        .button-absent, button.button-absent,
        .button-danger, button.button-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #d42f44 60%, #9f1f2a 100%);
            background-size: 200% 200%;
            color: var(--um-white);
            box-shadow: 0 18px 44px rgba(212, 47, 68, 0.22), inset 0 -6px 24px rgba(120, 20, 30, 0.08);
            animation: gradientShift 5.5s ease infinite;
            font-weight: 700;
        }

        .button-excuse, button.button-excuse {
            background: linear-gradient(135deg, #60a5fa 0%, #1f62d2 60%, #134e9b 100%);
            background-size: 200% 200%;
            color: var(--um-white);
            box-shadow: 0 18px 44px rgba(31, 98, 210, 0.2), inset 0 -6px 24px rgba(12, 42, 92, 0.08);
            animation: gradientShift 8s ease infinite;
            font-weight: 700;
        }

        .action-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .action-grid > * {
            min-width: 0;
        }

        .action-grid > form {
            width: 100%;
        }

        .action-grid .button,
        .action-grid button,
        .action-grid a.button {
            width: 100%;
            min-height: 140px;
            padding: 10px 18px;
            font-size: 1.02rem;
            border-radius: 24px;
            align-items: center;
            justify-content: center;
            display: inline-flex;
            flex-direction: column;
            gap: 6px;
            text-align: center;
            box-shadow: 0 18px 40px rgba(0,0,0,0.14);
            border: 1px solid rgba(255,255,255,0.06);
            transition: transform 0.22s cubic-bezier(.2,.9,.2,1), box-shadow 0.22s ease, background 0.22s ease;
        }

        /* decorative glow */
        .action-grid .button::after,
        .action-grid button::after,
        .action-grid a.button::after {
            content: '';
            width: 68px;
            height: 68px;
            border-radius: 999px;
            opacity: 0.08;
            position: relative;
            top: 4px;
            transition: transform 0.28s ease, opacity 0.28s ease;
            pointer-events: none;
        }

        .button-present::after { background: radial-gradient(circle, rgba(52,211,153,0.9), transparent 40%); }
        .button-late::after { background: radial-gradient(circle, rgba(255,210,122,0.9), transparent 40%); }
        .button-absent::after { background: radial-gradient(circle, rgba(255,107,107,0.9), transparent 40%); }
        .button-excuse::after { background: radial-gradient(circle, rgba(96,165,250,0.9), transparent 40%); }

        .action-grid .button:focus,
        .action-grid button:focus,
        .action-grid a.button:focus {
            outline: none;
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 30px 72px rgba(0,0,0,0.22), 0 0 0 8px rgba(212,175,55,0.06);
            z-index: 3;
        }

        .action-grid .button:hover,
        .action-grid button:hover,
        .action-grid a.button:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .form-panel,
        .form-card,
        .stats-card,
        .activity-card,
        .table-card,
        .hero-panel {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            backdrop-filter: blur(18px);
            position: relative;
            overflow: hidden;
        }

        .form-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0.012) 0 2px, transparent 2px 8px);
            opacity: 0.04;
            pointer-events: none;
        }

        .announcement-card {
            padding: 26px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.06));
            border: 1px solid rgba(212, 175, 55, 0.18);
            box-shadow: 0 26px 60px rgba(0, 0, 0, 0.16);
            transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
        }

        .announcement-card:hover {
            transform: translateY(-2px);
            border-color: rgba(212, 175, 55, 0.28);
            box-shadow: 0 30px 72px rgba(0, 0, 0, 0.22);
        }

        .announcement-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .announcement-card h3 {
            margin: 0;
            font-size: 1.2rem;
            line-height: 1.3;
            color: var(--um-white);
        }

        .announcement-badge {
            display: inline-flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.16);
            color: var(--um-gold);
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .announcement-time {
            color: rgba(247, 245, 242, 0.7);
            font-size: 0.92rem;
            white-space: nowrap;
        }

        .announcement-body {
            margin: 0;
            color: var(--um-offwhite);
            line-height: 1.85;
        }

        .field-row.two-column {
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 24px;
        }

        .form-select {
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(255,255,255,0.06);
            color: var(--um-white);
            padding: 14px 16px;
            min-height: 52px;
        }

        .form-select option {
            /* dropdown options are rendered by the UA; force readable contrast */
            color: #111;
            background: #fff;
        }

        .announcement-card p {
            margin: 0;
        }

        .announcement-card h3 {
            color: var(--um-white);
        }

        .announcement-badge {
            margin-bottom: 10px;
        }

        .announcement-card + .announcement-card {
            margin-top: 0;
        }

        .field-row.two-column .field-group {
            background: transparent;
        }

        .field-row.two-column label {
            color: var(--um-offwhite);
        }
        .field-row,
        .input-group {
            display: grid;
            gap: 12px;
        }

        .field-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-row.two-column {
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 24px;
        }

        .form-label {
            font-size: 0.95rem;
            color: var(--um-offwhite);
            font-weight: 600;
        }

        .page-title-row,
        .page-hero-content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: space-between;
        }

        .profile-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(320px, 1fr);
            gap: 28px;
            align-items: start;
        }

        .profile-fields,
        .profile-side-panel {
            display: grid;
            gap: 22px;
        }

        .profile-panel,
        .profile-meta-card,
        .profile-picture-card {
            padding: 28px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02), 0 18px 36px rgba(0, 0, 0, 0.14);
        }

        .profile-picture-card {
            display: grid;
            gap: 18px;
            justify-items: center;
            text-align: center;
            align-items: center;
        }

        .profile-meta-card {
            display: grid;
            gap: 16px;
        }

        .profile-meta-detail {
            display: grid;
            gap: 6px;
            padding: 14px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(212, 175, 55, 0.12);
        }

        .profile-meta-label {
            color: var(--um-offwhite);
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .profile-meta-value {
            color: var(--um-white);
            font-size: 1rem;
            font-weight: 700;
        }

        .profile-meta-card h3,
        .profile-picture-card h3 {
            margin: 0;
            color: var(--um-white);
            font-size: 1.15rem;
        }

        .profile-meta-card p {
            margin: 0;
            color: var(--um-gray);
            line-height: 1.7;
        }

        .button-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .form-control,
        .form-input,
        .form-file,
        .form-select,
        .form-textarea {
            width: 100%;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.05);
            color: var(--um-white);
            padding: 14px 16px;
            font-size: 16px;
            min-height: 52px;
            transition: border 0.22s ease, box-shadow 0.22s ease, background 0.22s ease, transform 0.22s ease;
            appearance: none;
        }

        .form-control option,
        .form-select option {
            color: #000;
            background: #fff;
        }

        .form-control:focus,
        .form-input:focus,
        .form-file:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--um-gold);
            box-shadow: 0 10px 30px rgba(212,175,55,0.08), 0 0 0 6px rgba(212, 175, 55, 0.10);
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-3px);
        }

        .form-file {
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--um-white);
        }

        .form-textarea {
            min-height: 140px;
            resize: vertical;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper .form-control {
            padding-right: 84px;
        }

        .password-wrapper .button {
            z-index: 2;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            height: 40px;
            width: 40px;
            display: inline-grid;
            place-items: center;
            border-radius: 999px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            color: var(--um-white);
            padding: 0;
        }
        .password-toggle i { font-size: 1.05rem; }

        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--um-offwhite);
            font-size: 0.98rem;
            cursor: pointer;
        }

        .form-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--um-gold);
        }

        .section-card {
            border-radius: 28px;
            background: var(--surface-strong);
            border: 1px solid rgba(212, 175, 55, 0.16);
            padding: 26px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.08);
        }

        .section-card h3 {
            margin: 0 0 10px;
            color: var(--um-charcoal);
            font-size: 1.05rem;
        }

        .section-card p {
            margin: 0;
            color: var(--um-charcoal);
            line-height: 1.7;
        }

        .form-section {
            display: grid;
            gap: 18px;
            margin-bottom: 20px;
        }

        /* Mobile-first overrides for public login & landing panels
           Keep desktop styles untouched; only adjust on narrow screens */
        @media (max-width: 480px) {
            .public-form-panel {
                padding: 18px 12px;
                align-items: stretch;
                justify-content: center;
            }

            .public-form-box {
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                padding: 20px 16px;
                border-radius: 12px;
                min-height: 0;
            }

            .public-form-heading { text-align: left; }

            .landing-pill { width: 56px; height: 56px; font-size: 1.25rem; }

            .public-form-heading .page-title { font-size: 1.6rem; margin-top: 6px; }

            .public-login-actions {
                flex-direction: column;
                gap: 12px;
            }

            .public-login-actions .button {
                min-width: 0;
                max-width: 100%;
                width: 100%;
                padding: 14px 16px;
                border-radius: 12px;
            }

            .form-control {
                padding: 12px 12px;
                min-height: 48px;
                font-size: 15px;
            }

            .page-title { font-size: clamp(1.4rem, 4vw, 2.2rem); }
        }

        .form-section-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .form-section-header .section-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: rgba(212, 175, 55, 0.12);
            color: var(--um-gold);
        }

        .form-section-header h3 {
            margin: 0;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--um-white);
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 24px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.1);
            min-width: 220px;
        }

        .stat-card-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(212, 175, 55, 0.14);
            color: var(--um-gold);
            font-size: 1.25rem;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--um-white);
            line-height: 1;
        }

        .stat-card-label {
            color: var(--um-gray);
            font-size: 0.95rem;
            margin-top: 4px;
        }

        .athlete-card {
            border-radius: 26px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.12);
            display: grid;
            gap: 16px;
            padding: 22px;
        }

        .athlete-card-header {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .athlete-avatar {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(212, 175, 55, 0.16);
            color: var(--um-dark);
            font-weight: 800;
            font-size: 1.1rem;
        }

        .athlete-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--um-white);
        }

        .athlete-meta {
            color: var(--um-gray);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .athlete-stats {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .athlete-stat {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .athlete-stat strong {
            color: var(--um-white);
            font-size: 1.2rem;
        }

        .athlete-stat span {
            color: var(--um-gray);
            font-size: 0.92rem;
        }

        .athlete-status {
            align-self: start;
            display: inline-flex;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .athlete-status.green { background: rgba(24, 160, 98, 0.16); color: #12b347; }
        .athlete-status.yellow { background: rgba(244, 197, 66, 0.16); color: #b3871c; }
        .athlete-status.red { background: rgba(212, 57, 69, 0.16); color: #d42f44; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            min-width: 92px;
        }

        .status-badge-present { background: rgba(24, 160, 98, 0.14); color: #12b347; }
        .status-badge-late { background: rgba(244, 197, 66, 0.16); color: #b3871c; }
        .status-badge-absent { background: rgba(212, 57, 69, 0.14); color: #d42f44; }
        .status-badge-excuse { background: rgba(25, 103, 214, 0.16); color: #114fa6; }
        .status-badge-special-training { background: rgba(46, 204, 113, 0.16); color: #3ddc84; }
        .status-badge-no-training { background: rgba(108, 117, 125, 0.16); color: #a8afb8; }
        .status-badge-info { background: rgba(255, 255, 255, 0.08); color: var(--um-white); }

        .landing-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 999px;
            border: 1px solid rgba(212, 175, 55, 0.18);
            color: var(--um-gold);
            font-size: 0.95rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.03);
        }

        .landing-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(212, 175, 55, 0.14), transparent 20%), radial-gradient(circle at 80% 30%, rgba(255, 255, 255, 0.04), transparent 12%);
            pointer-events: none;
            opacity: 0.9;
        }

        .landing-hero {
            position: relative;
            overflow: hidden;
            min-height: 720px;
            padding: 72px;
            border-radius: 38px;
            background: linear-gradient(135deg, rgba(128, 0, 0, 0.96), rgba(53, 0, 15, 0.98));
            border: 1px solid rgba(212, 175, 55, 0.18);
            box-shadow: 0 50px 130px rgba(0, 0, 0, 0.14);
            display: grid;
            place-items: center;
            text-align: center;
        }

        .landing-hero-inner {
            display: grid;
            gap: 28px;
            max-width: 980px;
            width: 100%;
            justify-items: center;
            text-align: center;
        }

        .landing-hero-copy {
            width: 100%;
            text-align: center;
        }

        .landing-hero h1 {
            font-size: clamp(4rem, 5.5vw, 6.8rem);
            margin: 24px 0 10px;
            line-height: 1.0;
            letter-spacing: -0.06em;
            color: var(--um-white);
            font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, serif;
            font-weight: 700;
            text-transform: uppercase;
            text-shadow: 0 10px 24px rgba(0, 0, 0, 0.32);
        }

        .landing-hero h1 .page-title-subline {
            display: block;
            font-size: 0.55em;
            line-height: 1.2;
            margin-top: 0.4em;
            color: rgba(255, 255, 255, 0.88);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .landing-hero .landing-subtitle {
            width: min(760px, 100%);
            color: var(--um-offwhite);
            font-size: 1.22rem;
            line-height: 1.9;
            margin: 0 auto 34px;
            text-align: center;
            padding: 0 14px;
        }

        .landing-hero .hero-actions {
            margin-top: 28px;
        }

        .landing-hero .hero-actions .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-width: 280px;
            min-height: 78px;
            font-size: 1.1rem;
            padding: 0 30px;
            line-height: 1.2;
        }

        .button-extra-large {
            min-width: 340px;
            min-height: 88px;
            font-size: 1.2rem;
            padding: 0 40px;
        }

        .button-large {
            min-width: 220px;
            padding: 18px 26px;
            font-size: 1rem;
        }

        .public-form-panel {
            width: 100%;
            max-width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 32px 20px;
            margin: 0;
        }

        .public-form-box {
            width: min(92%, 560px);
            max-width: 560px;
            margin: 0 auto;
            padding: 38px 34px;
            background: rgba(44, 6, 14, 0.98);
            border-radius: 32px;
            border: 1px solid rgba(212, 175, 55, 0.24);
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.45);
            color: var(--um-white);
        }

        .public-form-box.register-form-box {
            width: min(96%, 980px);
            max-width: 980px;
            padding: 42px 40px;
            background: rgba(44, 6, 14, 0.98);
        }

        .public-form-panel .public-form-box {
            display: block;
        }

        .public-form-panel .public-form-box {
            display: block;
        }

        .public-form-heading {
            margin-top: 0;
            max-width: 680px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .public-login-actions {
            justify-content: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .public-login-actions .button {
            min-width: 220px;
            max-width: 320px;
            width: 100%;
            padding: 18px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .public-login-actions .button + .button {
            margin-left: 0;
        }

        .landing-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: rgba(212, 175, 55, 0.16);
            color: var(--um-gold);
            font-size: 1.55rem;
            font-weight: 800;
        }


        .page-panel {
            width: min(1200px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 28px;
        }

        .page-hero {
            padding: 34px 36px;
            border-radius: 36px;
            background: linear-gradient(180deg, rgba(128, 0, 0, 0.9), rgba(38, 8, 14, 0.95));
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 40px 110px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(212, 175, 55, 0.12), transparent 18%),
                        radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.06), transparent 14%);
            pointer-events: none;
        }

        .page-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
        }

        .page-title {
            margin: 0;
            font-size: clamp(2.6rem, 4vw, 3.6rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.06em;
            background: linear-gradient(90deg, #FFFFFF, #F4C542);
            -webkit-background-clip: text;
            color: transparent;
        }

        .page-subtitle {
            margin: 0.8rem 0 0;
            max-width: 780px;
            color: rgba(247, 245, 242, 0.86);
            font-size: 1rem;
            line-height: 1.8;
        }

        .section-accent {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.18);
            color: var(--um-gold);
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .large-box {
            padding: 36px;
            border-radius: 32px;
            background: rgba(23, 5, 10, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 40px 90px rgba(0, 0, 0, 0.28);
            position: relative;
            overflow: hidden;
        }

        .large-box::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(circle at top right, rgba(244, 197, 66, 0.12), transparent 18%),
                              radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.04), transparent 14%);
            border-radius: inherit;
        }

        .large-box > * {
            position: relative;
            z-index: 1;
        }

        .notification-card {
            background: rgba(23, 5, 10, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 22px;
            margin: 22px auto;
            width: 100%;
            max-width: 860px;
            box-sizing: border-box;
        }

        .notification-card-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .notification-card-title {
            margin: 8px 0 10px;
            font-size: clamp(1.6rem, 2vw, 2.2rem);
            line-height: 1.05;
        }

        .notification-card-description {
            margin: 0 0 16px;
            font-size: 0.98rem;
            max-width: 720px;
        }

        .notification-card-action {
            margin: 0;
            align-self: center;
        }

        .notification-card-items {
            margin-top: 16px;
            padding-left: 0;
            list-style: none;
        }

        .notification-card-item {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.96rem;
        }

        .notification-card-item:last-child {
            border-bottom: none;
        }

        .notification-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.16);
            color: #f4c542;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .button-small, button.button-small {
            padding: 10px 16px;
            font-size: 0.94rem;
        }

        .form-card {
            padding: 26px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02), 0 18px 42px rgba(0, 0, 0, 0.16);
        }

        .profile-picture-preview {
            padding: 26px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(32, 8, 14, 0.96), rgba(28, 6, 12, 0.98));
            border: 1px solid rgba(212, 175, 55, 0.16);
            display: grid;
            gap: 18px;
            align-items: center;
            justify-items: center;
            text-align: center;
        }

        .profile-picture-preview .avatar,
        .profile-picture-preview .avatar-image {
            width: 156px;
            height: 156px;
            border-radius: 28px;
            border: 2px solid rgba(212, 175, 55, 0.24);
            box-shadow: 0 24px 74px rgba(0, 0, 0, 0.28);
        }

        .profile-picture-preview h3 {
            margin: 0;
            color: var(--um-white);
            font-size: 1.15rem;
        }

        .profile-picture-preview p {
            margin: 0;
            color: var(--um-gray);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .form-label {
            font-size: 0.95rem;
            color: rgba(247, 245, 242, 0.92);
            font-weight: 600;
        }

        .grid-two {
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr);
            align-items: start;
        }

        .status-key {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 18px;
            color: var(--um-gray);
            font-size: 0.95rem;
        }

        .status-key span {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .dot.green { background: #18a062; }
        .dot.yellow { background: #f4b400; }
        .dot.red { background: #d43945; }
        .dot.blue { background: #1668d6; }
        .dot.teal { background: #3ddc84; }
        .dot.gray { background: #6c757d; }

        .table-grid,
        .calendar-grid {
            display: grid;
            gap: 18px;
        }

        .table-grid {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .calendar-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-top: 24px;
        }

        /* Scoped month grid for full-month schedule views */
        .month-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        @media (max-width: 1100px) {
            .month-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }
        }

        .calendar-cell,
        .student-card {
            padding: 22px;
            border-radius: 28px;
            background: var(--surface-soft);
            border: 1px solid rgba(212, 175, 55, 0.14);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
            transition: transform 0.22s ease;
        }

        .calendar-cell:hover,
        .student-card:hover {
            transform: translateY(-1px);
        }

        .calendar-cell h4 {
            margin: 0 0 10px;
            font-size: 1.3rem;
            color: var(--um-charcoal);
        }

        .calendar-cell p {
            margin: 0 0 12px;
            color: var(--um-charcoal);
            line-height: 1.6;
        }

        /* Month-specific cell styles (used alongside .calendar-cell) */
        .month-cell {
            padding: 16px;
            border-radius: 18px;
            background: var(--surface-soft);
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .month-cell:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 36px rgba(0,0,0,0.09);
        }

        .month-cell h4 {
            margin: 0 0 8px;
            font-size: 1.05rem;
            color: var(--um-white);
            font-weight: 700;
        }

        .month-cell p {
            margin: 0 0 10px;
            color: var(--um-gray);
            line-height: 1.4;
            font-size: 0.92rem;
        }

        .month-cell.has-schedule {
            border-color: rgba(212, 175, 55, 0.16);
            background: linear-gradient(180deg, rgba(255,255,255,0.02), var(--surface-soft));
        }

        .month-cell.rest-day {
            opacity: 0.95;
            background: rgba(255,255,255,0.02);
            border-color: rgba(255,255,255,0.03);
        }

        .month-cell.empty-day {
            background: rgba(255,255,255,0.01);
        }

        /* Responsive tweaks for schedule forms and month grid */
        @media (max-width: 780px) {
            .form-panel,
            .form-card {
                padding: 16px;
                border-radius: 16px;
            }

            .coach-dashboard-page .hero-copy {
                font-size: 0.95rem;
            }

            .coach-dashboard-page .large-box {
                padding: 18px;
            }

            .coach-dashboard-page .roster-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 10px;
            }

            .coach-dashboard-page .student-card {
                padding: 14px;
            }

            .coach-dashboard-page .student-info h3 {
                font-size: 1rem;
            }

            .coach-dashboard-page .student-info p {
                font-size: 0.82rem;
            }

            .coach-dashboard-page .stat-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 8px;
            }

            .coach-dashboard-page .stat-grid .stat-block {
                padding: 10px;
            }

            .coach-dashboard-page .status-key {
                flex-direction: column;
                gap: 8px;
            }

            .coach-dashboard-page .status-key span {
                display: inline-flex;
            }

            .coach-dashboard-page .dashboard-metrics-row {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: stretch;
            }

            .coach-dashboard-page .dashboard-metrics-row > .dashboard-metric-card {
                flex: 1 1 160px;
                min-width: 150px;
                max-width: 100%;
            }

            .coach-dashboard-page .dashboard-metrics-row > .dashboard-metric-card:nth-child(1),
            .coach-dashboard-page .dashboard-metrics-row > .dashboard-metric-card:nth-child(2) {
                width: 100%;
            }

            .coach-dashboard-page .coach-date-form,
            .coach-dashboard-page .dashboard-action-row,
            .student-roster .student-date-row,
            .student-roster .student-nav-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }

            .student-roster .student-date-panel {
                display: flex;
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .student-roster .student-date-row {
                width: 100%;
                justify-content: flex-start;
                gap: 10px;
                flex-wrap: wrap;
            }

            .student-roster .student-date-row > * {
                flex: 1 1 120px;
            }

            .student-roster .student-date-row > button {
                flex: 1 1 120px;
                min-width: 120px;
            }

            .student-roster .student-nav-row {
                display: flex;
                gap: 8px;
                flex-wrap: nowrap;
                align-items: center;
                justify-content: space-between;
                width: 100%;
            }

            .student-roster .student-nav-row > a {
                min-width: 90px;
            }

            .coach-dashboard-page .coach-date-select,
            .student-roster .coach-date-select {
                min-width: 130px;
                flex: 1 1 110px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.07);
                border: 1px solid rgba(255,255,255,0.12);
                color: var(--um-white);
                padding: 12px 14px;
            }

            .coach-dashboard-page .coach-date-button,
            .coach-dashboard-page .coach-nav-button {
                padding: 12px 14px;
                min-width: 72px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255,255,255,0.14);
                color: var(--um-white);
            }

            .coach-dashboard-page .coach-date-button:hover,
            .coach-dashboard-page .coach-nav-button:hover {
                background: rgba(255, 255, 255, 0.12);
            }

            .coach-dashboard-page .coach-date-form {
                width: 100%;
            }

            .coach-dashboard-page .coach-date-form > * {
                flex: 1 1 120px;
            }
        }

        .field-row {
            grid-template-columns: 1fr !important;
            gap: 10px;
            }

            .field-group .form-control,
            .field-group .form-select {
                width: 100%;
                min-width: 0;
            }

            .action-row {
                display: flex;
                flex-direction: column-reverse;
                gap: 10px;
            }

            .action-row .button {
                width: 100%;
            }

            .month-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .month-cell {
                padding: 12px;
                border-radius: 14px;
            }
        }

        .student-card {
            display: grid;
            gap: 14px;
            text-decoration: none;
            color: inherit;
        }

        .coach-dashboard-page .student-card {
            padding: 16px;
            border-radius: 20px;
        }

        .student-info h3 {
            margin: 0;
            color: var(--um-white);
            font-size: 1.05rem;
        }

        .student-info p {
            margin: 0;
            color: var(--um-gray);
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .coach-dashboard-page .stat-grid {
            display: grid;
            gap: 9px;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        }

        .coach-dashboard-page .stat-grid .stat-block {
            padding: 12px;
            border-radius: 16px;
        }

        .stat-block {
            padding: 18px;
            border-radius: 20px;
            background: var(--surface-strong);
            border: 1px solid rgba(212, 175, 55, 0.16);
        }

        .coach-dashboard-page .student-card .stat-block {
            padding: 12px;
            border-radius: 16px;
        }

        .stat-block strong {
            display: block;
            font-size: 1.5rem;
            color: var(--um-charcoal);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--surface-soft);
            color: var(--um-gold);
            font-size: 0.92rem;
            font-weight: 700;
            border: 1px solid rgba(212, 175, 55, 0.16);
        }

        .badge + .badge {
            margin-left: 6px;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            background: rgba(255, 255, 255, 0.08);
            color: var(--um-white);
        }

        .status-chip.present { background: rgba(24, 160, 98, 0.16); color: #12b347; }
        .status-chip.late { background: rgba(244, 197, 66, 0.16); color: #b3871c; }
        .status-chip.absent { background: rgba(212, 57, 69, 0.16); color: #d42f44; }
        .status-chip.special_training { background: rgba(46, 204, 113, 0.16); color: #3ddc84; }
        .status-chip.no_training { background: rgba(108, 117, 125, 0.16); color: #a8afb8; }
        .status-chip.excuse { background: rgba(25, 103, 214, 0.16); color: #114fa6; }
        .status-chip.info { background: rgba(255, 255, 255, 0.08); color: var(--um-white); }

        .avatar {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: rgba(212, 175, 55, 0.16);
            color: var(--um-dark);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .form-card {
            padding: 24px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.28s ease;
            z-index: 18;
        }

        @media (max-width: 900px) {
            .sidebar:not(.closed) ~ .main-area + .sidebar-overlay,
            .sidebar:not(.closed) ~ .sidebar-overlay {
                opacity: 1;
                pointer-events: all;
            }
        }

        .mobile-nav-toggle {
            display: none;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            color: var(--um-white);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
            margin-top: 20px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1100px) {
            .sidebar {
                width: 92px;
                min-width: 92px;
            }

            .main-area {
                margin-left: 92px;
            }
        }

        @media (max-width: 900px) {
            .app-shell {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                width: 320px;
                max-width: 320px;
                transform: translateX(-100%);
                left: 0;
                height: 100vh;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar.closed {
                transform: translateX(-100%);
            }

            .main-area {
                margin-left: 0;
            }

            .topbar {
                padding: 18px 20px;
            }

            .page-content {
                padding: 20px 20px 32px;
            }

            .grid-two,
            .profile-form-grid,
            .field-row,
            .stats-grid,
            .card-grid,
            .dashboard-grid,
            .table-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-footer {
                display: none;
            }

            .mobile-nav-toggle {
                display: grid;
            }
        }

        @media (max-width: 700px) {
            .mobile-form-stack {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .mobile-form-stack .form-control,
            .mobile-form-stack .button {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }

            .landing-hero {
                min-height: auto;
                padding: 34px 18px;
                border-radius: 28px;
            }

            .landing-hero-inner {
                gap: 18px;
            }

            .landing-hero .landing-subtitle {
                max-width: 100%;
                font-size: 1rem;
                margin-bottom: 24px;
                text-align: center;
            }

            .landing-hero .hero-actions {
                width: 100%;
                display: grid;
                gap: 14px;
            }

            .landing-hero .hero-actions .button,
            .button-extra-large,
            .button-large,
            .public-login-actions .button {
                width: 100%;
                min-width: auto;
                min-height: 64px;
                padding: 16px 20px;
                font-size: 1rem;
            }

            .public-form-panel {
                min-height: auto;
                padding: 16px;
            }

            .public-form-box {
                width: 100%;
                padding: 26px 20px;
            }

            .public-form-heading {
                text-align: left;
            }

            .public-login-actions {
                justify-content: stretch;
                gap: 12px;
            }

            .public-login-actions .button {
                width: 100%;
            }

            .topbar {
                padding: 14px 16px;
                flex-wrap: wrap;
                gap: 12px;
            }

            .topbar-left,
            .topbar-actions {
                width: 100%;
            }

            .topbar-title {
                font-size: 1.45rem;
            }

            .topbar-meta {
                gap: 12px;
                flex-wrap: wrap;
            }

            .profile-chip {
                width: 100%;
                justify-content: space-between;
                padding: 10px 12px;
                min-width: 0;
            }

            .profile-chip > div {
                min-width: 0;
                overflow: hidden;
            }

            .profile-chip > div > div {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .sidebar {
                width: 100%;
                max-width: 100%;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-header,
            .nav-item,
            .nav-group {
                gap: 12px;
            }

            .nav-item {
                padding: 12px 14px;
            }

            .nav-icon {
                width: 52px;
                height: 52px;
            }

            .page-content {
                padding: 16px 16px 28px;
            }

            .page-panel {
                padding: 0 8px;
            }

            .large-box {
                padding: 22px;
            }

            .grid-two,
            .field-row,
            .stats-grid,
            .card-grid,
            .dashboard-grid,
            .table-grid,
            .stat-grid,
            .calendar-grid {
                grid-template-columns: 1fr;
            }

            .table-grid,
            .calendar-grid {
                gap: 16px;
            }

            .calendar-cell,
            .student-card,
            .form-card,
            .section-card {
                padding: 18px;
            }

            .calendar-cell h4 {
                font-size: 1.15rem;
            }

            .status-key {
                gap: 12px;
                font-size: 0.9rem;
            }

            .badge,
            .status-chip {
                font-size: 0.85rem;
                padding: 10px 12px;
            }

            .avatar,
            .avatar-image {
                width: 46px;
                height: 46px;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .hero-page {
                width: 100%;
                max-width: 100%;
            }
        }

        /* Coaches list styles */
        .coach-grid {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            align-items: flex-start;
            margin-top: 18px;
        }
        .coach-columns { display:flex; gap:18px; align-items:flex-start; margin-top:18px; }
        .coach-column { flex:1 1 50%; display:flex; flex-direction:column; gap:12px; }
        .coach-column-title { margin:0; color:var(--um-gray); font-size:0.95rem; font-weight:700; padding-left:6px; }

        .coach-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            padding: 16px;
            min-width: 260px;
            max-width: 420px;
            flex: 1 1 300px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .coach-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 30px 72px rgba(0,0,0,0.28);
        }

        .coach-left { display:flex; gap:12px; align-items:center; min-width:0; }
        .coach-avatar { width:64px; height:64px; border-radius:50%; object-fit:cover; flex:0 0 64px; border: 2px solid rgba(255,255,255,0.06); box-shadow: 0 8px 18px rgba(0,0,0,0.28); }
        .coach-initials { width:64px; height:64px; border-radius:50%; display:grid; place-items:center; background: rgba(255,255,255,0.06); font-weight:700; color:var(--um-white); flex:0 0 64px; border: 2px solid rgba(255,255,255,0.04); }
        .coach-meta { min-width:0; }
        .coach-name { font-weight:700; font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .coach-sub { color:var(--um-gray); font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        .coach-actions { display:flex; gap:10px; align-items:center; }
        .coach-actions .button { min-width:84px; padding:8px 12px; }

        /* Edit form header */
        .coach-edit-header { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
        .coach-edit-header .meta { color:var(--um-gray); }
        .avatar-preview { width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.06); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }
        .avatar-preview-wrap { display:flex; gap:8px; align-items:center; }
        .avatar-clear { padding:6px 10px; height:auto; }

        /* File input styling */
        .visually-hidden { position: absolute !important; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        .file-input { display:flex; gap:12px; align-items:center; }
        .file-input .file-name { color:var(--um-gray); font-size:0.95rem; }
        .file-input .file-choose { padding:10px 14px; border-radius:14px; }
        .file-input .file-choose:hover { transform: translateY(-2px); }

        /* Form polish */
        .form-panel { padding: 28px; border-radius: 24px; box-shadow: 0 18px 40px rgba(0,0,0,0.28); }
        .field-group { display:block; }
        .form-label { display:block; margin-bottom:8px; color:var(--um-offwhite); font-weight:700; }
        .form-control::placeholder { color: rgba(255,255,255,0.26); }
        .form-control { background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02)); }
    </style>
</head>
<body>
    @php
        $authUser = session('user');
        $publicPages = ['', 'login', 'register'];
        $isPublic = in_array(request()->path(), $publicPages, true) || request()->is('/');
        $pageTitleRaw = trim(str_replace(' | UM Attendance', '', $__env->yieldContent('title')));
        $pageHeading = $pageTitleRaw ?: 'Dashboard';
    @endphp

    <div class="app-shell">
        @if (!$isPublic)
            @include('components.sidebar')
            <div class="main-area">
                @include('components.topbar')
                <main class="page-content">
                    <div class="content-wrapper">
                        @include('components.alert')
                        @yield('content')
                    </div>
                </main>
            </div>
            <div class="sidebar-overlay" data-sidebar-close></div>
        @else
            <main class="hero-page @yield('heroPageClass')">
                @include('components.alert')
                @yield('content')
            </main>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.querySelector('.sidebar');
            var mobileToggle = document.querySelector('[data-mobile-toggle]');
            var closeToggle = document.querySelector('[data-sidebar-close]');

            var setSidebarState = function () {
                if (!sidebar) {
                    return;
                }

                if (window.innerWidth <= 900) {
                    sidebar.classList.add('closed');
                    sidebar.classList.remove('open');
                } else {
                    sidebar.classList.remove('open');
                    sidebar.classList.remove('closed');
                }
            };

            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function () {
                    if (window.innerWidth <= 900) {
                        sidebar.classList.toggle('open');
                        sidebar.classList.toggle('closed');
                    } else {
                        sidebar.classList.toggle('closed');
                        sidebar.classList.remove('open');
                    }
                });
            }

            if (closeToggle && sidebar) {
                closeToggle.addEventListener('click', function () {
                    sidebar.classList.remove('open');
                    sidebar.classList.add('closed');
                });
            }

            if (sidebar) {
                setSidebarState();
                window.addEventListener('resize', setSidebarState);
            }
        });
    </script>
    <div id="confirm-modal" style="display:none; position:fixed; inset:0; z-index:9999;">
        <div style="background: rgba(0,0,0,0.6); position:absolute; inset:0;"></div>
        <div style="display:grid; place-items:center; position:relative; z-index:2; width:100%; height:100%;">
            <div style="background:var(--surface-soft); padding:20px; border-radius:14px; border:1px solid rgba(255,255,255,0.06); width:min(520px, 96%);">
                <h3 id="confirm-modal-title" style="margin:0 0 8px;">Confirm delete</h3>
                <p id="confirm-modal-body" style="margin:0 0 16px; color:var(--um-offwhite);">Are you sure?</p>
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button id="confirm-modal-cancel" class="button button-secondary">Cancel</button>
                    <button id="confirm-modal-confirm" class="button button-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Avatar preview for file inputs with id 'avatar' and remove control
            function handleAvatarInput(input) {
                if (!input) return;
                var container = input.closest('.form-panel') || input.parentElement;
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    var existingWrap = container.querySelector('.avatar-preview-wrap');
                    if (!file) {
                        if (existingWrap) existingWrap.remove();
                        return;
                    }
                    var url = URL.createObjectURL(file);
                    if (existingWrap) {
                        var img = existingWrap.querySelector('.avatar-preview');
                        if (img) img.src = url;
                        return;
                    }
                    var wrap = document.createElement('div');
                    wrap.className = 'avatar-preview-wrap';
                    var img = document.createElement('img');
                    img.className = 'avatar-preview';
                    img.src = url;
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'avatar-clear button button-secondary';
                    btn.textContent = 'Remove';
                    btn.addEventListener('click', function () {
                        input.value = '';
                        if (wrap) wrap.remove();
                    });
                    wrap.appendChild(img);
                    wrap.appendChild(btn);
                    container.insertBefore(wrap, container.firstChild);
                });
            }

            document.querySelectorAll('input[type=file][id=avatar]').forEach(handleAvatarInput);

            // Custom file-input UI: wire .file-choose buttons to hidden inputs and show filename
            document.querySelectorAll('.file-input').forEach(function (wrap) {
                var input = wrap.querySelector('input[type=file]');
                var btn = wrap.querySelector('.file-choose');
                var nameSpan = wrap.querySelector('.file-name');
                if (!input || !btn) return;
                btn.addEventListener('click', function () { input.click(); });
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    nameSpan.textContent = file ? file.name : 'No file chosen';
                });
            });

            // Delete confirmation modal
            var modal = document.getElementById('confirm-modal');
            var modalTitle = document.getElementById('confirm-modal-title');
            var modalBody = document.getElementById('confirm-modal-body');
            var btnConfirm = document.getElementById('confirm-modal-confirm');
            var btnCancel = document.getElementById('confirm-modal-cancel');
            var pendingForm = null;

            document.addEventListener('submit', function (e) {
                var form = e.target;

                // Password confirmation check: prevent submit if present and not matching
                try {
                    var pwd = form.querySelector('input[name="password"]');
                    var pwdc = form.querySelector('input[name="password_confirmation"], input[id="password_confirmation"]');
                    if (pwd && pwdc) {
                        var v1 = pwd.value || '';
                        var v2 = pwdc.value || '';
                        if (v1 !== v2) {
                            e.preventDefault();
                            alert('Passwords do not match. Please confirm your password.');
                            pwd.focus();
                            return;
                        }
                    }
                } catch (err) {
                    // ignore
                }

                if (form.classList && form.classList.contains('confirm-delete')) {
                    e.preventDefault();
                    pendingForm = form;
                    var name = form.getAttribute('data-name') || 'this item';
                    modalTitle.textContent = 'Delete ' + name + '?';
                    modalBody.textContent = 'This action cannot be undone. Are you sure you want to delete ' + name + '?';
                    modal.style.display = 'grid';
                }
            }, true);

            btnCancel.addEventListener('click', function () {
                modal.style.display = 'none';
                pendingForm = null;
            });

            btnConfirm.addEventListener('click', function () {
                if (pendingForm) {
                    modal.style.display = 'none';
                    pendingForm.submit();
                    pendingForm = null;
                }
            });

            // Password show/hide toggles
            document.querySelectorAll('.password-toggle').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var wrapper = btn.closest('.password-wrapper');
                    if (!wrapper) return;
                    var input = wrapper.querySelector('input[type=password], input[type=text]');
                    if (!input) return;
                    var isPwd = input.type === 'password';
                    input.type = isPwd ? 'text' : 'password';
                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-eye', !isPwd);
                        icon.classList.toggle('bi-eye-slash', isPwd);
                    }
                });
            });
        })();
    </script>
</body>
</html>
