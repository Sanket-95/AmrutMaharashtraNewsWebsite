<!-- Login Navbar Component -->
<style>
    .login-navbar {
        background: linear-gradient(135deg, #ff8c00, #ff6600);
        padding: 8px 0;
        border-bottom: 2px solid #d35400;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: relative;
        z-index: 999;
    }
    
    .login-navbar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 15px;
    }
    
    .news-btn {
        background: white;
        color: #ff6600;
        border: none;
        padding: 6px 15px;
        border-radius: 20px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .news-btn:hover {
        background: #fff5e6;
        transform: translateY(-1px);
    }
    
    .profile-container {
        position: relative;
    }
    
    .profile-btn {
        background: white;
        color: #ff6600;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 18px;
    }
    
    .profile-btn:hover {
        transform: scale(1.05);
    }
    
    .profile-modal {
        display: none;
        position: absolute;
        top: 45px;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        width: 250px;
        z-index: 1000;
        border: 1px solid #ffa500;
    }
    
    .profile-modal.show {
        display: block;
    }
    
    .modal-header {
        background: #ff6600;
        color: white;
        padding: 12px;
        border-radius: 8px 8px 0 0;
    }
    
    .modal-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .user-info-item {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }
    
    .user-info-label {
        color: #666;
        font-size: 12px;
    }
    
    .user-info-value {
        color: #333;
        font-weight: 600;
    }
    
    .logout-btn {
        background: #dc3545;
        color: white;
        border: none;
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
        font-size: 14px;
    }
    
    .logout-btn:hover {
        background: #c82333;
    }
    
    @media (max-width: 768px) {
        .login-navbar-content {
            padding: 0 10px;
        }
        
        .news-btn {
            padding: 5px 12px;
            font-size: 13px;
        }
        
        .profile-btn {
            width: 34px;
            height: 34px;
            font-size: 16px;
        }
        
        .profile-modal {
            width: 220px;
            right: -10px;
        }
    }
</style>

<?php
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['name'] ?? 'User';
    $user_roll = $_SESSION['roll'] ?? '';
    $user_location = $_SESSION['location'] ?? '';
    
    $show_news_button = ($user_roll !== 'district_user');
    ?>
    
    <div class="login-navbar">
        <div class="login-navbar-content">
            <?php if ($show_news_button): ?>
            <a href="newsapproval.php" class="news-btn">
                <i class="bi bi-newspaper"></i> News
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <button class="profile-btn" id="profileButton">
                    <i class="bi bi-person-circle"></i>
                </button>
                
                <div class="profile-modal" id="profileModal">
                    <div class="modal-header">
                        <h5>Profile Info</h5>
                    </div>
                    <div class="modal-body">
                        <div class="user-info-item">
                            <div class="user-info-label">Name</div>
                            <div class="user-info-value"><?php echo htmlspecialchars($user_name); ?></div>
                        </div>
                        
                        <div class="user-info-item">
                            <div class="user-info-label">Role</div>
                            <div class="user-info-value"><?php echo htmlspecialchars(ucfirst($user_roll)); ?></div>
                        </div>
                        
                        <div class="user-info-item">
                            <div class="user-info-label">Location</div>
                            <div class="user-info-value"><?php echo htmlspecialchars($user_location); ?></div>
                        </div>
                        
                        <a href="backend/logout.php" class="logout-btn">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profileButton');
            const profileModal = document.getElementById('profileModal');
            
            profileButton.addEventListener('click', function(e) {
                e.stopPropagation();
                profileModal.classList.toggle('show');
            });
            
            document.addEventListener('click', function(e) {
                if (!profileButton.contains(e.target) && !profileModal.contains(e.target)) {
                    profileModal.classList.remove('show');
                }
            });
        });
    </script>
    <?php
}
?>