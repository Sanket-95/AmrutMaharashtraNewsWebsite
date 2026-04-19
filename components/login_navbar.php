<!-- Login Navbar Component with User Management Dropdown -->
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
    
    /* Desktop Dropdown Styles */
    .desktop-only {
        display: flex;
    }
    
    .dashboard-dropdown {
        position: relative;
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
        cursor: pointer;
    }
    
    .dashboard-btn:hover {
        background: #fff5e6;
        transform: translateY(-1px);
    }
    
    .dashboard-btn i:last-child {
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .dashboard-dropdown.open .dashboard-btn i:last-child {
        transform: rotate(180deg);
    }
    
    .dashboard-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        min-width: 200px;
        z-index: 1000;
        margin-top: 5px;
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .dashboard-dropdown.open .dashboard-dropdown-menu {
        display: block;
    }
    
    .dashboard-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .dashboard-dropdown-item:last-child {
        border-bottom: none;
    }
    
    .dashboard-dropdown-item:hover {
        background: #fff5e6;
        color: #ff6600;
    }
    
    .dashboard-dropdown-item i {
        font-size: 16px;
        width: 20px;
        color: #ff6600;
    }
    
    /* Advertisement Dropdown Styles */
    .ad-dropdown {
        position: relative;
    }
    
    .ad-btn {
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
        cursor: pointer;
    }
    
    .ad-btn:hover {
        background: #fff5e6;
        transform: translateY(-1px);
    }
    
    .ad-btn i:last-child {
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .ad-dropdown.open .ad-btn i:last-child {
        transform: rotate(180deg);
    }
    
    .ad-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        min-width: 200px;
        z-index: 1000;
        margin-top: 5px;
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .ad-dropdown.open .ad-dropdown-menu {
        display: block;
    }
    
    .ad-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .ad-dropdown-item:last-child {
        border-bottom: none;
    }
    
    .ad-dropdown-item:hover {
        background: #fff5e6;
        color: #ff6600;
    }
    
    .ad-dropdown-item i {
        font-size: 16px;
        width: 20px;
        color: #ff6600;
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
    
    /* User Management Dropdown Styles */
    .user-mgmt-dropdown {
        position: relative;
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
        cursor: pointer;
    }
    
    .user-mgmt-btn:hover {
        background: #218838;
        transform: translateY(-1px);
        color: white;
    }
    
    .user-mgmt-btn i:last-child {
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .user-mgmt-dropdown.open .user-mgmt-btn i:last-child {
        transform: rotate(180deg);
    }
    
    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        min-width: 200px;
        z-index: 1000;
        margin-top: 5px;
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .user-mgmt-dropdown.open .dropdown-menu {
        display: block;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .dropdown-item:last-child {
        border-bottom: none;
    }
    
    .dropdown-item:hover {
        background: #fff5e6;
        color: #ff6600;
    }
    
    .dropdown-item i {
        font-size: 16px;
        width: 20px;
        color: #ff6600;
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
    
    /* Mobile dropdown submenu */
    .mobile-submenu {
        margin-left: 30px;
        margin-top: 5px;
        display: none;
        flex-direction: column;
        gap: 5px;
    }
    
    .mobile-submenu.show {
        display: flex;
    }
    
    .mobile-submenu-item {
        background: rgba(255, 255, 255, 0.9);
        color: #ff6600;
        padding: 10px 15px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        font-size: 13px;
    }
    
    .mobile-submenu-item:hover {
        background: white;
        transform: translateX(5px);
    }
    
    .mobile-menu-parent {
        cursor: pointer;
        justify-content: space-between;
    }
    
    .mobile-menu-parent .arrow-icon {
        transition: transform 0.3s ease;
    }
    
    .mobile-menu-parent.open .arrow-icon {
        transform: rotate(180deg);
    }
    
    /* Desktop styles */
    @media (min-width: 769px) {
        .mobile-only {
            display: none !important;
        }
        .desktop-only {
            display: flex !important;
        }
    }
    
    /* Mobile styles */
    @media (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }
        .mobile-only {
            display: block;
        }
        
        .login-navbar-content {
            padding: 0 10px;
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
    
    // Determine if user has access to dashboard
    $has_dashboard_access = (!$is_dashboard_page && $user_roll !== 'district_user');
    
    // Determine if user has access to ads analytics
    $has_ads_analytics_access = ($user_roll === 'Super Admin' || $user_name === 'Vijay Joshi');
    
    // Check if any dashboard items are visible
    $show_dashboard_dropdown = ($has_dashboard_access || $has_ads_analytics_access);
    
    // Check if user has access to advertisement section (Super Admin only)
    $has_ad_access = ($user_roll === 'Super Admin');
    ?>
    
    <div class="login-navbar">
        <div class="login-navbar-content">
            <!-- Left side - News button -->
            <div class="desktop-only" style="display: flex; align-items: center;">
                <?php 
                // Check which button to show based on current page and user role
                if ($user_roll === 'district_user') {
                    if ($is_news_approval_page) {
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    }
                } else {
                    if ($is_news_approval_page) {
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    } elseif ($current_page === 'post_news.php') {
                        ?>
                        <a href="newsapproval.php" class="news-btn">
                            <i class="bi bi-newspaper"></i>
                            <span>बातमी</span>
                        </a>
                        <?php
                    } else {
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
            
            <!-- Mobile news button -->
            <div class="mobile-only">
                <?php 
                if ($user_roll === 'district_user') {
                    if ($is_news_approval_page) {
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    }
                } else {
                    if ($is_news_approval_page) {
                        ?>
                        <a href="post_news.php" class="news-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>नवीन बातमी</span>
                        </a>
                        <?php
                    } elseif ($current_page === 'post_news.php') {
                        ?>
                        <a href="newsapproval.php" class="news-btn">
                            <i class="bi bi-newspaper"></i>
                            <span>बातमी</span>
                        </a>
                        <?php
                    } else {
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
            
            <!-- Right side buttons -->
            <div class="nav-buttons-container">
                <!-- DESKTOP BUTTONS (visible only on desktop) -->
                <div class="desktop-only" style="display: flex; align-items: center; gap: 8px;">
                    
                    <!-- Dashboard Dropdown (Combined) -->
                    <?php if ($show_dashboard_dropdown): ?>
                    <div class="dashboard-dropdown" id="dashboardDropdown">
                        <button class="dashboard-btn" id="dashboardBtn">
                            <i class="bi bi-speedometer2"></i>
                            <span>डॅशबोर्ड</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dashboard-dropdown-menu">
                            <?php if ($has_dashboard_access): ?>
                            <a href="dashboard.php" class="dashboard-dropdown-item">
                                <i class="bi bi-newspaper"></i>
                                <span>न्यूज डॅशबोर्ड</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($has_ads_analytics_access): ?>
                            <a href="ads_analytics.php" class="dashboard-dropdown-item">
                                <i class="bi bi-graph-up"></i>
                                <span>जाहिरात विश्लेषण</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Advertisement Dropdown (for Super Admin only) -->
                    <?php if ($has_ad_access): ?>
                    <div class="ad-dropdown" id="adDropdown">
                        <button class="ad-btn" id="adBtn">
                            <i class="bi bi-megaphone"></i>
                            <span>जाहिरात</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="ad-dropdown-menu">
                            <a href="advertisement_post.php" class="ad-dropdown-item">
                                <i class="bi bi-plus-circle"></i>
                                <span>जाहिरात पोस्टिंग</span>
                            </a>
                            <a href="social_media_ads_gallery.php" class="ad-dropdown-item">
                                <i class="bi bi-images"></i>
                                <span>जाहिरात प्रतिमा गॅलरी</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- User Management Dropdown (for Super Admin) -->
                    <?php if ($user_roll === 'Super Admin'): ?>
                    <div class="user-mgmt-dropdown" id="userMgmtDropdown">
                        <button class="user-mgmt-btn" id="userMgmtBtn">
                            <i class="bi bi-people"></i>
                            <span>व्यवस्थापन</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="newUser.php" class="dropdown-item">
                                <i class="bi bi-person-plus"></i>
                                <span>यूजर व्यवस्थापन</span>
                            </a>
                            <a href="division_management.php" class="dropdown-item">
                                <i class="bi bi-diagram-3"></i>
                                <span>विभाग व्यवस्थापन</span>
                            </a>
                            <a href="district_management.php" class="dropdown-item">
                                <i class="bi bi-geo-alt"></i>
                                <span>जिल्हा व्यवस्थापन</span>
                            </a>
                            <a href="manage_nav_categories.php" class="dropdown-item">
                                <i class="bi bi-list-ul"></i>
                                <span>नेव्हिगेशन कॅटेगरी व्यवस्थापन</span>
                            </a>
                            <a href="link_generator.php" class="dropdown-item">
                                <i class="bi bi-link-45deg"></i>
                                <span>लिंक जनरेटर</span>
                            </a>
                        </div>
                    </div>
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
        
        <!-- MOBILE MENU (visible only on mobile) -->
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
            
            <!-- Mobile Dashboard Dropdown -->
            <?php if ($show_dashboard_dropdown): ?>
            <div class="mobile-menu-parent mobile-menu-item" id="mobileDashboardParent">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-speedometer2"></i>
                        <span>डॅशबोर्ड</span>
                    </span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="mobile-submenu" id="mobileDashboardSubmenu">
                <?php if ($has_dashboard_access): ?>
                <a href="dashboard.php" class="mobile-submenu-item">
                    <i class="bi bi-newspaper"></i>
                    <span>न्यूज डॅशबोर्ड</span>
                </a>
                <?php endif; ?>
                
                <?php if ($has_ads_analytics_access): ?>
                <a href="ads_analytics.php" class="mobile-submenu-item">
                    <i class="bi bi-graph-up"></i>
                    <span>जाहिरात विश्लेषण</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Mobile Advertisement Dropdown (for Super Admin only) -->
            <?php if ($has_ad_access): ?>
            <div class="mobile-menu-parent mobile-menu-item" id="mobileAdParent">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-megaphone"></i>
                        <span>जाहिरात</span>
                    </span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="mobile-submenu" id="mobileAdSubmenu">
                <a href="advertisement_post.php" class="mobile-submenu-item">
                    <i class="bi bi-plus-circle"></i>
                    <span>जाहिरात पोस्टिंग</span>
                </a>
                <a href="social_media_ads_gallery.php" class="mobile-submenu-item">
                    <i class="bi bi-images"></i>
                    <span>जाहिरात प्रतिमा गॅलरी</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Mobile User Management with Submenu (for Super Admin) -->
            <?php if ($user_roll === 'Super Admin'): ?>
            <div class="mobile-menu-parent mobile-menu-item" id="mobileUserMgmtParent">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-people"></i>
                        <span>व्यवस्थापन</span>
                    </span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </div>
            </div>
            <div class="mobile-submenu" id="mobileUserSubmenu">
                <a href="newUser.php" class="mobile-submenu-item">
                    <i class="bi bi-person-plus"></i>
                    <span>यूजर व्यवस्थापन</span>
                </a>
                <a href="division_management.php" class="mobile-submenu-item">
                    <i class="bi bi-diagram-3"></i>
                    <span>विभाग व्यवस्थापन</span>
                </a>
                <a href="district_management.php" class="mobile-submenu-item">
                    <i class="bi bi-geo-alt"></i>
                    <span>जिल्हा व्यवस्थापन</span>
                </a>
                <a href="manage_nav_categories.php" class="mobile-submenu-item">
                    <i class="bi bi-list-ul"></i>
                    <span>नेव्हिगेशन कॅटेगरी व्यवस्थापन</span>
                </a>
                <a href="link_generator.php" class="mobile-submenu-item">
                    <i class="bi bi-link-45deg"></i>
                    <span>लिंक जनरेटर</span>
                </a>
            </div>
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
            
            // Only initialize desktop dropdowns on desktop screens
            function initDesktopDropdowns() {
                if (window.innerWidth > 768) {
                    // Desktop dropdown for Dashboard
                    const dashboardDropdown = document.getElementById('dashboardDropdown');
                    const dashboardBtn = document.getElementById('dashboardBtn');
                    
                    if (dashboardDropdown && dashboardBtn) {
                        // Remove existing listeners to avoid duplicates
                        const newDashboardBtn = dashboardBtn.cloneNode(true);
                        dashboardBtn.parentNode.replaceChild(newDashboardBtn, dashboardBtn);
                        const newDashboardDropdown = dashboardDropdown.cloneNode(true);
                        dashboardDropdown.parentNode.replaceChild(newDashboardDropdown, dashboardDropdown);
                        
                        const finalDashboardBtn = document.getElementById('dashboardBtn');
                        const finalDashboardDropdown = document.getElementById('dashboardDropdown');
                        
                        if (finalDashboardBtn && finalDashboardDropdown) {
                            finalDashboardBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                finalDashboardDropdown.classList.toggle('open');
                                if (profileModal.classList.contains('show')) {
                                    profileModal.classList.remove('show');
                                }
                                const adDropdown = document.getElementById('adDropdown');
                                const userMgmtDropdown = document.getElementById('userMgmtDropdown');
                                if (adDropdown && adDropdown.classList.contains('open')) {
                                    adDropdown.classList.remove('open');
                                }
                                if (userMgmtDropdown && userMgmtDropdown.classList.contains('open')) {
                                    userMgmtDropdown.classList.remove('open');
                                }
                            });
                        }
                    }
                    
                    // Desktop dropdown for Advertisement
                    const adDropdown = document.getElementById('adDropdown');
                    const adBtn = document.getElementById('adBtn');
                    
                    if (adDropdown && adBtn) {
                        const newAdBtn = adBtn.cloneNode(true);
                        adBtn.parentNode.replaceChild(newAdBtn, adBtn);
                        const newAdDropdown = adDropdown.cloneNode(true);
                        adDropdown.parentNode.replaceChild(newAdDropdown, adDropdown);
                        
                        const finalAdBtn = document.getElementById('adBtn');
                        const finalAdDropdown = document.getElementById('adDropdown');
                        
                        if (finalAdBtn && finalAdDropdown) {
                            finalAdBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                finalAdDropdown.classList.toggle('open');
                                if (profileModal.classList.contains('show')) {
                                    profileModal.classList.remove('show');
                                }
                                const dashboardDropdown = document.getElementById('dashboardDropdown');
                                const userMgmtDropdown = document.getElementById('userMgmtDropdown');
                                if (dashboardDropdown && dashboardDropdown.classList.contains('open')) {
                                    dashboardDropdown.classList.remove('open');
                                }
                                if (userMgmtDropdown && userMgmtDropdown.classList.contains('open')) {
                                    userMgmtDropdown.classList.remove('open');
                                }
                            });
                        }
                    }
                    
                    // Desktop dropdown for User Management
                    const userMgmtDropdown = document.getElementById('userMgmtDropdown');
                    const userMgmtBtn = document.getElementById('userMgmtBtn');
                    
                    if (userMgmtDropdown && userMgmtBtn) {
                        const newUserMgmtBtn = userMgmtBtn.cloneNode(true);
                        userMgmtBtn.parentNode.replaceChild(newUserMgmtBtn, userMgmtBtn);
                        const newUserMgmtDropdown = userMgmtDropdown.cloneNode(true);
                        userMgmtDropdown.parentNode.replaceChild(newUserMgmtDropdown, userMgmtDropdown);
                        
                        const finalUserMgmtBtn = document.getElementById('userMgmtBtn');
                        const finalUserMgmtDropdown = document.getElementById('userMgmtDropdown');
                        
                        if (finalUserMgmtBtn && finalUserMgmtDropdown) {
                            finalUserMgmtBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                finalUserMgmtDropdown.classList.toggle('open');
                                if (profileModal.classList.contains('show')) {
                                    profileModal.classList.remove('show');
                                }
                                const dashboardDropdown = document.getElementById('dashboardDropdown');
                                const adDropdown = document.getElementById('adDropdown');
                                if (dashboardDropdown && dashboardDropdown.classList.contains('open')) {
                                    dashboardDropdown.classList.remove('open');
                                }
                                if (adDropdown && adDropdown.classList.contains('open')) {
                                    adDropdown.classList.remove('open');
                                }
                            });
                        }
                    }
                }
            }
            
            // Mobile submenu for Dashboard
            const mobileDashboardParent = document.getElementById('mobileDashboardParent');
            const mobileDashboardSubmenu = document.getElementById('mobileDashboardSubmenu');
            
            if (mobileDashboardParent && mobileDashboardSubmenu) {
                mobileDashboardParent.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('open');
                    mobileDashboardSubmenu.classList.toggle('show');
                });
            }
            
            // Mobile submenu for Advertisement
            const mobileAdParent = document.getElementById('mobileAdParent');
            const mobileAdSubmenu = document.getElementById('mobileAdSubmenu');
            
            if (mobileAdParent && mobileAdSubmenu) {
                mobileAdParent.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('open');
                    mobileAdSubmenu.classList.toggle('show');
                });
            }
            
            // Mobile submenu for User Management
            const mobileUserMgmtParent = document.getElementById('mobileUserMgmtParent');
            const mobileUserSubmenu = document.getElementById('mobileUserSubmenu');
            
            if (mobileUserMgmtParent && mobileUserSubmenu) {
                mobileUserMgmtParent.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('open');
                    mobileUserSubmenu.classList.toggle('show');
                });
            }
            
            // Profile button click handler
            if (profileButton) {
                profileButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileModal.classList.toggle('show');
                    // Close desktop dropdowns if open
                    const dashboardDropdown = document.getElementById('dashboardDropdown');
                    const adDropdown = document.getElementById('adDropdown');
                    const userMgmtDropdown = document.getElementById('userMgmtDropdown');
                    if (dashboardDropdown && dashboardDropdown.classList.contains('open')) {
                        dashboardDropdown.classList.remove('open');
                    }
                    if (adDropdown && adDropdown.classList.contains('open')) {
                        adDropdown.classList.remove('open');
                    }
                    if (userMgmtDropdown && userMgmtDropdown.classList.contains('open')) {
                        userMgmtDropdown.classList.remove('open');
                    }
                    // Close mobile menu if open
                    if (mobileMenu.classList.contains('show')) {
                        mobileMenu.classList.remove('show');
                    }
                });
            }
            
            // Mobile menu toggle click handler
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenu.classList.toggle('show');
                    // Close profile modal if open
                    if (profileModal.classList.contains('show')) {
                        profileModal.classList.remove('show');
                    }
                });
            }
            
            // Prevent modal from closing when clicking on settings link
            if (settingsLink) {
                settingsLink.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                // Close profile modal
                if (profileButton && !profileButton.contains(e.target) && profileModal && !profileModal.contains(e.target)) {
                    profileModal.classList.remove('show');
                }
                
                // Close mobile menu
                if (mobileMenuToggle && !mobileMenuToggle.contains(e.target) && mobileMenu && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.remove('show');
                }
                
                // Close desktop dropdowns only on desktop
                if (window.innerWidth > 768) {
                    const dashboardDropdown = document.getElementById('dashboardDropdown');
                    const dashboardBtn = document.getElementById('dashboardBtn');
                    const adDropdown = document.getElementById('adDropdown');
                    const adBtn = document.getElementById('adBtn');
                    const userMgmtDropdown = document.getElementById('userMgmtDropdown');
                    const userMgmtBtn = document.getElementById('userMgmtBtn');
                    
                    if (dashboardDropdown && dashboardBtn && !dashboardBtn.contains(e.target) && !dashboardDropdown.contains(e.target)) {
                        dashboardDropdown.classList.remove('open');
                    }
                    if (adDropdown && adBtn && !adBtn.contains(e.target) && !adDropdown.contains(e.target)) {
                        adDropdown.classList.remove('open');
                    }
                    if (userMgmtDropdown && userMgmtBtn && !userMgmtBtn.contains(e.target) && !userMgmtDropdown.contains(e.target)) {
                        userMgmtDropdown.classList.remove('open');
                    }
                }
            });
            
            // Initialize desktop dropdowns
            initDesktopDropdowns();
            
            // Re-initialize on resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    if (mobileMenu) mobileMenu.classList.remove('show');
                    initDesktopDropdowns();
                }
            });
        });
    </script>
    <?php
}
?>