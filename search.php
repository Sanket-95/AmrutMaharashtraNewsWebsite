<?php
// search.php – Search results page with full layout (categories navbar included)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'components/db_config.php';
include 'components/header_fixed.php';
include 'components/navbar.php';


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
              AND (
                    Region LIKE ?
                    OR district_name LIKE ?
                    OR category_name LIKE ?
                    OR title LIKE ?
                    OR summary LIKE ?
                    OR content LIKE ?
                    OR published_by LIKE ?
                  )";

$stmt_count = $conn->prepare($sql_count);

$stmt_count->bind_param(
    "sssssss",
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term
);

$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_rows / $limit);

$sql_news = "SELECT news_id, title, cover_photo_url, summary 
             FROM news_articles 
             WHERE is_approved = 1 
             AND published_date <= CURDATE()
             AND (
                    Region LIKE ?
                    OR district_name LIKE ?
                    OR category_name LIKE ?
                    OR title LIKE ?
                    OR summary LIKE ?
                    OR content LIKE ?
                    OR published_by LIKE ?
                  )
             ORDER BY published_date DESC
             LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql_news);

$stmt->bind_param(
    "sssssssii",
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $search_term,
    $limit,
    $offset
);
$stmt->execute();
$news_result = $stmt->get_result();
$news_items = $news_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

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
    gap: 15px;
}
.search-header-left {
    flex: 1 1 auto;
}
.search-header-actions {
    display: flex;
    gap: 10px;
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
    white-space: nowrap;
}
.home-link:hover {
    background-color: #d35400;
    color: white;
}
/* Search bar styles */
.search-form {
    display: flex;
    gap: 5px;
    align-items: center;
}
.search-form input {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 14px;
    min-width: 200px;
}
.search-form button {
    background-color: #f97316;
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    white-space: nowrap;
}
.search-form button:hover {
    background-color: #d35400;
}
@media (max-width: 576px) {
    .search-header {
        flex-direction: column;
        align-items: stretch;
    }
    .search-header-actions {
        justify-content: space-between;
    }
    .search-form input {
        min-width: 150px;
    }
}

/* News Card Styles - Fixed size with full image display */
.news-card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
}
.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* Image container for consistent sizing */
.news-card-image-container {
    width: 100%;
    height: 200px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.news-card-image-container img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain; /* Changed from 'cover' to 'contain' to show full image */
    transition: transform 0.3s ease;
}

.news-card-image-container img:hover {
    transform: scale(1.05);
}

.news-card-body {
    padding: 15px;
    flex: 1;
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
            <div class="search-header-left">
                <h2>शोध परिणाम: "<?php echo htmlspecialchars($q); ?>"</h2>
                <p class="text-muted mb-0">एकूण <?php echo $total_rows; ?> बातम्या सापडल्या.</p>
            </div>
            <div class="search-header-actions">
                <!-- Search form -->
                <form class="search-form" method="get" action="search.php">
                    <input type="text" name="q" placeholder="पुन्हा शोधा..." value="<?php echo htmlspecialchars($q); ?>" aria-label="Search">
                    <button type="submit"><i class="bi bi-search"></i> शोधा</button>
                </form>
                <a href="index.php" class="home-link">
                    <i class="bi bi-house-door me-1"></i> मुख्य पृष्ठ
                </a>
            </div>
        </div>

        <?php if ($total_rows > 0): ?>
            <div class="row g-4">
                <?php foreach ($news_items as $news): ?>
                    <div class="col-md-4">
                        <div class="news-card">
                            <div class="news-card-image-container">
                                <img src="<?php echo htmlspecialchars($news['cover_photo_url'] ?: 'components/assets/default-news.jpeg'); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                                     onerror="this.onerror=null; this.src='components/assets/default-news.jpeg';">
                            </div>
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