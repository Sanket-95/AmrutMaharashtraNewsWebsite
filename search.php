<?php
// search.php – Search results page with full layout (categories navbar included)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'components/header_fixed.php';
include 'components/navbar.php';
include 'components/db_config.php';

// Get search query from URL
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search with prepared statement
$search_term = "%$q%";
$sql_count = "SELECT COUNT(*) as total 
              FROM news_articles 
              WHERE is_approved = 1 
                AND published_date <= CURDATE()
                AND (title LIKE ? OR summary LIKE ?)";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $search_term, $search_term);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_rows / $limit);

$sql_news = "SELECT news_id, title, cover_photo_url, summary 
             FROM news_articles 
             WHERE is_approved = 1 
               AND published_date <= CURDATE()
               AND (title LIKE ? OR summary LIKE ?)
             ORDER BY published_date DESC
             LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_news);
$stmt->bind_param("ssii", $search_term, $search_term, $limit, $offset);
$stmt->execute();
$news_result = $stmt->get_result();
$news_items = $news_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Static categories array (only for the navbar display – values match index.php)
$categories = [
    ['label' => 'मुख्य पृष्ठ', 'value' => 'home', 'group' => 'main'],
    [
        'label' => 'अमृत विषयी',
        'value' => 'amrut_about_group',
        'is_group' => true,
        'children' => [
            ['label' => 'आमच्याविषयी', 'value' => 'About Us', 'type' => 'link', 'url' => 'about_us.php'],
            ['label' => 'अमृत घडामोडी', 'value' => 'Amrut Events'],
            ['label' => 'लाभार्थी स्टोरी', 'value' => 'Beneficiary Story'],
            ['label' => 'ब्लॉग', 'value' => 'Blog'],
            ['label' => 'अमृत सेवाकार्य', 'value' => 'Amrut Service']
        ]
    ],
    ['label' => 'दिनविशेष', 'value' => 'Today Special', 'group' => 'content'],
    ['label' => 'यशस्वी उद्योजक', 'value' => 'Successful Entrepreneur', 'group' => 'content'],
    ['label' => 'शब्दामृत', 'value' => 'Words Amrut', 'group' => 'content'],
    ['label' => 'स्मार्ट शेतकरी', 'value' => 'Smart Farmer', 'group' => 'content'],
    ['label' => 'सक्षम विद्यार्थी', 'value' => 'Capable Student', 'group' => 'content'],
    ['label' => 'अध्यात्म', 'value' => 'Spirituality', 'group' => 'content'],
    ['label' => 'सामाजिक परिवर्तक', 'value' => 'Social Situation', 'group' => 'content'],
    ['label' => 'स्त्रीशक्ती', 'value' => 'Women Power', 'group' => 'content'],
    ['label' => 'पर्यटन', 'value' => 'Tourism', 'group' => 'content'],
    ['label' => 'वार्ता', 'value' => 'News', 'group' => 'content'],
    ['label' => 'लेख', 'value' => 'Articles', 'group' => 'content']
];
?>

<!-- Categories Navigation (exactly as in index.php) -->
<style>
/* Copy all categories navbar styles from index.php here */
/* ... (paste the entire <style> block from your index.php) ... */
</style>

<div class="categories-navbar">
    <div class="container-fluid px-3">
        <!-- Desktop categories -->
        <div class="categories-container d-none d-md-flex">
            <?php foreach ($categories as $index => $category): ?>
                <?php if (isset($category['is_group']) && $category['is_group']): ?>
                    <button class="category-dropdown-toggle <?php echo $index === 1 ? 'active' : ''; ?>" 
                            data-category="<?php echo htmlspecialchars($category['value']); ?>"
                            id="dropdown-toggle-<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </button>
                <?php else: ?>
                    <a href="index.php#<?php echo htmlspecialchars($category['value']); ?>" 
                       class="category-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                       data-category="<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <a href="javascript:void(0);" class="contact-btn" onclick="goToContact(event)">
                <i class="bi bi-bell"></i> संपर्क
            </a>
        </div>

        <!-- Mobile hamburger -->
        <button class="mobile-categories-toggle d-md-none" id="mobileCategoriesToggle" title="श्रेण्या दाखवा/लपवा">
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        
        <!-- Mobile categories menu (hidden by default) -->
        <div class="mobile-categories-menu" id="mobileCategoriesMenu">
            <?php foreach ($categories as $index => $category): ?>
                <?php if (isset($category['is_group']) && $category['is_group']): ?>
                    <div class="mobile-category-item">
                        <a href="javascript:void(0);" 
                           class="mobile-category-main mobile-category-toggle <?php echo $index === 1 ? 'active' : ''; ?>"
                           data-category="<?php echo htmlspecialchars($category['value']); ?>"
                           data-toggle="submenu">
                            <?php echo htmlspecialchars($category['label']); ?>
                        </a>
                        <div class="mobile-submenu" id="submenu-<?php echo htmlspecialchars($category['value']); ?>">
                            <?php foreach ($category['children'] as $child): ?>
                                <?php if (isset($child['type']) && $child['type'] === 'link'): ?>
                                    <a href="<?php echo htmlspecialchars($child['url']); ?>" 
                                       class="mobile-submenu-item link-item">
                                        <?php echo htmlspecialchars($child['label']); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="index.php#<?php echo htmlspecialchars($child['value']); ?>" 
                                       class="mobile-submenu-item" 
                                       data-category="<?php echo htmlspecialchars($child['value']); ?>">
                                        <?php echo htmlspecialchars($child['label']); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php#<?php echo htmlspecialchars($category['value']); ?>" 
                       class="mobile-category-main <?php echo $index === 0 ? 'active' : ''; ?>" 
                       data-category="<?php echo htmlspecialchars($category['value']); ?>">
                        <?php echo htmlspecialchars($category['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <a href="javascript:void(0);" class="mobile-contact-btn">
                <i class="bi bi-bell"></i> संपर्क साधा
            </a>
        </div>
    </div>
</div>

<!-- Dropdown menu for "अमृत विषयी" -->
<div class="dropdown-wrapper" id="dropdownWrapper">
    <div class="category-dropdown-menu" id="amrutAboutDropdown">
        <?php 
        foreach ($categories as $category):
            if (isset($category['is_group']) && $category['value'] === 'amrut_about_group'): 
        ?>
            <?php foreach ($category['children'] as $child): ?>
                <?php if (isset($child['type']) && $child['type'] === 'link'): ?>
                    <a href="<?php echo htmlspecialchars($child['url']); ?>" 
                       class="dropdown-item link-item">
                        <?php echo htmlspecialchars($child['label']); ?>
                    </a>
                <?php else: ?>
                    <a href="index.php#<?php echo htmlspecialchars($child['value']); ?>" 
                       class="dropdown-item" 
                       data-category="<?php echo htmlspecialchars($child['value']); ?>">
                        <?php echo htmlspecialchars($child['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; endforeach; ?>
    </div>
</div>

<style>
/* Search page specific styles */
.search-header {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 5px solid #f97316;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.home-link {
    background-color: #f97316;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.2s;
}
.home-link:hover {
    background-color: #d35400;
    color: white;
}
.news-card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
    background: white;
}
.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.news-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.news-card-body {
    padding: 15px;
}
.news-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
    line-height: 1.4;
}
.news-card-summary {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.read-more {
    color: #f97316;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.read-more:hover {
    text-decoration: underline;
    color: #d35400;
}
.pagination .page-link {
    color: #f97316;
}
.pagination .page-item.active .page-link {
    background-color: #f97316;
    border-color: #f97316;
    color: white;
}
.no-results {
    text-align: center;
    padding: 50px 20px;
    background: #f8f9fa;
    border-radius: 10px;
    color: #666;
}
</style>

<main class="container mt-4">
    <?php if (!empty($q)): ?>
        <div class="search-header">
            <div>
                <h2>शोध परिणाम: "<?php echo htmlspecialchars($q); ?>"</h2>
                <p class="text-muted mb-0">एकूण <?php echo $total_rows; ?> बातम्या सापडल्या.</p>
            </div>
            <a href="index.php" class="home-link">
                <i class="bi bi-house-door me-1"></i> मुख्य पृष्ठ
            </a>
        </div>

        <?php if ($total_rows > 0): ?>
            <div class="row g-4">
                <?php foreach ($news_items as $news): ?>
                    <div class="col-md-4">
                        <div class="news-card">
                            <img src="<?php echo htmlspecialchars($news['cover_photo_url'] ?: 'assets/default-news.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>"
                                 onerror="this.src='assets/default-news.jpg'">
                            <div class="news-card-body">
                                <h3 class="news-card-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="news-card-summary"><?php echo htmlspecialchars($news['summary']); ?></p>
                                <a href="news.php?id=<?php echo $news['news_id']; ?>" class="read-more">
                                    पूर्ण वाचा <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="पृष्ठ क्रमांक" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $page-1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $page+1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-results">
                <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3">कोणतीही बातमी सापडली नाही.</h4>
                <p class="text-muted">कृपया वेगळे कीवर्ड वापरून पुन्हा प्रयत्न करा.</p>
                <a href="index.php" class="btn btn-outline-primary mt-3" style="border-color: #f97316; color: #f97316;">
                    मुख्य पृष्ठावर जा
                </a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-results">
            <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #f97316;"></i>
            <h4 class="mt-3">शोधासाठी काहीही टाकले नाही.</h4>
            <p class="text-muted">कृपया शोध बारमध्ये कीवर्ड टाइप करा.</p>
            <a href="index.php" class="btn btn-outline-primary mt-3" style="border-color: #f97316; color: #f97316;">
                मुख्य पृष्ठावर जा
            </a>
        </div>
    <?php endif; ?>
</main>

<!-- JavaScript for categories navbar (copy from index.php) -->
<script>
// Function to scroll to footer (for contact button)
function goToContact(event) {
    if (event) event.preventDefault();
    const footer = document.querySelector('footer');
    if (footer) {
        footer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggle for "अमृत विषयी"
    const dropdownToggle = document.querySelector('.category-dropdown-toggle[data-category="amrut_about_group"]');
    const dropdownMenu = document.getElementById('amrutAboutDropdown');
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
            this.classList.toggle('active');
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.category-dropdown-toggle') && !event.target.closest('.category-dropdown-menu')) {
            if (dropdownMenu) dropdownMenu.classList.remove('show');
            if (dropdownToggle) dropdownToggle.classList.remove('active');
        }
    });

    // Mobile hamburger toggle
    const mobileToggle = document.getElementById('mobileCategoriesToggle');
    const mobileMenu = document.getElementById('mobileCategoriesMenu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (mobileMenu.style.display === 'block') {
                mobileMenu.style.display = 'none';
                mobileToggle.classList.remove('active');
            } else {
                mobileMenu.style.display = 'block';
                mobileToggle.classList.add('active');
            }
        });
    }

    // Toggle mobile submenus
    document.querySelectorAll('.mobile-category-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryValue = this.getAttribute('data-category');
            const submenu = document.getElementById('submenu-' + categoryValue);
            if (submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    this.classList.remove('active');
                } else {
                    document.querySelectorAll('.mobile-submenu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.mobile-category-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                    submenu.classList.add('show');
                    this.classList.add('active');
                }
            }
        });
    });

    // Handle category link clicks (redirect to index.php with hash)
    document.querySelectorAll('.category-link[data-category], .dropdown-item[data-category], .mobile-category-main[data-category], .mobile-submenu-item[data-category]').forEach(link => {
        link.addEventListener('click', function(e) {
            const categoryValue = this.getAttribute('data-category');
            if (categoryValue && categoryValue !== 'amrut_about_group') {
                // Redirect to index.php with hash
                window.location.href = 'index.php#' + categoryValue;
            }
        });
    });
});
</script>

<?php
$conn->close();
$showWhatsapp = true;
include 'components/footer.php';
?>