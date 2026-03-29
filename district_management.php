<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only admin can access this page
if ($_SESSION['roll'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';


// Get current username for updated_by
$current_username = $_SESSION['username'] ?? '';

// Fetch all divisions from mdivision table for dropdown
$divisions = [];
$div_sql = "SELECT id, division, marathiname FROM mdivision ORDER BY id";
$div_result = mysqli_query($conn, $div_sql);
if ($div_result) {
    while ($row = mysqli_fetch_assoc($div_result)) {
        $divisions[] = $row;
    }
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_district'])) {
        $divisionid = mysqli_real_escape_string($conn, $_POST['divisionid']);
        $division = mysqli_real_escape_string($conn, $_POST['division']);
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $dmarathi = mysqli_real_escape_string($conn, $_POST['dmarathi']);
        $updated_by = mysqli_real_escape_string($conn, $current_username);
        
        // Check if district already exists in the same division
        $check_sql = "SELECT * FROM mdistrict WHERE division = '$division' AND district = '$district'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "हा जिल्हा या विभागात आधीपासून अस्तित्वात आहे!";
        } else {
            $sql = "INSERT INTO mdistrict (divisionid, division, district, dmarathi, updated_by, updated_at) 
                    VALUES ('$divisionid', '$division', '$district', '$dmarathi', '$updated_by', NOW())";
            
            if (mysqli_query($conn, $sql)) {
                $message = "जिल्हा यशस्वीरित्या जोडला गेला!";
            } else {
                $error = "त्रुटी: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle edit
    if (isset($_POST['edit_district'])) {
        $distid = mysqli_real_escape_string($conn, $_POST['distid']);
        $divisionid = mysqli_real_escape_string($conn, $_POST['divisionid']);
        $division = mysqli_real_escape_string($conn, $_POST['division']);
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $dmarathi = mysqli_real_escape_string($conn, $_POST['dmarathi']);
        $updated_by = mysqli_real_escape_string($conn, $current_username);
        
        $sql = "UPDATE mdistrict SET 
                divisionid='$divisionid', 
                division='$division', 
                district='$district', 
                dmarathi='$dmarathi', 
                updated_by='$updated_by', 
                updated_at=NOW() 
                WHERE distid='$distid'";
        
        if (mysqli_query($conn, $sql)) {
            $message = "जिल्हा यशस्वीरित्या अपडेट केला गेला!";
        } else {
            $error = "त्रुटी: " . mysqli_error($conn);
        }
    }
    
    // Handle delete
    if (isset($_POST['delete_district'])) {
        $distid = mysqli_real_escape_string($conn, $_POST['distid']);
        
        $sql = "DELETE FROM mdistrict WHERE distid='$distid'";
        
        if (mysqli_query($conn, $sql)) {
            $message = "जिल्हा यशस्वीरित्या हटविला गेला!";
        } else {
            $error = "त्रुटी: " . mysqli_error($conn);
        }
    }
}

// Pagination settings
$records_per_page = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total districts count for pagination
$total_districts_sql = "SELECT COUNT(*) as total FROM mdistrict";
$total_districts_result = mysqli_query($conn, $total_districts_sql);
$total_districts = mysqli_fetch_assoc($total_districts_result)['total'];
$total_pages = ceil($total_districts / $records_per_page);

// Fetch districts with pagination - ORDER BY distid ASC
$districts = [];
$sql = "SELECT d.*, 
        (SELECT marathiname FROM mdivision WHERE id = d.divisionid) as division_marathi 
        FROM mdistrict d 
        ORDER BY d.distid ASC 
        LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $districts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>जिल्हा व्यवस्थापन - अमृत महाराष्ट्र</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts for Marathi -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Khand:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #fff5e6, #fff0e0);
            font-family: 'Mukta', sans-serif;
            min-height: 100vh;
        }
        
        .district-container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .district-header {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .district-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shine 10s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .district-card {
            background: white;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(255, 102, 0, 0.2);
            overflow: hidden;
            border: 3px solid #FF6600;
            border-top: none;
        }
        
        .form-section {
            background: #fff9f2;
            padding: 30px;
            border-bottom: 2px solid #FFD8B0;
        }
        
        .form-section h4 {
            color: #FF6600;
            font-family: 'Khand', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #FF6600;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-control, .form-select {
            border: 2px solid #FFD8B0;
            border-radius: 10px;
            padding: 12px 15px;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #FF6600;
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25);
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(255, 102, 0, 0.3);
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 102, 0, 0.4);
        }
        
        .table-section {
            padding: 30px;
        }
        
        .table {
            font-family: 'Mukta', sans-serif;
        }
        
        .table thead {
            background: linear-gradient(135deg, #FF6600, #FF8C00);
            color: white;
        }
        
        .table thead th {
            font-weight: 600;
            font-size: 16px;
            padding: 15px;
            border: none;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #FFD8B0;
        }
        
        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 14px;
        }
        
        .edit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            margin-right: 5px;
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .edit-btn:hover, .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination {
            gap: 5px;
        }
        
        .page-link {
            color: #FF6600;
            border: 2px solid #FFD8B0;
            border-radius: 8px;
            padding: 8px 16px;
            font-family: 'Mukta', sans-serif;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: #FF6600;
            color: white;
            border-color: #FF6600;
            transform: translateY(-2px);
        }
        
        .page-item.active .page-link {
            background: #FF6600;
            border-color: #FF6600;
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #ccc;
            border-color: #FFD8B0;
            background: #f8f9fa;
        }
        
        .toastify {
            font-family: 'Mukta', sans-serif !important;
            font-size: 16px !important;
            border-radius: 8px !important;
            padding: 12px 20px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }
        
        .small-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .division-badge {
            background: #fff0e0;
            color: #FF6600;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .info-badge {
            background: #fff0e0;
            color: #FF6600;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .district-container {
                margin: 20px auto;
            }
            
            .form-section, .table-section {
                padding: 20px;
            }
            
            .submit-btn {
                width: 100%;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody td {
                display: block;
                text-align: left;
                padding: 10px;
                border-bottom: 1px solid #FFD8B0;
            }
            
            .table tbody td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                color: #FF6600;
                margin-right: 10px;
                min-width: 100px;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="district-container">
        <!-- Header -->
        <div class="district-header">
            <h2 style="font-family: 'Khand', sans-serif; font-size: 32px;">
                <i class="bi bi-map me-2"></i>जिल्हा व्यवस्थापन
            </h2>
            <p style="font-size: 16px; opacity: 0.9;">नवीन जिल्हा जोडा / संपादित करा</p>
        </div>
        
        <!-- Main Card -->
        <div class="district-card">
            <!-- Add/Edit Form Section -->
            <div class="form-section">
                <h4>
                    <i class="bi bi-plus-circle me-2"></i>
                    नवीन जिल्हा जोडा
                </h4>
                
                <form method="POST" action="" id="districtForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                <i class="bi bi-diagram-3 me-1"></i> विभाग निवडा *
                            </label>
                            <select class="form-select" name="divisionid" id="divisionSelect" required onchange="updateDivisionName()">
                                <option value="">-- विभाग निवडा --</option>
                                <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo $div['id']; ?>" 
                                        data-division="<?php echo htmlspecialchars($div['division']); ?>"
                                        data-marathi="<?php echo htmlspecialchars($div['marathiname']); ?>">
                                    <?php echo htmlspecialchars($div['division']); ?> (<?php echo htmlspecialchars($div['marathiname']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> विभागाचे नाव निवडा
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                <i class="bi bi-globe me-1"></i> विभाग (इंग्रजी)
                            </label>
                            <input type="text" class="form-control" name="division" id="divisionName" 
                                   readonly style="background-color: #f8f9fa;">
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> आपोआप भरेल
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                <i class="bi bi-building me-1"></i> जिल्हा (इंग्रजी) *
                            </label>
                            <input type="text" class="form-control" name="district" 
                                   placeholder="उदा. Palghar, Thane" required id="districtInput">
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> इंग्रजीमध्ये जिल्ह्याचे नाव
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">
                                <i class="bi bi-translate me-1"></i> जिल्हा (मराठी) *
                            </label>
                            <input type="text" class="form-control" name="dmarathi" 
                                   placeholder="उदा. पालघर, ठाणे" required id="marathiInput">
                            <div class="small-note">
                                <i class="bi bi-info-circle"></i> मराठीमध्ये जिल्ह्याचे नाव
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-12 d-flex justify-content-end">
                            <input type="hidden" name="add_district" value="1">
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-save"></i> जतन करा
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Current User Info -->
                <div class="alert alert-light mt-4" style="background: #fff0e0; border: 1px dashed #FF6600;">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span>
                            <i class="bi bi-person-circle" style="color: #FF6600;"></i>
                            <strong>वापरकर्ता:</strong> <?php echo htmlspecialchars($current_username); ?>
                        </span>
                        <span>
                            <i class="bi bi-calendar-check" style="color: #FF6600;"></i>
                            <strong>आजची तारीख:</strong> <?php echo date('d-m-Y H:i:s'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Districts List Section -->
            <div class="table-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 style="color: #FF6600; font-family: 'Khand', sans-serif; margin: 0;">
                        <i class="bi bi-list-ul me-2"></i>
                        जिल्ह्यांची यादी
                    </h4>
                    <span class="info-badge">
                        <i class="bi bi-map"></i> एकूण: <?php echo $total_districts; ?> | पान <?php echo $page; ?>/<?php echo $total_pages; ?>
                    </span>
                </div>
                
                <?php if (empty($districts)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-map" style="font-size: 80px; color: #FFA500;"></i>
                        <h5 style="color: #FF6600; margin-top: 20px;">कोणतेही जिल्हे नाहीत</h5>
                        <p class="text-muted">कृपया वरील फॉर्ममध्ये नवीन जिल्हा जोडा.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>विभाग</th>
                                    <th>जिल्हा (इंग्रजी)</th>
                                    <th>जिल्हा (मराठी)</th>
                                    <th>द्वारे अपडेट</th>
                                    <th>अपडेट तारीख</th>
                                    <th>कृती</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($districts as $dist): ?>
                                <tr>
                                    <td data-label="ID"><strong>#<?php echo $dist['distid']; ?></strong></td>
                                    <td data-label="विभाग">
                                        <span class="division-badge">
                                            <i class="bi bi-diagram-3"></i>
                                            <?php echo htmlspecialchars($dist['division']); ?>
                                            (<?php echo htmlspecialchars($dist['division_marathi'] ?? $dist['division']); ?>)
                                        </span>
                                    </td>
                                    <td data-label="जिल्हा (इंग्रजी)"><?php echo htmlspecialchars($dist['district']); ?></td>
                                    <td data-label="जिल्हा (मराठी)"><?php echo htmlspecialchars($dist['dmarathi']); ?></td>
                                    <td data-label="द्वारे अपडेट">
                                        <i class="bi bi-person-circle" style="color: #FF6600;"></i>
                                        <?php echo htmlspecialchars($dist['updated_by'] ?? 'N/A'); ?>
                                    </td>
                                    <td data-label="अपडेट तारीख">
                                        <i class="bi bi-clock" style="color: #FF6600;"></i>
                                        <?php echo $dist['updated_at'] ? date('d-m-Y H:i', strtotime($dist['updated_at'])) : 'N/A'; ?>
                                    </td>
                                    <td data-label="कृती">
                                        <button class="edit-btn" onclick="editDistrict(
                                            <?php echo $dist['distid']; ?>, 
                                            '<?php echo $dist['divisionid']; ?>', 
                                            '<?php echo htmlspecialchars($dist['division']); ?>',
                                            '<?php echo htmlspecialchars($dist['district']); ?>', 
                                            '<?php echo htmlspecialchars($dist['dmarathi']); ?>'
                                        )">
                                            <i class="bi bi-pencil"></i> संपादन
                                        </button>
                                        <button class="delete-btn" onclick="deleteDistrict(<?php echo $dist['distid']; ?>)">
                                            <i class="bi bi-trash"></i> हटवा
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav aria-label="जिल्हा नेव्हिगेशन">
                            <ul class="pagination">
                                <!-- Previous button -->
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" <?php echo $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                        <i class="bi bi-chevron-left"></i> मागील
                                    </a>
                                </li>
                                
                                <!-- Page numbers -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next button -->
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" <?php echo $page >= $total_pages ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                        पुढील <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #FF6600, #FF8C00); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>जिल्हा संपादित करा
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="distid" id="editDistId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-diagram-3 me-1"></i> विभाग निवडा *
                                </label>
                                <select class="form-select" name="divisionid" id="editDivisionSelect" required onchange="updateEditDivisionName()">
                                    <option value="">-- विभाग निवडा --</option>
                                    <?php foreach ($divisions as $div): ?>
                                    <option value="<?php echo $div['id']; ?>" 
                                            data-division="<?php echo htmlspecialchars($div['division']); ?>"
                                            data-marathi="<?php echo htmlspecialchars($div['marathiname']); ?>">
                                        <?php echo htmlspecialchars($div['division']); ?> (<?php echo htmlspecialchars($div['marathiname']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-globe me-1"></i> विभाग (इंग्रजी)
                                </label>
                                <input type="text" class="form-control" name="division" id="editDivisionName" 
                                       readonly style="background-color: #f8f9fa;">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-building me-1"></i> जिल्हा (इंग्रजी) *
                                </label>
                                <input type="text" class="form-control" name="district" id="editDistrict" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-translate me-1"></i> जिल्हा (मराठी) *
                                </label>
                                <input type="text" class="form-control" name="dmarathi" id="editDmarathi" required>
                            </div>
                        </div>
                        
                        <div class="alert alert-light mt-3" style="background: #fff0e0;">
                            <i class="bi bi-info-circle" style="color: #FF6600;"></i>
                            <small>अपडेट करणारे: <strong><?php echo htmlspecialchars($current_username); ?></strong></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करा</button>
                        <button type="submit" name="edit_district" class="btn" style="background: #FF6600; color: white;">
                            <i class="bi bi-check-circle"></i> अपडेट करा
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #fd7e14); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>जिल्हा हटवा
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="distid" id="deleteDistId">
                        <p style="font-size: 16px;">तुम्हाला हा जिल्हा कायमचा हटवायचा आहे का?</p>
                        <p class="text-danger"><strong>सावधान:</strong> ही क्रिया पूर्ववत करता येणार नाही!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करा</button>
                        <button type="submit" name="delete_district" class="btn btn-danger">
                            <i class="bi bi-trash"></i> हटवा
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Toastify configuration
        function showToast(message, type = 'success') {
            let backgroundColor = '#28a745';
            let icon = '✅';
            
            if (type === 'error') {
                backgroundColor = '#dc3545';
                icon = '❌';
            } else if (type === 'info') {
                backgroundColor = '#17a2b8';
                icon = 'ℹ️';
            }
            
            Toastify({
                text: `${icon} ${message}`,
                duration: 3000,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: backgroundColor,
                    borderRadius: "8px",
                    fontFamily: "'Mukta', sans-serif",
                    fontSize: "16px",
                    padding: "12px 20px",
                    boxShadow: "0 5px 15px rgba(0,0,0,0.2)"
                }
            }).showToast();
        }
        
        // Update division name in main form
        window.updateDivisionName = function() {
            const select = document.getElementById('divisionSelect');
            const selectedOption = select.options[select.selectedIndex];
            const divisionName = selectedOption.getAttribute('data-division');
            document.getElementById('divisionName').value = divisionName || '';
        };
        
        // Update division name in edit form
        window.updateEditDivisionName = function() {
            const select = document.getElementById('editDivisionSelect');
            const selectedOption = select.options[select.selectedIndex];
            const divisionName = selectedOption.getAttribute('data-division');
            document.getElementById('editDivisionName').value = divisionName || '';
        };
        
        // Edit function
        window.editDistrict = function(distid, divisionid, division, district, dmarathi) {
            document.getElementById('editDistId').value = distid;
            document.getElementById('editDivisionSelect').value = divisionid;
            document.getElementById('editDivisionName').value = division;
            document.getElementById('editDistrict').value = district;
            document.getElementById('editDmarathi').value = dmarathi;
            
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        };
        
        // Delete function
        window.deleteDistrict = function(distid) {
            document.getElementById('deleteDistId').value = distid;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        };
        
        // Show messages
        <?php if ($message): ?>
        showToast('<?php echo $message; ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error): ?>
        showToast('<?php echo $error; ?>', 'error');
        <?php endif; ?>
        
        // Form validation
        document.getElementById('districtForm')?.addEventListener('submit', function(e) {
            const divisionSelect = document.getElementById('divisionSelect');
            const district = document.getElementById('districtInput').value.trim();
            const marathi = document.getElementById('marathiInput').value.trim();
            
            if (divisionSelect.value === '') {
                e.preventDefault();
                showToast('कृपया विभाग निवडा!', 'error');
            } else if (district === '' || marathi === '') {
                e.preventDefault();
                showToast('कृपया सर्व फील्ड भरा!', 'error');
            }
        });
    </script>
</body>
</html>

<?php
include 'components/footer.php';
?>