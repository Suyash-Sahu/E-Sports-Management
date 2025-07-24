<?php
session_start();
include 'includes/db_connection.php';
include 'includes/header.php';

// Get area-wise player distribution
$areaQuery = "SELECT 
    c.state,
    c.city,
    p.game_domain,
    COUNT(*) as player_count
FROM player_profiles pp
JOIN colleges c ON pp.college_id = c.college_id
JOIN players p ON pp.user_id = p.user_id
GROUP BY c.state, c.city, p.game_domain
ORDER BY c.state, c.city, player_count DESC";

$areaResult = $conn->query($areaQuery);
?>  

<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="particles" id="particles"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">WELCOME TO <span class="glitch" title="E-SPORTS HUB">E-SPORTS HUB</span></h1>
                <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">Join India's Premier E-Sports Tournament Platform</p>
                <div data-aos="fade-up" data-aos-delay="600">
                    <a href="register.php" class="btn btn-neon btn-neon-pink btn-register">REGISTER NOW</a>
                    <a href="tournaments.php" class="btn btn-neon">VIEW TOURNAMENTS</a>
                    <a href="leaderboard.php" class="btn btn-neon btn-neon-purple">LEADERBOARDS</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">WHY CHOOSE US</h2>
    <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <i class="fas fa-trophy feature-icon"></i>
                    <h3 class="feature-title">Competitive Tournaments</h3>
                    <p>Participate in tournaments from college level to national championships with massive prize pools.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <i class="fas fa-users feature-icon"></i>
                    <h3 class="feature-title">Vibrant Community</h3>
                    <p>Connect with fellow gamers across India, build teams, and forge alliances in our gaming network.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-card">
                    <i class="fas fa-award feature-icon"></i>
                    <h3 class="feature-title">Achievements & Rewards</h3>
                    <p>Earn badges, unlock exclusive rewards, and gain recognition for your gaming prowess.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Tournaments -->
<section class="tournaments-section" id="tournaments">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">FEATURED TOURNAMENTS</h2>
        <div class="row">
            <?php
            $sql = "SELECT t.*, g.game_name, o.organization_name,
                    (SELECT COUNT(*) FROM tournament_players tp WHERE tp.tournament_id = t.tournament_id) as current_players
                    FROM tournaments t 
                    JOIN games g ON t.game_id = g.game_id
                    JOIN organizer_profiles o ON t.organizer_id = o.user_id
                    WHERE t.status = 'upcoming' 
                    AND t.registration_deadline > NOW()
                    ORDER BY t.start_date LIMIT 3";
            $result = $conn->query($sql);        
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">';
                    echo '<div class="tournament-card">';
                    echo '<div class="tournament-img-container">';
                    echo '<img src="/api/placeholder/800/500" alt="' . htmlspecialchars($row['tournament_name']) . '" class="tournament-img">';
                    echo '<div class="tournament-date">' . date('M d, Y', strtotime($row['start_date'])) . '</div>';
                    echo '</div>';
                    echo '<div class="tournament-content">';
                    echo '<h3 class="tournament-title">' . htmlspecialchars($row['tournament_name']) . '</h3>';
                    echo '<div class="tournament-info">';
                    echo '<span><i class="fas fa-users"></i> ' . $row['current_players'] . '/' . htmlspecialchars($row['max_players']) . ' Players</span>';
                    echo '<span class="tournament-prize">₹' . number_format($row['prize_pool']) . '</span>';
                    echo '</div>';
                    echo '<a href="tournament_details.php?id=' . $row['tournament_id'] . '" class="btn btn-neon w-100">VIEW DETAILS</a>';
                    echo '</div></div></div>';
                }
            } else {
                echo '<div class="col-12 text-center"><p>No upcoming tournaments found.</p></div>';
            }
            ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="tournaments.php" class="btn btn-neon btn-neon-purple">VIEW ALL TOURNAMENTS</a>
    </div>
</div>
</section>

<!-- Player Distribution Section -->
<section class="distribution-section" id="distribution">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">PLAYER DISTRIBUTION</h2>
        <div class="row">
            <?php
            if ($areaResult->num_rows > 0) {
                $currentState = '';
                while($row = $areaResult->fetch_assoc()) {
                    if ($currentState != $row['state']) {
                        if ($currentState != '') {
                            echo '</div></div></div>';
                        }
                        echo '<div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">';
                        echo '<div class="distribution-card">';
                        echo '<h5 class="distribution-title">' . htmlspecialchars($row['state']) . '</h5>';
                        $currentState = $row['state'];
                    }
                    echo '<div class="city-info">';
                    echo '<div class="city-name">' . htmlspecialchars($row['city']) . '</div>';
                    echo '<div class="game-info">' . htmlspecialchars($row['game_domain']) . ': ' . $row['player_count'] . ' players</div>';
                    echo '</div>';
                }
                if ($currentState != '') {
                    echo '</div></div></div>';
                }
            } else {
                echo '<div class="col-12 text-center"><p>No player data available.</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5" style="background: linear-gradient(rgba(5, 5, 20, 0.8), rgba(5, 5, 20, 0.9)), url('/api/placeholder/1920/1080'); background-size: cover; background-attachment: fixed;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="mb-4">READY TO DOMINATE THE ARENA?</h2>
                <p class="lead mb-4">Join thousands of players competing in India's biggest e-sports tournaments. Register now and start your journey to the top!</p>
                <a href="register.php" class="btn btn-neon btn-neon-pink btn-lg">REGISTER NOW</a>
            </div>
        </div>
    </div>
</section>

<!-- Live Ticker -->
<section class="ticker-section">
    <div class="ticker-container">
        <div class="ticker-item">🏆 Valorant Championship Finals - May 25, 2025</div>
        <div class="ticker-item">🎮 BGMI Tournament Registrations Open Now</div>
        <div class="ticker-item">🥇 Congratulations to TeamXYZ for winning the CS:GO Championship</div>
        <div class="ticker-item">📱 Download our mobile app for live updates</div>
        <div class="ticker-item">💰 Total Prize Pool this season: ₹50,00,000</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<!-- Custom JavaScript -->
<script>
    // Initialize AOS Animation
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Create Particles Animation
        const particlesContainer = document.getElementById('particles');
        if (particlesContainer) {
            // Create initial particles
            for (let i = 0; i < 50; i++) {
                createParticle(particlesContainer);
            }
        }
        
        // Navbar scroll effect
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(5, 5, 20, 0.95)';
                    navbar.style.boxShadow = '0 2px 10px rgba(0, 243, 255, 0.3)';
        } else {
                    navbar.style.background = 'rgba(5, 5, 20, 0.9)';
                    navbar.style.boxShadow = '0 2px 10px rgba(0, 243, 255, 0.3)';
                }
            });
        }
    });

    // Create particle element function
    function createParticle(container) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random position
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        
        // Random size
        const size = Math.random() * 3 + 1;
        
        // Random color
        const colors = ['#00f3ff', '#bf00ff', '#ff00ff'];
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        // Random duration
        const duration = Math.random() * 10 + 5;
        
        // Set styles
        particle.style.left = posX + '%';
        particle.style.top = posY + '%';
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.backgroundColor = color;
        particle.style.animationDuration = duration + 's';
        particle.style.animationDelay = Math.random() * 5 + 's';
        
        // Add to container
        container.appendChild(particle);
        
        // Remove after animation completes and create new one
        setTimeout(() => {
            particle.remove();
            createParticle(container);
        }, duration * 1000);
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>     