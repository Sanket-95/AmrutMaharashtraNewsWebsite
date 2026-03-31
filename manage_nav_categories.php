<?php
// manage_nav_categories.php - FIXED (No resubmission warning)
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'components/db_config.php';

// Start output buffering
ob_start();

// Function to redirect without header errors
function redirectToSelf() {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Handle all POST requests first (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = false;
    $message = '';
    $error = '';
    
    if (isset($_POST['add_category'])) {
        $value_name = trim($_POST['value_name']);
        $label_name = trim($_POST['label_name']);
        $type = $_POST['type'];
        $group_name = trim($_POST['group_name']);
        $display_order = (int)$_POST['display_order'];
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $url = ($type === 'link') ? trim($_POST['url']) : null;
        
        // Check for duplicate
        $check = $conn->prepare("SELECT cid FROM nav_categories WHERE value_name = ?");
        $check->bind_param('s', $value_name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "व्हॅल्यू '$value_name' आधीच अस्तित्वात आहे";
        } else {
            $stmt = $conn->prepare("INSERT INTO nav_categories (value_name, label_name, parent_id, type, url, group_name, display_order, is_enable) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param('ssisssi', $value_name, $label_name, $parent_id, $type, $url, $group_name, $display_order);
            
            if ($stmt->execute()) {
                $message = "✓ श्रेणी यशस्वीरित्या जोडली";
                $redirect = true;
            } else {
                $error = "त्रुटी: " . $conn->error;
            }
        }
    }
    
    if (isset($_POST['edit_category'])) {
        $cid = (int)$_POST['cid'];
        $value_name = trim($_POST['value_name']);
        $label_name = trim($_POST['label_name']);
        $type = $_POST['type'];
        $group_name = trim($_POST['group_name']);
        $display_order = (int)$_POST['display_order'];
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $url = ($type === 'link') ? trim($_POST['url']) : null;
        
        $stmt = $conn->prepare("UPDATE nav_categories SET value_name=?, label_name=?, parent_id=?, type=?, url=?, group_name=?, display_order=? WHERE cid=?");
        $stmt->bind_param('ssisssii', $value_name, $label_name, $parent_id, $type, $url, $group_name, $display_order, $cid);
        
        if ($stmt->execute()) {
            $message = "✓ श्रेणी अद्यतनित केली";
            $redirect = true;
        } else {
            $error = "त्रुटी: " . $conn->error;
        }
    }
    
    // Store messages in session for after redirect
    if ($redirect) {
        $_SESSION['form_message'] = $message;
        redirectToSelf();
    } elseif ($error) {
        $_SESSION['form_error'] = $error;
        redirectToSelf();
    }
}

// Handle GET actions (delete, toggle, edit)
if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    
    $check = $conn->prepare("SELECT COUNT(*) FROM nav_categories WHERE parent_id = ?");
    $check->bind_param('i', $cid);
    $check->execute();
    $has_children = $check->get_result()->fetch_row()[0] > 0;
    
    if ($has_children) {
        $_SESSION['form_error'] = "ही श्रेणी हटवू शकत नाही (उप-श्रेणी आहेत)";
    } else {
        $stmt = $conn->prepare("DELETE FROM nav_categories WHERE cid = ?");
        $stmt->bind_param('i', $cid);
        if ($stmt->execute()) {
            $_SESSION['form_message'] = "✓ श्रेणी हटवली";
        }
    }
    redirectToSelf();
}

if (isset($_GET['toggle'])) {
    $cid = (int)$_GET['toggle'];
    $current = (int)$_GET['status'];
    $new = $current ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE nav_categories SET is_enable = ? WHERE cid = ?");
    $stmt->bind_param('ii', $new, $cid);
    if ($stmt->execute()) {
        $_SESSION['form_message'] = "✓ स्थिती बदलली";
    }
    redirectToSelf();
}

// Get messages from session and clear them
$message = isset($_SESSION['form_message']) ? $_SESSION['form_message'] : '';
$error = isset($_SESSION['form_error']) ? $_SESSION['form_error'] : '';
unset($_SESSION['form_message']);
unset($_SESSION['form_error']);

// Get all categories
$categories = [];
$result = $conn->query("SELECT * FROM nav_categories ORDER BY group_name, parent_id, display_order");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Build tree for display
$tree = [];
$map = [];
foreach ($categories as $cat) {
    $map[$cat['cid']] = $cat;
    $map[$cat['cid']]['children'] = [];
}
foreach ($categories as $cat) {
    if ($cat['parent_id'] === null) {
        $tree[] = &$map[$cat['cid']];
    } else {
        if (isset($map[$cat['parent_id']])) {
            $map[$cat['parent_id']]['children'][] = &$map[$cat['cid']];
        }
    }
}

// Get parent groups for dropdown
$parent_groups = array_filter($categories, function($c) {
    return $c['parent_id'] === null && $c['type'] === 'group';
});

// Get edit data if editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($categories as $cat) {
        if ($cat['cid'] == $edit_id) {
            $edit_data = $cat;
            break;
        }
    }
}

include 'components/header.php';
include 'components/navbar.php';
include 'components/login_navbar.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-orange text-white">
            <h5>नेव्हिगेशन कॅटेगरी व्यवस्थापन</h5>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <strong><?php echo $edit_data ? '✏️ श्रेणी संपादित करा' : '➕ नवीन श्रेणी जोडा'; ?></strong>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="cid" value="<?php echo $edit_data['cid']; ?>">
                            <input type="hidden" name="edit_category" value="1">
                        <?php else: ?>
                            <input type="hidden" name="add_category" value="1">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>व्हॅल्यू नाव *</label>
                                <input type="text" name="value_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($edit_data['value_name'] ?? ''); ?>" required>
                                <small>उदा: news, about_us, events</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>लेबल नाव (मराठी) *</label>
                                <input type="text" name="label_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($edit_data['label_name'] ?? ''); ?>" required>
                                <small>उदा: वार्ता, आमच्याविषयी</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>प्रकार *</label>
                                <select name="type" class="form-select" id="type" required>
                                    <option value="category" <?php echo ($edit_data['type'] ?? '') == 'category' ? 'selected' : ''; ?>>श्रेणी</option>
                                    <option value="group" <?php echo ($edit_data['type'] ?? '') == 'group' ? 'selected' : ''; ?>>ग्रुप</option>
                                    <option value="link" <?php echo ($edit_data['type'] ?? '') == 'link' ? 'selected' : ''; ?>>लिंक</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>ग्रुप नाव *</label>
                                <select name="group_name" class="form-select" id="group_name">
                                    <option value="main" <?php echo ($edit_data['group_name'] ?? '') == 'main' ? 'selected' : ''; ?>>main</option>
                                    <option value="content" <?php echo ($edit_data['group_name'] ?? '') == 'content' ? 'selected' : ''; ?>>content</option>
                                    <option value="other" <?php echo ($edit_data['group_name'] ?? '') == 'other' ? 'selected' : ''; ?>>other (नवीन)</option>
                                </select>
                                <input type="text" name="custom_group" class="form-control mt-2" id="custom_group" 
                                       placeholder="नवीन ग्रुप नाव" style="display:none;">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>क्रमांक</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?php echo $edit_data['display_order'] ?? '1'; ?>">
                            </div>
                        </div>
                        
                        <div class="row" id="parent_row">
                            <div class="col-md-6 mb-3">
                                <label>पालक श्रेणी</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- मुख्य श्रेणी (पालक नाही) --</option>
                                    <?php foreach ($parent_groups as $p): ?>
                                        <option value="<?php echo $p['cid']; ?>" 
                                            <?php echo ($edit_data['parent_id'] ?? '') == $p['cid'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['label_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="url_row">
                                <label>URL (फक्त लिंकसाठी)</label>
                                <input type="text" name="url" class="form-control" 
                                       value="<?php echo htmlspecialchars($edit_data['url'] ?? ''); ?>" 
                                       placeholder="about_us.php">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-orange">
                            <?php echo $edit_data ? 'अद्यतनित करा' : 'जोडा'; ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="manage_nav_categories.php" class="btn btn-secondary">रद्द करा</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Categories List -->
            <div class="card">
                <div class="card-header bg-light">
                    <strong>📋 श्रेणी सूची</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>व्हॅल्यू</th>
                                    <th>लेबल</th>
                                    <th>प्रकार</th>
                                    <th>पालक</th>
                                    <th>ग्रुप</th>
                                    <th>क्रम</th>
                                    <th>स्थिती</th>
                                    <th>कार्य</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tree as $cat): ?>
                                    <?php displayCategoryRow($cat, 0, $conn); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function displayCategoryRow($cat, $level, $conn) {
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    $prefix = $level > 0 ? '└─ ' : '';
    
    $type_badge = match($cat['type']) {
        'group' => '<span class="badge bg-primary">ग्रुप</span>',
        'category' => '<span class="badge bg-success">श्रेणी</span>',
        'link' => '<span class="badge bg-info">लिंक</span>',
        default => ''
    };
    
    $status = $cat['is_enable'] ? 
        '<span class="badge bg-success">सक्षम</span>' : 
        '<span class="badge bg-danger">अक्षम</span>';
    
    $parent_name = '-';
    if ($cat['parent_id']) {
        $stmt = $conn->prepare("SELECT label_name FROM nav_categories WHERE cid = ?");
        $stmt->bind_param('i', $cat['parent_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $parent_name = $row['label_name'];
        }
    }
    
    echo '<tr>';
    echo '<td>' . $cat['cid'] . '</td>';
    echo '<td>' . $indent . $prefix . '<code>' . htmlspecialchars($cat['value_name']) . '</code></td>';
    echo '<td><strong>' . htmlspecialchars($cat['label_name']) . '</strong></td>';
    echo '<td>' . $type_badge . '</td>';
    echo '<td>' . $parent_name . '</td>';
    echo '<td><span class="badge bg-secondary">' . htmlspecialchars($cat['group_name']) . '</span></td>';
    echo '<td>' . $cat['display_order'] . '</td>';
    echo '<td>' . $status . '</td>';
    echo '<td>
            <a href="?edit=' . $cat['cid'] . '" class="btn btn-sm btn-warning">✏️</a>
            <a href="?toggle=' . $cat['cid'] . '&status=' . $cat['is_enable'] . '" class="btn btn-sm btn-info" onclick="return confirm(\'स्थिती बदलायची?\')">🔁</a>
            <a href="?delete=' . $cat['cid'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'हटवायची?\')">🗑️</a>
           </div>
        </div>
    </div>
    ';
    echo '</tr>';
    
    if (!empty($cat['children'])) {
        foreach ($cat['children'] as $child) {
            displayCategoryRow($child, $level + 1, $conn);
        }
    }
}
?>

<style>
.bg-orange { background-color: #FF6600; }
.btn-orange { background-color: #FF6600; color: white; }
.btn-orange:hover { background-color: #e65c00; color: white; }
.badge { font-size: 0.75rem; }
code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
.table-sm td, .table-sm th { padding: 0.5rem; vertical-align: middle; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const groupSelect = document.getElementById('group_name');
    const customGroup = document.getElementById('custom_group');
    const urlRow = document.getElementById('url_row');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'link') {
                urlRow.style.display = 'block';
            } else {
                urlRow.style.display = 'none';
            }
        });
        typeSelect.dispatchEvent(new Event('change'));
    }
    
    if (groupSelect) {
        groupSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                customGroup.style.display = 'block';
                customGroup.name = 'group_name';
                customGroup.required = true;
                groupSelect.name = '';
            } else {
                customGroup.style.display = 'none';
                customGroup.name = '';
                customGroup.required = false;
                groupSelect.name = 'group_name';
            }
        });
        groupSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php 
include 'components/footer.php'; 
$conn->close();
ob_end_flush();
?>