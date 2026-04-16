<?php
// components/random_news_carousel.php
// Random news carousel - horizontal infinite scrolling

// Get 10 random approved news articles
$random_news_query = "SELECT 
    news_id,
    title,
    cover_photo_url,
    category_name,
    published_date,
    summary
FROM news_articles 
WHERE is_approved = 1 
AND '$current_date' BETWEEN start_date AND end_date
ORDER BY RAND() 
LIMIT 10";

$random_news_result = $conn->query($random_news_query);

$random_news = [];
if ($random_news_result && $random_news_result->num_rows > 0) {
    while ($row = $random_news_result->fetch_assoc()) {
        $random_news[] = $row;
    }
}

// If less than 3 news, duplicate to have at least 3
while (count($random_news) < 6) {
    $random_news = array_merge($random_news, $random_news);
}

// Marathi category mapping
$marathi_categories = [
    'Today special' => 'दिनविशेष',
    'Amrut Events' => 'अमृत घडामोडी',
    'Beneficiary Story' => 'लाभार्थी स्टोरी',
    'Blog' => 'ब्लॉग',
    'Successful Entrepreneur' => 'यशस्वी उद्योजक',
    'Words Amrut' => 'शब्दामृत',
    'Smart Farmer' => 'स्मार्ट शेतकरी',
    'Capable Student' => 'सक्षम विद्यार्थी',
    'Spirituality' => 'अध्यात्म',
    'Social Situation' => 'सामाजिक परिस्थिती',
    'Women Power' => 'स्त्रीशक्ती',
    'Tourism' => 'पर्यटन',
    'Amrut Service' => 'अमृत सेवा कार्य',
    'News' => 'वार्ता',
    'Articles' => 'लेख'
];

// Default image
$default_image = 'photos/noimg.jpeg';
?>

<style>
/* Carousel Container */
.random-news-carousel {
    width: 100%;
    overflow: hidden;
    background: linear-gradient(135deg, #fff8f0, #fff3e6);
    padding: 20px 0;
    margin: 30px 0;
    border-radius: 15px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.random-news-title-section {
    text-align: center;
    margin-bottom: 20px;
    padding: 0 20px;
}

.random-news-title-section h3 {
    color: #ff6600;
    font-family: 'Khand', sans-serif;
    font-weight: 700;
    font-size: 1.8rem;
    position: relative;
    display: inline-block;
    margin-bottom: 10px;
}

.random-news-title-section h3:after {
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

.random-news-title-section p {
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

/* Card Styles */
.news-scroll-card {
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

.news-scroll-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

/* Card Image */
.scroll-card-image {
    width: 100%;
    height: 180px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.scroll-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.news-scroll-card:hover .scroll-card-image img {
    transform: scale(1.05);
}

/* Card Content */
.scroll-card-content {
    padding: 15px;
}

.scroll-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.4;
    margin-bottom: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-family: 'Mukta', sans-serif;
}

.scroll-card-category {
    display: inline-block;
    background: #ff6600;
    color: white;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    margin-bottom: 8px;
}

.scroll-card-date {
    font-size: 11px;
    color: #999;
    display: flex;
    align-items: center;
    gap: 5px;
}

.scroll-card-date i {
    font-size: 10px;
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

/* Mobile: 1 card at a time */
@media (max-width: 768px) {
    .news-scroll-card {
        width: 280px;
    }
    
    .scroll-card-image {
        height: 160px;
    }
    
    .scroll-card-title {
        font-size: 14px;
    }
    
    .random-news-title-section h3 {
        font-size: 1.4rem;
    }
}

@media (max-width: 576px) {
    .news-scroll-card {
        width: 260px;
    }
    
    .scroll-card-image {
        height: 150px;
    }
    
    .scrolling-content {
        gap: 15px;
    }
    
    .random-news-carousel {
        margin: 20px 0;
        padding: 15px 0;
    }
}

/* Optional: Add gradient fade on edges for better visual */
.random-news-carousel::before,
.random-news-carousel::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 50px;
    z-index: 2;
    pointer-events: none;
}

.random-news-carousel::before {
    left: 0;
    background: linear-gradient(to right, rgba(255,248,240,1), rgba(255,248,240,0));
}

.random-news-carousel::after {
    right: 0;
    background: linear-gradient(to left, rgba(255,248,240,1), rgba(255,248,240,0));
}

@media (max-width: 768px) {
    .random-news-carousel::before,
    .random-news-carousel::after {
        width: 30px;
    }
}
</style>

<div class="random-news-carousel">
    <div class="random-news-title-section">
        <h3><i class="bi bi-newspaper"></i> ताज्या बातम्या</h3>
        <p>इतर महत्त्वाच्या बातम्या वाचा</p>
    </div>
    
    <div class="scrolling-wrapper">
        <div class="scrolling-content">
            <!-- Duplicate news for infinite effect -->
            <?php foreach ($random_news as $news): ?>
                <a href="news.php?id=<?php echo $news['news_id']; ?>" class="news-scroll-card">
                    <div class="scroll-card-image">
                        <?php 
                        $img_url = !empty($news['cover_photo_url']) ? $news['cover_photo_url'] : $default_image;
                        ?>
                        <img src="<?php echo htmlspecialchars($img_url); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='<?php echo $default_image; ?>';">
                    </div>
                    <div class="scroll-card-content">
                        <span class="scroll-card-category">
                            <?php echo $marathi_categories[$news['category_name']] ?? 'बातमी'; ?>
                        </span>
                        <h4 class="scroll-card-title">
                            <?php echo htmlspecialchars(mb_substr($news['title'], 0, 60)) . (mb_strlen($news['title']) > 60 ? '...' : ''); ?>
                        </h4>
                        <div class="scroll-card-date">
                            <i class="bi bi-calendar3"></i>
                            <?php echo date('d-m-Y', strtotime($news['published_date'])); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            
            <!-- Duplicate for seamless infinite scrolling -->
            <?php foreach ($random_news as $news): ?>
                <a href="news.php?id=<?php echo $news['news_id']; ?>" class="news-scroll-card">
                    <div class="scroll-card-image">
                        <?php 
                        $img_url = !empty($news['cover_photo_url']) ? $news['cover_photo_url'] : $default_image;
                        ?>
                        <img src="<?php echo htmlspecialchars($img_url); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='<?php echo $default_image; ?>';">
                    </div>
                    <div class="scroll-card-content">
                        <span class="scroll-card-category">
                            <?php echo $marathi_categories[$news['category_name']] ?? 'बातमी'; ?>
                        </span>
                        <h4 class="scroll-card-title">
                            <?php echo htmlspecialchars(mb_substr($news['title'], 0, 60)) . (mb_strlen($news['title']) > 60 ? '...' : ''); ?>
                        </h4>
                        <div class="scroll-card-date">
                            <i class="bi bi-calendar3"></i>
                            <?php echo date('d-m-Y', strtotime($news['published_date'])); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>