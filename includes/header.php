<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include auth check functions
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Sports Hub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        /* Enhanced E-Sports Hub CSS - Responsive and Fixed Overlapping Issues */

        :root {
            --neon-blue: #00f3ff;
            --neon-pink: #ff00ff;
            --neon-purple: #bf00ff;
            --dark-bg: #0a0a1a;
            --darker-bg: #050510;
            --card-bg: rgba(10, 10, 30, 0.8);
            --primary-color: #4a00e0;
            --primary-hover: #3a00b0;
        }

        @font-face {
            font-family: 'CyberFont';
            src: url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Orbitron', sans-serif;
            background-color: var(--dark-bg);
            color: white;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 35%, rgba(10, 40, 95, 0.25) 0%, transparent 40%),
                radial-gradient(circle at 75% 44%, rgba(95, 10, 135, 0.25) 0%, transparent 40%);
        }

        /* Navbar Styles - Fixed Positioning */
        .navbar {
            background-color: rgba(5, 5, 20, 0.9) !important;
            border-bottom: 1px solid var(--neon-blue);
            padding: 0.5rem 1rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 243, 255, 0.3);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            position: relative;
        }

        .navbar-brand::before {
            content: "";
            position: absolute;
            height: 4px;
            width: 100%;
            bottom: -4px;
            left: 0;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
            transform: scaleX(0);
            transform-origin: bottom left;
            transition: transform 0.3s ease-out;
        }

        .navbar-brand:hover::before {
            transform: scaleX(1);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            margin: 0 0.5rem;
            position: relative;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link:focus {
            color: var(--neon-blue) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: linear-gradient(90deg, var(--neon-blue), transparent);
            transition: all 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        /* Hero Section - Improved for all screen sizes */
        .hero-section {
            background: linear-gradient(rgba(5, 5, 20, 0.7), rgba(5, 5, 20, 0.9)), url('/api/placeholder/1920/1080');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 120px 0 60px; /* Added top padding to prevent navbar overlap */
        }

        .hero-content {
            position: relative;
            z-index: 10;
            width: 100%;
            padding: 0 15px;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem); /* Responsive font size */
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.8);
            letter-spacing: 2px;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 3vw, 1.5rem); /* Responsive font size */
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Neon Buttons - Improved for touch devices */
        .btn-neon {
            position: relative;
            display: inline-block;
            padding: 12px 30px;
            color: var(--neon-blue);
            text-transform: uppercase;
            letter-spacing: 3px;
            overflow: hidden;
            transition: 0.5s;
            border: 1px solid var(--neon-blue);
            background-color: rgba(0, 243, 255, 0.1);
            margin: 10px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            touch-action: manipulation; /* Better touch behavior */
        }

        .btn-neon:hover, .btn-neon:focus {
            background-color: var(--neon-blue);
            color: var(--dark-bg);
            box-shadow: 0 0 25px var(--neon-blue);
            text-decoration: none;
            outline: none;
        }

        .btn-neon-purple {
            color: var(--neon-purple);
            border-color: var(--neon-purple);
            background-color: rgba(191, 0, 255, 0.1);
        }

        .btn-neon-purple:hover, .btn-neon-purple:focus {
            background-color: var(--neon-purple);
            box-shadow: 0 0 25px var(--neon-purple);
            color: var(--dark-bg);
        }

        .btn-neon-pink {
            color: var(--neon-pink);
            border-color: var(--neon-pink);
            background-color: rgba(255, 0, 255, 0.1);
        }

        .btn-neon-pink:hover, .btn-neon-pink:focus {
            background-color: var(--neon-pink);
            box-shadow: 0 0 25px var(--neon-pink);
            color: var(--dark-bg);
        }

        /* Responsive button spacing */
        @media (max-width: 576px) {
            .btn-neon {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }

        /* Animated Particles - Optimized performance */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background-color: var(--neon-blue);
            border-radius: 50%;
            animation: float 5s infinite linear;
            opacity: 0.5;
            will-change: transform, opacity; /* Performance optimization */
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(20px);
                opacity: 0;
            }
        }

        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background-color: var(--darker-bg);
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            font-weight: 700;
            color: white;
            font-size: clamp(1.5rem, 4vw, 2.5rem); /* Responsive font size */
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
        }

        .feature-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            height: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 243, 255, 0.2);
            margin-bottom: 20px; /* Ensure spacing between cards on mobile */
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--neon-blue), var(--neon-purple));
            -webkit-background-clip: text;
            background-clip: text; /* Standard version */
            -webkit-text-fill-color: transparent;
            text-fill-color: transparent; /* Standard version */
            display: inline-block;
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        /* Tournaments Section */
        .tournaments-section {
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }

        .tournament-card {
            background: linear-gradient(135deg, rgba(10, 10, 30, 0.8), rgba(5, 5, 20, 0.9));
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(0, 243, 255, 0.3);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .tournament-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 243, 255, 0.3);
        }

        .tournament-img-container {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .tournament-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s;
            display: block;
        }

        .tournament-card:hover .tournament-img {
            transform: scale(1.1);
        }

        .tournament-date {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(5, 5, 20, 0.8);
            color: var(--neon-blue);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            border: 1px solid var(--neon-blue);
            z-index: 2;
        }

        .tournament-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .tournament-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: white;
            word-break: break-word; /* Prevent text overflow */
        }

        .tournament-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            flex-wrap: wrap; /* Allow wrapping on small screens */
            gap: 0.5rem;
        }

        .tournament-prize {
            color: var(--neon-pink);
            font-weight: bold;
        }

        /* Live Ticker - Fixed animation and overflow */
        .ticker-section {
            background-color: var(--darker-bg);
            padding: 1rem 0;
            overflow: hidden;
            border-top: 1px solid rgba(0, 243, 255, 0.3);
            border-bottom: 1px solid rgba(0, 243, 255, 0.3);
            position: relative;
        }

        .ticker-container {
            display: flex;
            white-space: nowrap;
            width: max-content; /* Ensure content doesn't wrap */
            animation: ticker 30s linear infinite;
        }

        .ticker-item {
            display: inline-block;
            padding: 0 2rem;
            color: var(--neon-blue);
        }

        @keyframes ticker {
            0% {
                transform: translateX(100vw);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        /* Player Distribution Section */
        .distribution-section {
            padding: 6rem 0;
            background-color: var(--darker-bg);
        }

        .distribution-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            height: 100%;
            border: 1px solid rgba(191, 0, 255, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px; /* Ensure spacing on mobile */
        }

        .distribution-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(191, 0, 255, 0.3);
        }

        .distribution-title {
            color: white;
            border-bottom: 2px solid var(--neon-purple);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .city-info {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.2);
        }

        .city-name {
            color: white;
            font-weight: 600;
            word-break: break-word; /* Prevent text overflow */
        }

        .game-info {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            word-break: break-word; /* Prevent text overflow */
        }

        /* Glitch Effect - Performance optimized */
        .glitch {
            position: relative;
            animation: glitch 1s linear infinite;
            display: inline-block;
        }

        @keyframes glitch {
            2%, 64% {
                transform: translate(2px, 0) skew(0deg);
            }
            4%, 60% {
                transform: translate(-2px, 0) skew(0deg);
            }
            62% {
                transform: translate(0, 0) skew(5deg);
            }
        }

        .glitch:before,
        .glitch:after {
            content: attr(title);
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .glitch:before {
            animation: glitchTop 1s linear infinite;
            clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
            -webkit-clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
        }

        @keyframes glitchTop {
            2%, 64% {
                transform: translate(2px, -2px);
            }
            4%, 60% {
                transform: translate(-2px, 2px);
            }
            62% {
                transform: translate(13px, -1px) skew(-13deg);
            }
        }

        .glitch:after {
            animation: glitchBottom 1.5s linear infinite;
            clip-path: polygon(0 67%, 100% 67%, 100% 100%, 0 100%);
            -webkit-clip-path: polygon(0 67%, 100% 67%, 100% 100%, 0 100%);
        }

        @keyframes glitchBottom {
            2%, 64% {
                transform: translate(-2px, 0);
            }
            4%, 60% {
                transform: translate(-2px, 0);
            }
            62% {
                transform: translate(-22px, 5px) skew(21deg);
            }
        }

        /* Call to Action Section */
        .cta-section {
            padding: 5rem 0;
            background-attachment: fixed;
            background-size: cover;
            position: relative;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(5, 5, 20, 0.8), rgba(5, 5, 20, 0.9));
        }

        .cta-section .container {
            position: relative;
            z-index: 2;
        }

        /* Cards from your custom styles */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--card-bg);
            border: 1px solid rgba(0, 243, 255, 0.2);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 243, 255, 0.2);
        }

        /* Form Controls */
        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 243, 255, 0.2);
            color: white;
            border-radius: 5px;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--neon-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 243, 255, 0.25);
            color: white;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--darker-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--neon-blue);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--neon-purple);
        }

        /* New Responsive Media Queries */
        @media (max-width: 1200px) {
            .hero-section {
                padding: 100px 0 60px;
            }
        }

        @media (max-width: 992px) {
            .hero-section {
                padding: 90px 0 50px;
            }
            
            .feature-card, .tournament-card, .distribution-card {
                margin-bottom: 25px;
            }
            
            .section-title {
                margin-bottom: 2.5rem;
            }
            
            .section-title::after {
                width: 60px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 40px;
            }
            
            .feature-icon {
                font-size: 2.5rem;
            }
            
            .feature-title {
                font-size: 1.3rem;
            }
            
            .tournament-img {
                height: 180px;
            }
            
            .section-title {
                margin-bottom: 2rem;
            }
            
            .tournament-content {
                padding: 1.2rem;
            }
            
            .tournament-title {
                font-size: 1.2rem;
            }
            
            .features-section,
            .tournaments-section,
            .distribution-section {
                padding: 4rem 0;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 70px 0 30px;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .feature-card {
                padding: 1.5rem;
            }
            
            .tournament-img {
                height: 160px;
            }
            
            .tournament-info {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .features-section,
            .tournaments-section,
            .distribution-section {
                padding: 3rem 0;
            }
            
            .distribution-card {
                padding: 1.2rem;
            }
        }

        /* Fixed Animations for Reduced Motion Preference */
        @media (prefers-reduced-motion: reduce) {
            .glitch,
            .glitch:before,
            .glitch:after,
            .ticker-container,
            .particle {
                animation: none;
            }
            
            .btn-neon,
            .feature-card,
            .tournament-card,
            .tournament-img,
            .distribution-card,
            .card {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">E-Sports Hub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">     
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'organizer'): ?>
                        <li class="nav-item">   
                            <a class="nav-link" href="tournaments.php">Tournaments</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="teams.php">Teams</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_type'] === 'organizer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="organizer_dashboard.php">My Tournaments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="select_winner.php">
                                    <i class="fas fa-trophy"></i> Select Winners
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">   
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5 pt-4"> 
                    