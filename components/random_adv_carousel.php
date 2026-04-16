<?php
// components/ad_carousel.php
// Horizontal scrolling carousel for ads (replaces popup ad)

// Base path for ad images
$base_primary = "components/primary_advertised/";

$current_date = date('Y-m-d');

// Fetch MULTIPLE random active ads (LIMIT 10 instead of 1)
$query_ads = "SELECT * FROM ads_management 
              WHERE ad_type = 1
              AND is_active = 1
              AND '$current_date' BETWEEN start_date AND end_date
              ORDER BY RAND()
              LIMIT 10";

$result_ads = mysqli_query($conn, $query_ads);

$ads = [];

if ($result_ads && mysqli_num_rows($result_ads) > 0) {
    while ($row = mysqli_fetch_assoc($result_ads)) {
        $ads[] = [
            'image' => $base_primary . $row['image_name'],
            'link'  => $row['ad_link'],
            'alt'   => $row['ad_title']
        ];
    }
}

// If no ads found, don't display anything
if (empty($ads)) {
    return;
}

$default_image = 'photos/noimg.jpeg';
?>

<style>
/* Carousel Container */
.ad-carousel {
    width: 100%;
    overflow: hidden;
    background: linear-gradient(135deg, #fff8f0, #fff3e6);
    padding: 20px 0;
    margin: 30px 0;
    border-radius: 15px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.ad-carousel-title-section {
    text-align: center;
    margin-bottom: 20px;
    padding: 0 20px;
}

.ad-carousel-title-section h3 {
    color: #ff6600;
    font-family: 'Khand', sans-serif;
    font-weight: 700;
    font-size: 1.8rem;
    position: relative;
    display: inline-block;
    margin-bottom: 10px;
}

.ad-carousel-title-section h3:after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: #ff6600;
    border-radius: 2px;
}

.ad-carousel-title-section p {
    color: #666;
    font-size: 0.9rem;
    margin-top: 15px;
}

/* Scrolling Wrapper */
.scrolling-wrapper {
    overflow: hidden;
    width: 100%;
    position: relative;
}

.scrolling-content {
    display: flex;
    gap: 20px;
    animation: scrollLeft 30s linear infinite;
    width: fit-content;
}

/* Pause on hover */
.scrolling-wrapper:hover .scrolling-content {
    animation-play-state: paused;
}

/* Card Styles - FIXED HEIGHT & WIDTH, NO CROPPING */
.ad-scroll-card {
    flex: 0 0 auto;
    width: 300px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}

.ad-scroll-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    text-decoration: none;
}

/* Card Image - FIXED HEIGHT, CONTAIN TO PREVENT CROPPING */
.scroll-card-image {
    width: 100%;
    height: 200px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

.scroll-card-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.5s ease;
    background-color: #f8f9fa;
}

.ad-scroll-card:hover .scroll-card-image img {
    transform: scale(1.02);
}

/* No content section - hidden */
.scroll-card-content {
    display: none;
}

/* Animation - Right to Left */
@keyframes scrollLeft {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* Desktop: 3 cards */
@media (min-width: 992px) {
    .ad-scroll-card {
        width: 320px;
    }
    .scroll-card-image {
        height: 220px;
    }
}

/* Tablet: 2 cards */
@media (min-width: 768px) and (max-width: 991px) {
    .ad-scroll-card {
        width: 280px;
    }
    .scroll-card-image {
        height: 190px;
    }
}

/* Mobile: 1 card */
@media (max-width: 767px) {
    .ad-scroll-card {
        width: 260px;
    }
    .scroll-card-image {
        height: 170px;
    }
    
    .ad-carousel-title-section h3 {
        font-size: 1.4rem;
    }
    
    .scrolling-content {
        gap: 15px;
    }
    
    .ad-carousel {
        margin: 20px 0;
        padding: 15px 0;
    }
}

/* Small mobile */
@media (max-width: 480px) {
    .ad-scroll-card {
        width: 240px;
    }
    .scroll-card-image {
        height: 150px;
    }
}

/* Edge fade effect */
.ad-carousel::before,
.ad-carousel::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 50px;
    z-index: 2;
    pointer-events: none;
}

.ad-carousel::before {
    left: 0;
    background: linear-gradient(to right, rgba(255,248,240,1), rgba(255,248,240,0));
}

.ad-carousel::after {
    right: 0;
    background: linear-gradient(to left, rgba(255,248,240,1), rgba(255,248,240,0));
}

@media (max-width: 767px) {
    .ad-carousel::before,
    .ad-carousel::after {
        width: 30px;
    }
}
</style>

<div class="ad-carousel">
    <div class="ad-carousel-title-section">
        <h3><i class="bi bi-megaphone"></i> प्रायोजित जाहिराती</h3>
        <p>विशेष प्रायोजित माहितीसाठी येथे क्लिक करा</p>
    </div>
    
    <div class="scrolling-wrapper">
        <div class="scrolling-content">
            <!-- First set of ads -->
            <?php foreach ($ads as $ad): ?>
                <a href="<?php echo htmlspecialchars($ad['link']); ?>" 
                   class="ad-scroll-card"
                   target="_blank" 
                   rel="noopener noreferrer">
                    <div class="scroll-card-image">
                        <img src="<?php echo htmlspecialchars($ad['image']); ?>" 
                             alt="<?php echo htmlspecialchars($ad['alt']); ?>"
                             loading="lazy"
                             onerror="this.src='<?php echo $default_image; ?>'; this.onerror=null;">
                    </div>
                </a>
            <?php endforeach; ?>
            
            <!-- Duplicate for seamless infinite scrolling -->
            <?php foreach ($ads as $ad): ?>
                <a href="<?php echo htmlspecialchars($ad['link']); ?>" 
                   class="ad-scroll-card"
                   target="_blank" 
                   rel="noopener noreferrer">
                    <div class="scroll-card-image">
                        <img src="<?php echo htmlspecialchars($ad['image']); ?>" 
                             alt="<?php echo htmlspecialchars($ad['alt']); ?>"
                             loading="lazy"
                             onerror="this.src='<?php echo $default_image; ?>'; this.onerror=null;">
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>