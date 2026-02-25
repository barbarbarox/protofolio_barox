<?php
require_once 'config/database.php';

$db = getDB();

// Fetch profile
$profile = $db->query("SELECT * FROM profile LIMIT 1")->fetch();

// Fetch skills
$skills = $db->query("SELECT * FROM skills ORDER BY sort_order ASC")->fetchAll();

// Fetch projects
$projects = $db->query("SELECT * FROM projects ORDER BY is_featured DESC, created_at DESC")->fetchAll();

// Group skills by category
$skillsByCategory = [];
foreach ($skills as $skill) {
    $skillsByCategory[$skill['category']][] = $skill;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($profile['name'] ?? 'Portfolio') ?> - <?= htmlspecialchars($profile['title'] ?? 'Developer Portfolio') ?>">
    <title><?= htmlspecialchars($profile['name'] ?? 'Portfolio') ?> | Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Particle Canvas Background -->
    <canvas id="particles-canvas"></canvas>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="#" class="nav-logo">
                <span class="logo-bracket">&lt;</span>
                <span class="logo-text"><?= htmlspecialchars(explode(' ', $profile['name'] ?? 'Dev')[0]) ?></span>
                <span class="logo-bracket">/&gt;</span>
            </a>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#skills" class="nav-link">Skills</a></li>
                <li><a href="#projects" class="nav-link">Projects</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-greeting">
                    <span class="greeting-line"></span>
                    <span class="greeting-text">Hello, I'm</span>
                </div>
                <h1 class="hero-name"><?= htmlspecialchars($profile['name'] ?? 'John Doe') ?></h1>
                <div class="hero-title-wrapper">
                    <span class="hero-title-prefix">I'm a</span>
                    <span class="hero-title" id="typed-text"></span>
                    <span class="typed-cursor">|</span>
                </div>
                <p class="hero-description"><?= htmlspecialchars(substr($profile['bio'] ?? '', 0, 200)) ?></p>
                <div class="hero-buttons">
                    <a href="#projects" class="btn btn-primary">
                        <span>View Projects</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#contact" class="btn btn-outline">
                        <span>Contact Me</span>
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
                <div class="hero-social">
                    <?php if (!empty($profile['github'])): ?>
                    <a href="<?= htmlspecialchars($profile['github']) ?>" target="_blank" class="social-link" aria-label="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['linkedin'])): ?>
                    <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank" class="social-link" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($profile['instagram'])): ?>
                    <a href="<?= htmlspecialchars($profile['instagram']) ?>" target="_blank" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-avatar-wrapper">
                    <div class="avatar-ring"></div>
                    <div class="avatar-ring ring-2"></div>
                    <div class="avatar-glow"></div>
                    <div class="hero-avatar">
                        <i class="fas fa-code"></i>
                    </div>
                </div>
                <div class="floating-badge badge-1">
                    <i class="fab fa-html5"></i>
                </div>
                <div class="floating-badge badge-2">
                    <i class="fab fa-js"></i>
                </div>
                <div class="floating-badge badge-3">
                    <i class="fab fa-php"></i>
                </div>
                <div class="floating-badge badge-4">
                    <i class="fab fa-react"></i>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="mouse">
                <div class="mouse-wheel"></div>
            </div>
            <span>Scroll Down</span>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about" id="about">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-subtitle">Get To Know</span>
                <h2 class="section-title">About <span class="gradient-text">Me</span></h2>
                <div class="section-line"></div>
            </div>
            <div class="about-grid">
                <div class="about-info" data-reveal="left">
                    <div class="about-card glass-card">
                        <div class="about-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>Experience</h3>
                        <p>5+ Years Working</p>
                    </div>
                    <div class="about-card glass-card">
                        <div class="about-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3>Projects</h3>
                        <p><?= count($projects) ?>+ Completed</p>
                    </div>
                    <div class="about-card glass-card">
                        <div class="about-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Clients</h3>
                        <p>20+ Worldwide</p>
                    </div>
                </div>
                <div class="about-text" data-reveal="right">
                    <p class="about-description"><?= nl2br(htmlspecialchars($profile['bio'] ?? '')) ?></p>
                    <div class="about-details">
                        <?php if (!empty($profile['email'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($profile['email']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($profile['location'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($profile['location']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($profile['phone'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($profile['phone']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($profile['cv_link'])): ?>
                    <a href="<?= htmlspecialchars($profile['cv_link']) ?>" class="btn btn-primary" target="_blank">
                        <span>Download CV</span>
                        <i class="fas fa-download"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section class="section skills" id="skills">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-subtitle">What I Know</span>
                <h2 class="section-title">My <span class="gradient-text">Skills</span></h2>
                <div class="section-line"></div>
            </div>
            <div class="skills-container">
                <?php foreach ($skillsByCategory as $category => $catSkills): ?>
                <div class="skills-category" data-reveal>
                    <h3 class="category-title">
                        <i class="fas fa-<?= $category === 'Frontend' ? 'palette' : ($category === 'Backend' ? 'server' : 'tools') ?>"></i>
                        <?= htmlspecialchars($category) ?>
                    </h3>
                    <div class="skills-grid">
                        <?php foreach ($catSkills as $skill): ?>
                        <div class="skill-item glass-card">
                            <div class="skill-header">
                                <span class="skill-name"><?= htmlspecialchars($skill['name']) ?></span>
                                <span class="skill-percent"><?= $skill['percentage'] ?>%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" data-progress="<?= $skill['percentage'] ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="section projects" id="projects">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-subtitle">My Work</span>
                <h2 class="section-title">Recent <span class="gradient-text">Projects</span></h2>
                <div class="section-line"></div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="project-filters" data-reveal>
                <button class="filter-btn active" data-filter="all">All</button>
                <?php 
                $categories = array_unique(array_column($projects, 'category'));
                foreach ($categories as $cat): 
                ?>
                <button class="filter-btn" data-filter="<?= strtolower(str_replace(' ', '-', $cat)) ?>"><?= htmlspecialchars($cat) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card glass-card" data-reveal data-category="<?= strtolower(str_replace(' ', '-', $project['category'])) ?>">
                    <div class="project-image">
                        <div class="project-image-placeholder">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="project-overlay">
                            <?php if (!empty($project['link']) && $project['link'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($project['link']) ?>" target="_blank" class="project-btn" aria-label="Live Demo">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($project['github_link']) && $project['github_link'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($project['github_link']) ?>" target="_blank" class="project-btn" aria-label="Source Code">
                                <i class="fab fa-github"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="project-info">
                        <span class="project-category"><?= htmlspecialchars($project['category']) ?></span>
                        <h3 class="project-title"><?= htmlspecialchars($project['title']) ?></h3>
                        <p class="project-desc"><?= htmlspecialchars($project['description']) ?></p>
                        <div class="project-tech">
                            <?php 
                            $techs = explode(',', $project['tech_stack']);
                            foreach ($techs as $tech): 
                            ?>
                            <span class="tech-tag"><?= htmlspecialchars(trim($tech)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact" id="contact">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-subtitle">Get In Touch</span>
                <h2 class="section-title">Contact <span class="gradient-text">Me</span></h2>
                <div class="section-line"></div>
            </div>
            <div class="contact-grid">
                <div class="contact-info" data-reveal="left">
                    <div class="contact-card glass-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email</h3>
                        <p><?= htmlspecialchars($profile['email'] ?? 'email@example.com') ?></p>
                    </div>
                    <div class="contact-card glass-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Location</h3>
                        <p><?= htmlspecialchars($profile['location'] ?? 'Indonesia') ?></p>
                    </div>
                    <div class="contact-card glass-card">
                        <div class="contact-icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <h3>Social Media</h3>
                        <div class="contact-social">
                            <?php if (!empty($profile['github'])): ?>
                            <a href="<?= htmlspecialchars($profile['github']) ?>" target="_blank"><i class="fab fa-github"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($profile['linkedin'])): ?>
                            <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($profile['instagram'])): ?>
                            <a href="<?= htmlspecialchars($profile['instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper" data-reveal="right">
                    <form id="contact-form" class="contact-form glass-card">
                        <!-- Honeypot anti-bot field (hidden from real users) -->
                        <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" placeholder="Your Name" required id="contact-name" maxlength="100">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Your Email" required id="contact-email">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-tag"></i>
                                <input type="text" name="subject" placeholder="Subject" required id="contact-subject">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-wrapper textarea-wrapper">
                                <i class="fas fa-comment-alt"></i>
                                <textarea name="message" placeholder="Your Message" rows="5" required id="contact-message"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full" id="contact-submit">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <div id="form-status" class="form-status"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <a href="#" class="footer-logo">
                    <span class="logo-bracket">&lt;</span>
                    <span class="logo-text"><?= htmlspecialchars(explode(' ', $profile['name'] ?? 'Dev')[0]) ?></span>
                    <span class="logo-bracket">/&gt;</span>
                </a>
                <p class="footer-text">&copy; <?= date('Y') ?> <?= htmlspecialchars($profile['name'] ?? '') ?>. All Rights Reserved.</p>
                <div class="footer-social">
                    <?php if (!empty($profile['github'])): ?>
                    <a href="<?= htmlspecialchars($profile['github']) ?>" target="_blank"><i class="fab fa-github"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($profile['linkedin'])): ?>
                    <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($profile['instagram'])): ?>
                    <a href="<?= htmlspecialchars($profile['instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Hidden data for JS -->
    <script>
        const heroTitle = "<?= htmlspecialchars($profile['title'] ?? 'Full Stack Developer') ?>";
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
