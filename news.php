<?php
// news.php
session_start();

// Database connection
include 'components/db_config.php';

// Get news ID from URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch news details from database - Fix the query (remove status condition or use correct column name)
$query = "SELECT 
    news_id,
    district_name,
    category_name,
    title,
    cover_photo_url,
    secondary_photo_url,
    summary,
    content,
    published_by,
    published_date
FROM news_articles 
WHERE news_id = ?"; // Removed status condition

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5 text-center'><h2>न्यूज सापडली नाही</h2><a href='index.php' class='btn btn-primary mt-3'>होमपेज वर जा</a></div>";
    include 'components/footer.php';
    exit();
}

$news = $result->fetch_assoc();

// Format dates
$published_date = date('d-m-Y', strtotime($news['published_date']));
$published_time = date('h:i A', strtotime($news['published_date']));

// Marathi category names mapping
$marathi_categories = [
    'today_special' => 'दिनदिशेष',
    'amrut_events' => 'अमृत घडामोडी',
    'beneficiary_story' => 'लाभार्थी स्टोरी',
    'successful_entrepreneur' => 'यशस्वी उद्योजक',
    'words_amrut' => 'शब्दामृत',
    'smart_farmer' => 'स्मार्ट शेतकरी',
    'capable_student' => 'सक्षम दिद्यार्थी',
    'spirituality' => 'अध्यात्म',
    'social_situation' => 'सामाजिक परिस्थिती',
    'women_power' => 'स्त्रीशक्ती',
    'tourism' => 'पर्यटन',
    'amrut_service' => 'अमृत सेवा कार्य',
    'about_us' => 'आमच्या दिशयी',
    'home' => 'मुख्यपृष्ठ'
];

// Get Marathi category name
$category_marathi = $marathi_categories[$news['category_name']] ?? 'अमृत कार्यदीप';

// Include header
include 'components/header.php';
include 'components/navbar.php';

// Current URL for sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Default images
$default_cover_image = 'https://images.unsplash.com/photo-1551135049-8a33b2fb2f5e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
$default_secondary_image = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - अमृत महाराष्ट्र</title>
    
    
    <style>
        .news-detail-container {
            max-width: 1200px;
            margin: 30px auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .news-header {
            border-bottom: 3px solid #ff6600;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .news-publish{
            border-top: 3px solid #ff6600;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .news-title {
            color: #2c3e50;
            font-weight: 700;
            line-height: 1.4;
            margin: 20px 0;
            font-size: 2rem;
        }
        
        .news-meta {
            color: #666;
            font-size: 16px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .news-meta i {
            color: #ff6600;
            margin-right: 8px;
        }
        
        .news-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin: 25px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .news-content {
            line-height: 1.8;
            font-size: 18px;
            color: #333;
            text-align: justify;
            margin-top: 25px;
        }
        
        .news-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #ff6600;
            margin: 30px 0;
            font-style: italic;
            color: #555;
            font-size: 17px;
        }
        
        .social-share {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 40px 0;
            text-align: center;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 20px;
        }
        
        .share-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .facebook { background: #3b5998; }
        .twitter { background: #1da1f2; }
        .linkedin { background: #0077b5; }
        .whatsapp { background: #25d366; }
        .copy-link { background: #6c757d; }
        
        .breadcrumb {
            background: transparent;
            padding: 15px 0;
        }
        
        .breadcrumb-item a {
            color: #ff6600;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #666;
        }
        
        .comments-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #dee2e6;
        }
        
        .comment-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .news-title {
                font-size: 1.5rem;
            }
            
            .news-content {
                font-size: 16px;
            }
            
            .share-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Breadcrumb Navigation -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php"><i class="bi bi-house-door"></i> मुख्यपृष्ठ</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars(mb_substr($news['title'], 0, 50)) . (mb_strlen($news['title']) > 50 ? '...' : ''); ?>
                </li>
            </ol>
        </nav>
    </div>

    <div class="container news-detail-container">
        <!-- News Header -->
        <div class="news-header">
            <h1 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h1>     
        </div>

        <!-- Summary -->
        <?php if (!empty($news['summary'])): ?>
        <div class="news-summary">
            <p class="mb-0"><i class="bi bi-quote"></i> <?php echo nl2br(htmlspecialchars($news['summary'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Secondary Photo -->
        <div class="text-center">
            <?php 
            $secondary_photo = !empty($news['secondary_photo_url']) ? htmlspecialchars($news['secondary_photo_url']) : $default_secondary_image;
            ?>
            <img src="<?php echo $secondary_photo; ?>" 
                 alt="<?php echo htmlspecialchars($news['title']); ?> अतिरिक्त फोटो" 
                 class="news-image"
                 style="max-height: 400px;"
                 onerror="this.onerror=null; this.src='<?php echo $default_secondary_image; ?>';">
        </div>

        <!-- Main Content -->
        <div class="news-content">
            <?php echo nl2br(htmlspecialchars($news['content'])); ?>
        </div>

         <!-- News Header -->
        <div class="news-publish">
            <div class="news-meta">
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><i class="bi bi-person-fill"></i> <strong>प्रकाशक:</strong>
                        <?php echo htmlspecialchars($news['published_by']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="bi bi-calendar-event"></i> <strong>तारीख:</strong>
                        <?php echo $published_date; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><i class="bi bi-clock"></i> <strong>वेळ:</strong>
                        <?php echo $published_time; ?></p>
                    </div>
                </div>
                
                <?php if (!empty($news['district_name'])): ?>
                <div class="row mt-2">
                    <div class="col-12">
                        <p class="mb-0"><i class="bi bi-geo-alt"></i> <strong>जिल्हा:</strong>
                        <?php echo htmlspecialchars($news['district_name']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
       
            

        <!-- Social Share Section -->
        <div class="social-share">
            <h4 class="mb-4"><i class="bi bi-share-fill text-primary"></i> ही बातमी शेअर करा</h4>
            
            <div class="share-buttons">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" 
                   target="_blank" 
                   class="share-btn facebook"
                   title="Facebook वर शेअर करा">
                    <i class="bi bi-facebook"></i>
                </a>
                
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($news['title']); ?>" 
                   target="_blank" 
                   class="share-btn twitter"
                   title="Twitter वर शेअर करा">
                    <i class="bi bi-twitter"></i>
                </a>
                
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($current_url); ?>&title=<?php echo urlencode($news['title']); ?>" 
                   target="_blank" 
                   class="share-btn linkedin"
                   title="LinkedIn वर शेअर करा">
                    <i class="bi bi-linkedin"></i>
                </a>
                
                <a href="https://wa.me/?text=<?php echo urlencode($news['title'] . ' ' . $current_url); ?>" 
                   target="_blank" 
                   class="share-btn whatsapp"
                   title="WhatsApp वर शेअर करा">
                    <i class="bi bi-whatsapp"></i>
                </a>
                
                <button onclick="copyToClipboard()" 
                        class="share-btn copy-link border-0"
                        title="लिंक कॉपी करा">
                    <i class="bi bi-link-45deg"></i>
                </button>
            </div>
            
            <div class="mt-3">
                <small class="text-muted" id="copy-success" style="display: none;">
                    <i class="bi bi-check-circle-fill text-success"></i> लिंक कॉपी झाला!
                </small>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h3 class="mb-4 border-bottom pb-2"><i class="bi bi-chat-left-text"></i> प्रतिक्रिया</h3>
            
            <div class="no-comments text-center py-5">
                <i class="bi bi-chat-left-text display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">अद्याप कोणतीही प्रतिक्रिया नाही</h4>
                <p class="text-muted">तुम्ही चर्चा सुरु करू शकता</p>
            </div>

            <!-- Comment Form -->
            <div class="comment-form">
                <h5 class="mb-4"><i class="bi bi-pencil-square"></i> प्रतिक्रिया लिहा</h5>
                
                <form id="commentForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">नाव <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">ईमेल <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="website" class="form-label">वेबसाइट (ऐच्छिक)</label>
                            <input type="url" class="form-control" id="website">
                        </div>
                        
                        <div class="col-12">
                            <label for="comment" class="form-label">तुमची प्रतिक्रिया <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comment" rows="5" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="saveInfo">
                                <label class="form-check-label" for="saveInfo">
                                    पुढच्या वेळीसाठी माझे नाव, ईमेल आणि वेबसाइट सेव्ह करा
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-send"></i> प्रतिक्रिया पोस्ट करा
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Copy to clipboard function
    function copyToClipboard() {
        const url = "<?php echo $current_url; ?>";
        
        // Create temporary input element
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        
        // Select and copy text
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        // Remove temporary input
        document.body.removeChild(tempInput);
        
        // Show success message
        const successMsg = document.getElementById('copy-success');
        successMsg.style.display = 'block';
        setTimeout(() => {
            successMsg.style.display = 'none';
        }, 3000);
    }
    
    // Handle comment form submission
    document.getElementById('commentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const comment = document.getElementById('comment').value;
        
        // Basic validation
        if (!name || !email || !comment) {
            alert('कृपया सर्व आवश्यक फील्ड भरा');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('कृपया वैध ईमेल पत्ता टाका');
            return;
        }
        
        // Here you would send data to server via AJAX
        // For demo, show success message
        alert('तुमची प्रतिक्रिया सबमिट झाली आहे! लवकरच ती प्रदर्शित केली जाईल.');
        
        // Reset form
        this.reset();
    });
    
    // Handle keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to copy link
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            copyToClipboard();
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            document.getElementById('commentForm').reset();
        }
    });
    
    // Back to top button functionality
    window.onscroll = function() {
        const scrollBtn = document.getElementById('scrollToTop');
        if (scrollBtn) {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollBtn.style.display = 'block';
            } else {
                scrollBtn.style.display = 'none';
            }
        }
    };
    
    function scrollToTop() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
    
    // Function to handle image errors
    function handleImageError(img) {
        img.onerror = null;
        if (img.classList.contains('cover-photo')) {
            img.src = '<?php echo $default_cover_image; ?>';
        } else {
            img.src = '<?php echo $default_secondary_image; ?>';
        }
        img.alt = 'डीफॉल्ट फोटो';
    }
    
    // Add error handlers to images
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.news-image');
        images.forEach(function(img) {
            img.addEventListener('error', function() {
                handleImageError(this);
            });
        });
    });
    </script>

    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" 
            id="scrollToTop" 
            class="btn btn-primary rounded-circle position-fixed"
            style="bottom: 30px; right: 30px; width: 50px; height: 50px; display: none; z-index: 1000;">
        <i class="bi bi-arrow-up"></i>
    </button>
</body>
</html>

<?php
$stmt->close();
$conn->close();
include 'components/footer.php';
?>