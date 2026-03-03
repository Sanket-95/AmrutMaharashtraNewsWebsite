<?php
session_start();

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection and header
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';

// Configuration – adjust paths as needed
define('PRIMARY_ADS_PATH', 'components/primary_advertised/');
define('SECONDARY_ADS_PATH', 'components/secondary_advertised/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Ensure upload directories exist
if (!is_dir(PRIMARY_ADS_PATH)) mkdir(PRIMARY_ADS_PATH, 0755, true);
if (!is_dir(SECONDARY_ADS_PATH)) mkdir(SECONDARY_ADS_PATH, 0755, true);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $client_name    = trim($_POST['client_name'] ?? '');
    $client_email   = trim($_POST['client_email'] ?? '');
    $ad_title       = trim($_POST['ad_title'] ?? '');
    $ad_link        = trim($_POST['ad_link'] ?? '');
    $ad_type        = isset($_POST['ad_type']) ? (int)$_POST['ad_type'] : 0;
    $payment_method = trim($_POST['payment_method'] ?? '');
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $price          = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $start_date     = $_POST['start_date'] ?? '';
    $end_date       = $_POST['end_date'] ?? '';
    $created_by     = $_SESSION['name'] ?? 'Admin'; // from login session

    // Validation
    if (empty($client_name)) $errors[] = 'ग्राहकाचे नाव आवश्यक आहे.';
    if (empty($ad_title)) $errors[] = 'जाहिरात शीर्षक आवश्यक आहे.';
    if (!in_array($ad_type, [1, 2])) $errors[] = 'वैध प्रकार निवडा.';
    if (empty($payment_method)) $errors[] = 'पेमेंट पद्धत निवडा.';
    if (empty($transaction_id)) $errors[] = 'ट्रांझॅक्शन ID आवश्यक आहे.';
    if ($price <= 0) $errors[] = 'किंमत योग्य प्रविष्ट करा.';
    if (empty($start_date) || empty($end_date)) $errors[] = 'सुरु आणि शेवटची तारीख आवश्यक आहे.';
    if (!empty($start_date) && !empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $errors[] = 'शेवटची तारीख सुरु तारखेपेक्षा नंतरची असावी.';
    }

    // File upload handling
    $image_name = '';
    if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['ad_image']['tmp_name'];
        $file_size = $_FILES['ad_image']['size'];
        $file_ext  = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
            $errors[] = 'फक्त JPG, JPEG, PNG, WEBP फाइल्स स्वीकारल्या जातात.';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $errors[] = 'फाइल साइज 2MB पेक्षा कमी असावी.';
        } else {
            // Generate unique filename
            $image_name = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_dir = ($ad_type == 1) ? PRIMARY_ADS_PATH : SECONDARY_ADS_PATH;
            $upload_path = $upload_dir . $image_name;

            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $errors[] = 'फाइल अपलोड करताना त्रुटी.';
                $image_name = '';
            }
        }
    } else {
        $errors[] = 'जाहिरात प्रतिमा आवश्यक आहे.';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO ads_management 
                (client_name, client_email, ad_title, image_name, ad_link, ad_type, payment_method, transaction_id, price, start_date, end_date, created_by, payment_status, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sssssisssdss',
            $client_name,
            $client_email,
            $ad_title,
            $image_name,
            $ad_link,
            $ad_type,
            $payment_method,
            $transaction_id,
            $price,
            $start_date,
            $end_date,
            $created_by
        );

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'डेटाबेसमध्ये त्रुटी: ' . $conn->error;
            // If insert fails, delete the uploaded image
            if (!empty($image_name) && file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-orange text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> नवीन जाहिरात जोडा</h5>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    जाहिरात यशस्वीरित्या जोडली गेली. 
                    <a href="advertisement_add.php" class="alert-link">आणखी एक जोडा</a> किंवा 
                    <a href="advertisement_management.php" class="alert-link">व्यवस्थापनाकडे जा</a>.
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ग्राहकाचे नाव *</label>
                            <input type="text" name="client_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ईमेल (optional)</label>
                            <input type="email" name="client_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['client_email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">जाहिरात शीर्षक *</label>
                            <input type="text" name="ad_title" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['ad_title'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">लिंक (optional)</label>
                            <input type="url" name="ad_link" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['ad_link'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">प्रकार *</label>
                            <select name="ad_type" class="form-select" required>
                                <option value="">-- निवडा --</option>
                                <option value="1" <?php echo (isset($_POST['ad_type']) && $_POST['ad_type'] == 1) ? 'selected' : ''; ?>>मोठी जाहिरात</option>
                                <option value="2" <?php echo (isset($_POST['ad_type']) && $_POST['ad_type'] == 2) ? 'selected' : ''; ?>>छोटी जाहिरात</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">प्रतिमा * (जास्तीत जास्त 2MB, फक्त jpg/png/webp)</label>
                            <input type="file" name="ad_image" class="form-control" 
                                   accept=".jpg,.jpeg,.png,.webp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">पेमेंट पद्धत *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">-- निवडा --</option>
                                <option value="Cash" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cash') ? 'selected' : ''; ?>>रोख</option>
                                <option value="Cheque" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cheque') ? 'selected' : ''; ?>>धनादेश</option>
                                <option value="Online Transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Online Transfer') ? 'selected' : ''; ?>>ऑनलाईन ट्रान्सफर</option>
                                <option value="UPI" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'UPI') ? 'selected' : ''; ?>>UPI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ट्रांझॅक्शन ID *</label>
                            <input type="text" name="transaction_id" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">किंमत (₹) *</label>
                            <input type="number" step="0.01" name="price" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">सुरु तारीख *</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">शेवट तारीख *</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12 text-end">
                            <a href="advertisement_management.php" class="btn btn-secondary">रद्द करा</a>
                            <button type="submit" class="btn btn-orange">जाहिरात जोडा</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .bg-orange { background-color: #FF6600; }
    .btn-orange {
        background-color: #FF6600;
        color: white;
    }
    .btn-orange:hover {
        background-color: #e65c00;
        color: white;
    }
</style>

<?php
include 'components/footer.php';
$conn->close();
?>