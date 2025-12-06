<?php
session_start();
include 'components/db_config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle login form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error_message = 'कृपया वापरकर्तानाव आणि पासवर्ड भरा';
    } else {
        // Query to check user credentials
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify MD5 password
            if (md5($password) === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['roll'] = $user['roll'];
                $_SESSION['region'] = $user['region'];
                $_SESSION['district'] = $user['district'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect based on user role
                switch ($user['roll']) {
                    case 'admin':
                        header('Location: post_news.php');
                        break;
                    case 'publisher':
                        header('Location: post_news.php');
                        break;
                    case 'reviewer':
                        header('Location: post_news.php');
                        break;
                    default:
                        header('Location: post_news.php');
                }
                exit();
            } else {
                $error_message = 'अवैध वापरकर्तानाव किंवा पासवर्ड';
            }
        } else {
            $error_message = 'अवैध वापरकर्तानाव किंवा पासवर्ड';
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>लॉगिन - अमृत महाराष्ट्र</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts for Marathi -->
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700&family=Khand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-orange: #FF6600;
            --light-orange: #FF8C00;
            --lighter-orange: #FFA500;
            --bg-orange: #FFF8F0;
            --card-orange: #FFF3E0;
            --shadow-orange: rgba(255, 102, 0, 0.2);
        }
        
        body {
            font-family: 'Mukta', sans-serif;
            background: linear-gradient(135deg, var(--bg-orange) 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            border: 3px solid var(--primary-orange);
            box-shadow: 0 15px 35px var(--shadow-orange);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--light-orange));
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .logo i {
            font-size: 40px;
            color: var(--primary-orange);
        }
        
        .login-header h1 {
            font-family: 'Khand', sans-serif;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-orange);
            margin-bottom: 8px;
            font-family: 'Mukta', sans-serif;
        }
        
        .form-control {
            border: 2px solid #FFD8B5;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            font-family: 'Mukta', sans-serif;
        }
        
        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.25);
        }
        
        .input-group-text {
            background: #FFE8D6;
            border: 2px solid #FFD8B5;
            border-right: none;
            color: var(--primary-orange);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-orange), var(--light-orange));
            color: white;
            border: none;
            padding: 14px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s ease;
            font-family: 'Khand', sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, var(--light-orange), var(--primary-orange));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow-orange);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            font-family: 'Mukta', sans-serif;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: var(--light-orange);
            text-decoration: underline;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        
        /* Decorative elements */
        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 102, 0, 0.1);
            z-index: -1;
        }
        
        .circle-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
        }
        
        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .login-body {
                padding: 20px;
            }
            
            .login-header {
                padding: 20px 15px;
            }
            
            .logo {
                width: 60px;
                height: 60px;
            }
            
            .logo i {
                font-size: 30px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
        
        /* Animation for error message */
        @keyframes shake {
            0%, 100% {transform: translateX(0);}
            10%, 30%, 50%, 70%, 90% {transform: translateX(-5px);}
            20%, 40%, 60%, 80% {transform: translateX(5px);}
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <!-- Decorative Circles -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h1>अमृत महाराष्ट्र</h1>
                <p>वृत्तसेवा पोर्टल</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger mb-3 shake">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm">
                    <!-- Username Field -->
                    <div class="mb-4">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i> वापरकर्तानाव
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user-circle"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="तुमचे वापरकर्तानाव टाइप करा" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i> पासवर्ड
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="तुमचा पासवर्ड टाइप करा" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> लॉगिन करा
                        </button>
                    </div>
                </form>
                
                <!-- Forgot Password Link -->
                <div class="forgot-password">
                    <a href="#" onclick="showForgotPassword()">
                        <i class="fas fa-question-circle me-1"></i> पासवर्ड विसरलात?
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer Text -->
        <div class="footer-text">
            <p>© <?php echo date('Y'); ?> अमृत महाराष्ट्र. सर्व हक्क राखीव.</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Show forgot password alert
        function showForgotPassword() {
            alert('कृपया तुमच्या अॅडमिन किंवा सुपरवायझरशी संपर्क साधा पासवर्ड रिसेट करण्यासाठी.');
            return false;
        }
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('कृपया वापरकर्तानाव आणि पासवर्ड भरा.');
                return false;
            }
            
            return true;
        });
        
        // Add focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focus');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focus');
            });
        });
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>