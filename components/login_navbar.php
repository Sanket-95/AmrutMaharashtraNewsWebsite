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
    
    .nav-buttons-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dashboard-btn {
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
    
    .dashboard-btn:hover {
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
    
    .modal-footer {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
    }
    
    .logout-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .logout-btn:hover {
        background: #c82333;
        text-decoration: none;
        color: white;
    }
    
    @media (max-width: 768px) {
        .login-navbar-content {
            padding: 0 10px;
        }
        
        .news-btn, .dashboard-btn {
            padding: 5px 12px;
            font-size: 13px;
        }
        
        .nav-buttons-container {
            gap: 8px;
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
    $username = $_SESSION['username'] ?? '';
    
    // Get current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Define which pages should show which buttons
    $news_approval_pages = ['newsapproval.php', 'newsapproval_details.php'];
    $is_news_approval_page = in_array($current_page, $news_approval_pages);
    
    // Check if current page is dashboard.php
    $is_dashboard_page = ($current_page === 'dashboard.php');
    ?>
    
    <div class="login-navbar">
        <div class="login-navbar-content">
            <?php 
            // Check which button to show based on current page and user role
            if ($user_roll === 'district_user') {
                // For district_user, show "नवीन बातमी" button only on approval pages
                if ($is_news_approval_page) {
                    ?>
                    <a href="post_news.php" class="news-btn">
                        <i class="bi bi-plus-circle"></i> नवीन बातमी
                    </a>
                    <?php
                } else {
                    ?>
                    <div></div>
                    <?php
                }
            } else {
                // For admin and division_head
                if ($is_news_approval_page) {
                    // On approval pages, show "नवीन बातमी" button
                    ?>
                    <a href="post_news.php" class="news-btn">
                        <i class="bi bi-plus-circle"></i> नवीन बातमी
                    </a>
                    <?php
                } elseif ($current_page === 'post_news.php') {
                    // On post_news.php page, show "News" button (to go to approval page)
                    ?>
                    <a href="newsapproval.php" class="news-btn">
                        <i class="bi bi-newspaper"></i> बातमी
                    </a>
                    <?php
                } else {
                    // On other pages, show "News" button
                    ?>
                    <a href="newsapproval.php" class="news-btn">
                        <i class="bi bi-newspaper"></i> बातमी
                    </a>
                    <?php
                }
            }
            ?>
            
            <div class="nav-buttons-container">
                <?php 
                // Show dashboard button only for username "Vijay Joshi" and not on dashboard page
                if (!$is_dashboard_page && $username === 'Vijay Joshi'): 
                ?>
                <a href="dashboard.php" class="dashboard-btn">
                    <i class="bi bi-speedometer2"></i> डॅशबोर्ड
                </a>
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
                            
                            <!-- <div class="user-info-item">
                                <div class="user-info-label">Username</div>
                                <div class="user-info-value"><?php echo htmlspecialchars($username); ?></div>
                            </div> -->
                            
                            <div class="user-info-item">
                                <div class="user-info-label">Role</div>
                                <div class="user-info-value">
                                    <?php 
                                    if ($user_roll === 'admin') {
                                        echo 'Admin';
                                    } elseif ($user_roll === 'division_head') {
                                        echo 'Divisional Head';
                                    } elseif ($user_roll === 'district_user') {
                                        echo 'Publisher';
                                    } else {
                                        echo htmlspecialchars(ucfirst($user_roll));
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="user-info-item">
                                <div class="user-info-label">Location</div>
                                <div class="user-info-value"><?php echo htmlspecialchars($user_location); ?></div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <a href="backend/logout.php" class="logout-btn">
                                Logout
                            </a>
                        </div>
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