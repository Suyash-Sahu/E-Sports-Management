    <footer class="footer mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="footer-title">E-Sports Hub</h4>
                    <p class="footer-text">Your premier destination for competitive gaming tournaments across India. Join the future of esports.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="tournaments.php"><i class="fas fa-chevron-right"></i> Tournaments</a></li>
                        <li><a href="teams.php"><i class="fas fa-chevron-right"></i> Teams</a></li>
                        <li><a href="leaderboard.php"><i class="fas fa-chevron-right"></i> Leaderboard</a></li>
                        <li><a href="about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h4 class="footer-title">Contact Us</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@esportshub.com</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+91 1234567890</span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Mumbai, Maharashtra, India</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> E-Sports Hub. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="privacy.php" class="footer-link">Privacy Policy</a>
                        <a href="terms.php" class="footer-link">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background-color: var(--darker-bg);
            border-top: 1px solid var(--neon-blue);
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent, 
                var(--neon-blue), 
                var(--neon-purple), 
                var(--neon-pink), 
                transparent
            );
            animation: borderGlow 3s linear infinite;
        }

        @keyframes borderGlow {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .footer-title {
            color: var(--neon-blue);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, var(--neon-blue), transparent);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--neon-blue);
            color: var(--neon-blue);
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: var(--neon-blue);
            color: var(--dark-bg);
            transform: translateY(-3px);
            box-shadow: 0 0 15px var(--neon-blue);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .footer-links a i {
            margin-right: 0.5rem;
            font-size: 0.8rem;
            color: var(--neon-blue);
        }

        .footer-links a:hover {
            color: var(--neon-blue);
            transform: translateX(5px);
        }

        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-contact li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-contact li i {
            color: var(--neon-blue);
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .footer-bottom {
            background-color: rgba(5, 5, 20, 0.9);
            padding: 1.5rem 0;
            border-top: 1px solid rgba(0, 243, 255, 0.1);
        }

        .footer-bottom p {
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin-left: 1.5rem;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--neon-blue);
        }

        @media (max-width: 768px) {
            .footer-title {
                margin-top: 2rem;
            }
            
            .footer-bottom {
                text-align: center;
            }
            
            .footer-bottom .text-md-end {
                text-align: center !important;
                margin-top: 1rem;
            }
            
            .footer-link {
                margin: 0 0.75rem;
            }
        }
    </style>

   <!-- Bootstrap JS Bundle with Popper -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
   <!-- AOS Animation Library -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
   <!-- Custom JavaScript -->
   <script src="js/script.js"></script>
   </body>
   </html>