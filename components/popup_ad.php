<?php
// Base path for ad images
$base_primary = "components/primary_advertised/";

$current_date = date('Y-m-d');

// Fetch ONE random active big ad
$query_popup = "SELECT * FROM ads_management 
                WHERE ad_type = 1
                AND is_active = 1
                AND '$current_date' BETWEEN start_date AND end_date
                ORDER BY RAND()
                LIMIT 1";

$result_popup = mysqli_query($conn, $query_popup);

$popup_ad = null;

if ($row = mysqli_fetch_assoc($result_popup)) {
    $popup_ad = [
        'image' => $base_primary . $row['image_name'],
        'link'  => $row['ad_link'],
        'alt'   => $row['ad_title']
    ];
}
?>

<?php if ($popup_ad): ?>

<!-- POPUP OVERLAY -->
<div id="popupAdOverlay" class="popup-overlay">

    <div class="popup-ad-box">

        <!-- Countdown -->
        <div class="popup-timer" id="popupTimer">
            Ad closes in <span id="countdown">5</span>s
        </div>

        <!-- Ad Image -->
        <a href="<?php echo htmlspecialchars($popup_ad['link']); ?>" 
           target="_blank" 
           rel="noopener noreferrer">

            <img src="<?php echo htmlspecialchars($popup_ad['image']); ?>"
                 alt="<?php echo htmlspecialchars($popup_ad['alt']); ?>"
                 class="popup-ad-image"
                 onerror="this.onerror=null;this.src='photos/noimg.jpeg';">
        </a>

        <!-- Skip Button -->
        <button id="skipAdBtn" class="skip-btn" style="display:none;">
            Skip Ad ✖
        </button>

    </div>
</div>

<style>
/* Overlay */
.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999999;
}

/* Popup box */
.popup-ad-box {
    background: #fff;
    width: 90%;
    max-width: 500px;
    border-radius: 12px;
    text-align: center;
    padding: 15px;
}

/* Image */
.popup-ad-image {
    width: 100%;
    height: auto;
    max-height: 70vh;
    object-fit: contain;
}

/* Timer */
.popup-timer {
    font-size: 14px;
    margin-bottom: 10px;
    font-weight: 600;
}

/* Skip Button */
.skip-btn {
    margin-top: 10px;
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .popup-ad-box {
        max-width: 95%;
        padding: 10px;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let countdown = 5;
    const countdownEl = document.getElementById("countdown");
    const skipBtn = document.getElementById("skipAdBtn");
    const overlay = document.getElementById("popupAdOverlay");

    const timer = setInterval(function () {
        countdown--;
        countdownEl.textContent = countdown;

        if (countdown <= 0) {
            clearInterval(timer);
            document.getElementById("popupTimer").style.display = "none";
            skipBtn.style.display = "inline-block";
        }
    }, 1000);

    skipBtn.addEventListener("click", function () {
        overlay.style.display = "none";
    });

});
</script>

<?php endif; ?>