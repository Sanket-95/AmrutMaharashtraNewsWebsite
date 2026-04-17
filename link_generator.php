<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only Super Admin can access this page
// if ($_SESSION['roll'] !== 'Super Admin') {
//     header('Location: index.php');
//     exit();
// }

include 'components/db_config.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Get base URL dynamically (works for both localhost and live server)
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Remove port number if present (for localhost:8080 etc.)
    $host = preg_replace('/:\d+$/', '', $host);
    
    // Get script directory
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script_name);
    
    // Remove trailing slash
    $path = rtrim($path, '/\\');
    
    // For live domain, just return domain without path
    if ($host !== 'localhost' && !preg_match('/^\d+\.\d+\.\d+\.\d+$/', $host)) {
        return $protocol . $host;
    }
    
    // For localhost, include the project folder
    return $protocol . $host . $path;
}

// Handle form submission with PRG pattern - MUST be before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_link'])) {
    $link_name = trim(mysqli_real_escape_string($conn, $_POST['link_name']));
    
    if (empty($link_name)) {
        $_SESSION['error_message'] = "कृपया लिंक मालकाचे नाव प्रविष्ट करा.";
    } else {
        // Check if name already exists
        $check_query = "SELECT id FROM amrut_registration_links WHERE link_name = '$link_name'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error_message'] = "हे नाव आधीपासून अस्तित्वात आहे! कृपया वेगळे नाव वापरा.";
        } else {
            // Get the last inserted ID to create link
            $last_id_query = "SELECT MAX(id) as last_id FROM amrut_registration_links";
            $last_id_result = mysqli_query($conn, $last_id_query);
            $last_id_row = mysqli_fetch_assoc($last_id_result);
            $new_id = ($last_id_row['last_id'] ?? 0) + 1;
            
            // Generate full link with proper domain
            $base_url = getBaseUrl();
            $full_link = $base_url . "/amrut_family_registration.php?ref=" . $new_id;
            
            // Insert into database
            $insert_query = "INSERT INTO amrut_registration_links (link_name, link_url) VALUES ('$link_name', '$full_link')";
            
            if (mysqli_query($conn, $insert_query)) {
                $new_id = mysqli_insert_id($conn);
                $full_link = $base_url . "/amrut_family_registration.php?ref=" . $new_id;
                $_SESSION['success_message'] = "लिंक यशस्वीरित्या तयार केली गेली!";
                $_SESSION['new_link'] = $full_link;
            } else {
                $_SESSION['error_message'] = "लिंक तयार करताना त्रुटी: " . mysqli_error($conn);
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Check for session messages
$message = '';
$error = '';
$new_link = '';

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['new_link'])) {
    $new_link = $_SESSION['new_link'];
    unset($_SESSION['new_link']);
}

// Fetch all links
$links = [];
$query = "SELECT * FROM amrut_registration_links ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $links[] = $row;
    }
}

// NOW include header files AFTER all processing
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
?>

<style>
    .link-generator-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    .form-card, .links-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #FF6600, #FF8C00);
        color: white;
        padding: 15px 20px;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .card-header i {
        margin-right: 10px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }
    
    .required {
        color: #dc3545;
        margin-left: 3px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        font-family: 'Mukta', sans-serif;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #FF6600;
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }
    
    .btn-generate {
        background: linear-gradient(135deg, #FF6600, #FF8C00);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3);
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 20px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .table th {
        background: #FFF3E0;
        color: #FF6600;
        font-weight: 600;
    }
    
    .table tr:hover {
        background: #FFF8F0;
    }
    
    .link-cell {
        max-width: 400px;
        word-break: break-all;
    }
    
    .copy-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
    }
    
    .copy-btn:hover {
        background: #218838;
        transform: scale(1.05);
    }
    
    .empty-state {
        text-align: center;
        padding: 50px;
    }
    
    .empty-state i {
        font-size: 60px;
        color: #FFA500;
        margin-bottom: 15px;
    }
    
    .empty-state h4 {
        color: #FF6600;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #666;
    }
    
    .link-preview {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        margin-top: 15px;
        word-break: break-all;
        font-size: 12px;
        font-family: monospace;
    }
    
    @media (max-width: 768px) {
        .link-generator-container {
            padding: 0 15px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table th,
        .table td {
            padding: 10px;
            font-size: 12px;
        }
        
        .link-cell {
            max-width: 150px;
        }
        
        .copy-btn {
            padding: 4px 8px;
            font-size: 10px;
        }
    }
    
    @media (max-width: 576px) {
        .table th:nth-child(3),
        .table td:nth-child(3) {
            display: none;
        }
    }
</style>

<div class="link-generator-container">
    <!-- Generate Link Form -->
    <div class="form-card">
        <div class="card-header">
            <i class="bi bi-link-45deg"></i> नवीन लिंक तयार करा
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($new_link): ?>
                <div class="alert-success" style="background: #e7f3ff; border-color: #2196F3; color: #0c5460;">
                    <i class="bi bi-link"></i> 
                    <strong>नवीन लिंक तयार झाली:</strong><br>
                    <code style="word-break: break-all;"><?php echo htmlspecialchars($new_link); ?></code>
                    <button class="copy-btn mt-2" onclick="copyToClipboard('<?php echo htmlspecialchars($new_link); ?>')">
                        <i class="bi bi-clipboard"></i> लिंक कॉपी करा
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="linkForm">
                <div class="form-group">
                    <label><i class="bi bi-person"></i> लिंक मालकाचे नाव <span class="required">*</span></label>
                    <input type="text" class="form-control" name="link_name" required 
                           placeholder="लिंक मालकाचे नाव "
                           id="linkName">
                    <small class="text-muted">या नावाने लिंक ओळखली जाईल (हे नाव युनिक असावे)</small>
                </div>
                
                <div class="link-preview" id="linkPreview" style="display: none;">
                    <strong><i class="bi bi-eye"></i> लिंक पूर्वावलोकन:</strong><br>
                    <span id="previewUrl"></span>
                </div>
                
                <button type="submit" name="generate_link" class="btn-generate">
                    <i class="bi bi-plus-circle"></i> लिंक तयार करा
                </button>
            </form>
        </div>
    </div>
    
    <!-- Links List -->
    <div class="links-card">
        <div class="card-header">
            <i class="bi bi-table"></i> तयार केलेल्या लिंक्सची यादी
        </div>
        <div class="card-body">
            <?php if (empty($links)): ?>
                <div class="empty-state">
                    <i class="bi bi-link-45deg"></i>
                    <h4>अद्याप कोणतीही लिंक तयार केलेली नाही</h4>
                    <p>कृपया वरील फॉर्ममध्ये लिंक मालकाचे नाव प्रविष्ट करून नवीन लिंक तयार करा.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>क्र.</th>
                                <th>लिंक मालकाचे नाव</th>
                                <th>लिंक</th>
                                <th>तयार करण्याची तारीख</th>
                                <th>कृती</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $index => $link): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($link['link_name']); ?></strong></td>
                                    <td class="link-cell">
                                        <code style="font-size: 12px; word-break: break-all;"><?php echo htmlspecialchars($link['link_url']); ?></code>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($link['created_at'])); ?></td>
                                    <td>
                                        <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($link['link_url']); ?>')">
                                            <i class="bi bi-clipboard"></i> कॉपी
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Copy to clipboard function
function copyToClipboard(text) {
    // Modern approach using navigator.clipboard
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        alert('कॉपी करण्यात अयशस्वी. कृपया मॅन्युअली कॉपी करा.');
    }
    
    document.body.removeChild(textarea);
}

function showCopySuccess() {
    if (typeof toastr !== 'undefined') {
        toastr.success('लिंक कॉपी केली गेली!');
    } else {
        alert('✅ लिंक कॉपी केली गेली!');
    }
}

// Live preview of link
const linkNameInput = document.getElementById('linkName');
if (linkNameInput) {
    linkNameInput.addEventListener('input', function() {
        const linkName = this.value.trim();
        const previewDiv = document.getElementById('linkPreview');
        const previewUrl = document.getElementById('previewUrl');
        
        if (linkName) {
            const baseUrl = '<?php echo getBaseUrl(); ?>';
            previewUrl.textContent = baseUrl + '/amrut_family_registration.php?ref=[ID]';
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    });
}

// Form validation
const linkForm = document.getElementById('linkForm');
if (linkForm) {
    linkForm.addEventListener('submit', function(e) {
        const linkName = document.getElementById('linkName').value.trim();
        if (linkName === '') {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error('कृपया लिंक मालकाचे नाव प्रविष्ट करा.');
            } else {
                alert('कृपया लिंक मालकाचे नाव प्रविष्ट करा.');
            }
        }
    });
}

// Clear form after successful submission
<?php if ($message && !$error): ?>
    if (document.getElementById('linkName')) {
        document.getElementById('linkName').value = '';
        document.getElementById('linkPreview').style.display = 'none';
    }
<?php endif; ?>
</script>

<?php
include 'components/footer.php';
?>