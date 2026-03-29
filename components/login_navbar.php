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
        white-space: nowrap;
    }
    
    .news-btn:hover {
        background: #fff5e6;
        transform: translateY(-1px);
    }
    
    .nav-buttons-container {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
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
        white-space: nowrap;
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
        flex-shrink: 0;
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
        width: 280px;
        z-index: 1000;
        border: 1px solid #ffa500;
    }
    
    .profile-modal.show {
        display: block;
    }
    
    .modal-header {
        background: #ff6600;
        color: white;
        padding: 12px 15px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .settings-link {
        color: white;
        font-size: 18px;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }
    
    .settings-link:hover {
        transform: rotate(90deg);
        color: white;
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
        word-break: break-word;
    }
    
    .modal-footer {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
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
    
    .user-mgmt-btn {
        background: #28a745;
        color: white;
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
        white-space: nowrap;
    }
    
    .user-mgmt-btn:hover {
        background: #218838;
        transform: translateY(-1px);
        color: white;
    }
    
    .mobile-menu-toggle {
        display: none;
        background: white;
        color: #ff6600;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .mobile-menu-toggle:hover {
        background: #fff5e6;
    }
    
    .mobile-menu {
        display: none;
        position: absolute;
        top: 52px;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #ff8c00, #ff6600);
        padding: 10px;
        border-top: 1px solid #d35400;
        box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        z-index: 998;
        flex-direction: column;
        gap: 8px;
    }
    
    .mobile-menu.show {
        display: flex;
    }
    
    .mobile-menu-item {
        background: white;
        color: #ff6600;
        padding: 12px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }
    
    .mobile-menu-item:hover {
        background: #fff5e6;
        transform: translateX(5px);
    }
    
    .mobile-menu-item i {
        font-size: 18px;
    }
    
    @media (max-width: 992px) {
        .login-navbar-content {
            padding: 0 12px;
        }
        
        .news-btn, .dashboard-btn, .user-mgmt-btn {
            padding: 5px 12px;
            font-size: 13px;
        }
        
        .nav-buttons-container {
            gap: 6px;
        }
    }
    
    @media (max-width: 768px) {
        .login-navbar-content {
            padding: 0 10px;
        }
        
        .desktop-buttons {
            display: none;
        }
        
        .mobile-menu-toggle {
            display: flex;
        }
        
        .nav-buttons-container {
            gap: 5px;
        }
        
        .profile-btn {
            width: 34px;
            height: 34px;
            font-size: 16px;
        }
        
        .profile-modal {
            width: 260px;
            right: -5px;
        }
    }
    
    @media (max-width: 480px) {
        .news-btn span, .dashboard-btn span, .user-mgmt-btn span {
            display: none;
        }
        
        .news-btn i, .dashboard-btn i, .user-mgmt-btn i {
            font-size: 16px;
            margin: 0;
        }
        
        .news-btn, .dashboard-btn, .user-mgmt-btn {
            padding: 6px 10px;
        }
        
        .profile-btn {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .mobile-menu-item span {
            font-size: 14px;
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
            <!-- Left side - News button (desktop) -->
            <div class="desktop-buttons" style="display: flex; align-items: center;">
                <?php 
                // Check which button to show based on current page and user role
                if ($user_roll === 'district_user') {
                    // For district_user, show "नवीन बातमी" button only on approval pages
                    if ($is_news_approval_page) {
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    }
                } else {
                    // For admin and division_head
                    if ($is_news_approval_page) {
                        // On approval pages, show "नवीन बातमी" button
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    } elseif ($current_page === 'post_news.php') {
                        // On post_news.php page, show "News" button (to go to approval page)
                        ?>
                        <a href="newsapproval.php" class="news-btn">
                            <i class="bi bi-newspaper"></i>
                            <span>बातमी</span>
                        </a>
                        <?php
                    } else {
                        // On other pages, show "News" button
                        ?>
                        <a href="newsapproval.php" class="news-btn">
                            <i class="bi bi-newspaper"></i>
                            <span>बातमी</span>
                        </a>
                        <?php
                    }
                }
                ?>
            </div>
            
            <!-- Right side buttons. -->
            <div class="nav-buttons-container">
                <!-- Desktop buttons -->
                <div class="desktop-buttons" style="display: flex; align-items: center; gap: 8px;">
                    <?php if ($user_roll === 'Super Admin' || $user_name === 'Vijay Joshi'): ?>
                    <a href="ads_analytics.php" class="dashboard-btn">
                        <i class="bi bi-speedometer2"></i>
                        <span>जाहिरात विश्लेषण</span>
                    </a>
                    <?php endif; ?>

                    <?php if ($user_roll === 'Super Admin'): ?>
                    <a href="advertisement_post.php" class="dashboard-btn">
                        <i class="bi bi-megaphone"></i>
                        <span>जाहिरात</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Show dashboard button only for non-district users and not on dashboard page
                    if (!$is_dashboard_page && $user_roll !== 'district_user'):
                    ?>
                    <a href="dashboard.php" class="dashboard-btn">
                        <i class="bi bi-speedometer2"></i>
                        <span>डॅशबोर्ड</span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- User Management Button (for Super Admin) -->
                    <?php if ($user_roll === 'Super Admin'): ?>
                    <a href="newUser.php" class="user-mgmt-btn">
                        <i class="bi bi-people"></i>
                        <span>यूजर व्यवस्थापन</span>
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Toggle Button -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="bi bi-list"></i>
                </button>
                
                <!-- Profile Button -->
                <div class="profile-container">
                    <button class="profile-btn" id="profileButton">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    
                    <div class="profile-modal" id="profileModal">
                        <div class="modal-header">
                            <h5>Profile Info</h5>
                            <a href="profile.php" class="settings-link" title="Profile Settings">
                                <i class="bi bi-gear"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="user-info-item">
                                <div class="user-info-label">Name</div>
                                <div class="user-info-value"><?php echo htmlspecialchars($user_name); ?></div>
                            </div>
                            
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
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <?php 
            // Mobile menu items based on user role and current page
            if ($user_roll === 'district_user') {
                if ($is_news_approval_page) {
                    ?>
                    <a href="post_news.php" class="mobile-menu-item">
                        <i class="bi bi-plus-circle"></i>
                        <span>नवीन बातमी</span>
                    </a>
                    <?php
                }
            } else {
                if ($is_news_approval_page) {
                    ?>
                    <a href="post_news.php" class="mobile-menu-item">
                        <i class="bi bi-plus-circle"></i>
                        <span>नवीन बातमी</span>
                    </a>
                    <?php
                } elseif ($current_page === 'post_news.php') {
                    ?>
                    <a href="newsapproval.php" class="mobile-menu-item">
                        <i class="bi bi-newspaper"></i>
                        <span>बातमी</span>
                    </a>
                    <?php
                } else {
                    ?>
                    <a href="newsapproval.php" class="mobile-menu-item">
                        <i class="bi bi-newspaper"></i>
                        <span>बातमी</span>
                    </a>
                    <?php
                }
            }
            ?>
            
            <?php if ($user_roll === 'Super Admin' || $user_name === 'Vijay Joshi'): ?>
            <a href="ads_analytics.php" class="mobile-menu-item">
                <i class="bi bi-speedometer2"></i>
                <span>जाहिरात विश्लेषण</span>
            </a>
            <?php endif; ?>

            <?php if ($user_roll === 'Super Admin'): ?>
            <a href="advertisement_post.php" class="mobile-menu-item">
                <i class="bi bi-megaphone"></i>
                <span>जाहिरात</span>
            </a>
            <?php endif; ?>
            
            <?php if (!$is_dashboard_page && $user_roll !== 'district_user'): ?>
            <a href="dashboard.php" class="mobile-menu-item">
                <i class="bi bi-speedometer2"></i>
                <span>डॅशबोर्ड</span>
            </a>
            <?php endif; ?>
            
            <?php if ($user_roll === 'Super Admin'): ?>
            <a href="newUser.php" class="mobile-menu-item">
                <i class="bi bi-people"></i>
                <span>यूजर व्यवस्थापन</span>
            </a>
            <?php endif; ?>
            
            <a href="profile.php" class="mobile-menu-item">
                <i class="bi bi-gear"></i>
                <span>प्रोफाइल सेटिंग्ज</span>
            </a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profileButton');
            const profileModal = document.getElementById('profileModal');
            const settingsLink = document.querySelector('.settings-link');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            
            // Profile button click handler
            profileButton.addEventListener('click', function(e) {
                e.stopPropagation();
                profileModal.classList.toggle('show');
                // Close mobile menu if open
                if (mobileMenu.classList.contains('show')) {
                    mobileMenu.classList.remove('show');
                }
            });
            
            // Mobile menu toggle click handler
            mobileMenuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                mobileMenu.classList.toggle('show');
                // Close profile modal if open
                if (profileModal.classList.contains('show')) {
                    profileModal.classList.remove('show');
                }
            });
            
            // Prevent modal from closing when clicking on settings link
            if (settingsLink) {
                settingsLink.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileButton.contains(e.target) && !profileModal.contains(e.target)) {
                    profileModal.classList.remove('show');
                }
                if (!mobileMenuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.remove('show');
                }
            });
            
            // Close menus on window resize if above mobile breakpoint
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    mobileMenu.classList.remove('show');
                }
            });
        });
    </script>
    <?php
}