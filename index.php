<?php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';
?>

<div class="banner">
    <div class="promo-banner-slideshow">
        <div class="slide">
            <img src="./assets/subi-outside.jpg" alt="Shop builds on track">
        </div>
        <div class="slide">
            <img src="./assets/subi-wrx.jpg" alt="WRX engine bay tuned">
        </div>
        <div class="slide">
            <img src="./assets/vinny-z.jpg" alt="Vinny Ten Racing Nissan Z">
        </div>

        <button class="prev" type="button" onclick="plusSlides(-1)">&#10094;</button>
        <button class="next" type="button" onclick="plusSlides(1)">&#10095;</button>
    </div>

    <div class="dots" aria-label="Slideshow navigation">
        <button class="dot" type="button" onclick="currentSlide(1)"></button>
        <button class="dot" type="button" onclick="currentSlide(2)"></button>
        <button class="dot" type="button" onclick="currentSlide(3)"></button>
    </div>
</div>

<div class="main-content">
    <section class="content-grid">
        <!-- LEFT SIDEBAR -->
        <aside class="left-sidebar">
            <div class="card latest-video">
                <h2>Latest Video</h2>
                <iframe
                    id="latest-video"
                    width="100%"
                    height="250"
                    title="Vinny Ten Racing latest YouTube upload"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="card newsletter-signup">
                <h2>Newsletter</h2>
                <p>Get updates on new builds, events, and dyno days.</p>
                <form action="#" method="post">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn-primary btn-full-width">Subscribe</button>
                </form>
            </div>

            <div class="card socials">
                <h2>Follow Us</h2>
                <ul class="social-links">
                    <li><a href="https://www.facebook.com/VinnyTenRacing" target="_blank">Facebook</a></li>
                    <li><a href="https://www.instagram.com/vinnytenracing/" target="_blank">Instagram</a></li>
                    <li><a href="https://www.youtube.com/@VinnyTenTV" target="_blank">YouTube</a></li>
                </ul>
            </div>

            <div class="card vinny-ten-info">
                <h2>Vinny Ten Info</h2>
                <ul class="info-section">
                    <li>
                        <details>
                            <summary>Hours of Operation</summary>
                            <ul>
                                <li>Mon – Sat: 9:00 AM – 6:00 PM</li>
                                <li>Sunday: Closed</li>
                                <li>Dyno &amp; tuning by appointment outside normal hours.</li>
                            </ul>
                        </details>
                    </li>
                    <li><a href="direction.html">Directions</a></li>
                    <li><a href="privacy.html">Privacy Statement</a></li>
                    <li><a href="terms.html">Terms &amp; Conditions</a></li>
                </ul>
            </div>
        </aside>

        <!-- RIGHT MAIN CONTENT -->
        <main class="right-content">
            <section class="attention-grabber card">
                <h1>Built for Speed, Tuned for Perfection</h1>
                <p>
                    With over 30 years of turbocharging, tuning, and race engineering experience, we turn
                    performance dreams into reality. From full custom builds to everyday maintenance, our team
                    delivers precision, power, and trust — all under one roof.
                </p>
                <p>
                    Whether it’s street, drag, drift, or rally, we’ve got the tools, tech, and passion
                    to make it happen.
                </p>
                <div class="attention-actions">
                    <a href="shop.php" class="btn-primary">Browse Shop</a>
                    <a href="services.php" class="btn-secondary">Performance Services</a>
                </div>
            </section>

            <section class="featured-specials">
                <div class="section-header">
                    <h2>Featured Specials</h2>
                    <p><a href="shop.php">view all</a></p>
                </div>
                <div class="specials-grid">
                    <article class="special-item card">
                        <img src="./assets/special1.jpg" alt="UpRev tuning software">
                        <h3>UpRev</h3>
                        <p class="special-price">$600.00</p>
                    </article>
                    <article class="special-item card">
                        <img src="./assets/special2.jpg" alt="HP Tuners">
                        <h3>HP Tuners</h3>
                        <p class="special-price">$600.00</p>
                    </article>
                    <article class="special-item card">
                        <img src="./assets/special3.jpg" alt="Meisters c haft Axleback Exhaust">
                        <h3>Mesiterschaft Axleback Exhaust</h3>
                        <p class="special-price">$250.00</p>
                    </article>
                </div>
            </section>

            <section class="featured-packages">
                <div class="section-header">
                    <h2>Featured Packages</h2>
                    <p><a href="shop.php">view all</a></p>
                </div>

                <div class="packages-grid">
                    <?php
                    $sql = "SELECT product_id, product_name, image_url, price
                            FROM products
                            ORDER BY created_at DESC
                            LIMIT 4";
                    $res = mysqli_query($conn, $sql);
                    while ($p = mysqli_fetch_assoc($res)): ?>
                        <article class="package-item card">
                            <a href="product.php?id=<?php echo (int)$p['product_id']; ?>" class="product-card-link">
                                <div class="package-image-wrapper">
                                    <?php if (!empty($p['image_url'])): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($p['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                        >
                                    <?php else: ?>
                                        <div class="package-placeholder">
                                            Image coming soon
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="package-body">
                                    <h3 class="package-title">
                                        <?php echo htmlspecialchars($p['product_name']); ?>
                                    </h3>
                                    <?php if (isset($p['price'])): ?>
                                        <p class="package-price">
                                            $<?php echo number_format((float)$p['price'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                    <button type="button" class="btn-secondary btn-sm">
                                        View details
                                    </button>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; ?>
                </div>
            </section>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
