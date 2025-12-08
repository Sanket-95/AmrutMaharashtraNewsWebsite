<?php
// contact.php
session_start();
include 'components/db_config.php';
include 'components/header.php';
include 'components/navbar.php';
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>संपर्क - अमृत महाराष्ट्र</title>
    
    <style>
        :root {
            --primary-orange: #FF6600;
            --secondary-orange: #FF8C00;
            --light-orange: #FFE5CC;
            --dark-orange: #CC5200;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --dark-gray: #333333;
        }
        
        body {
            font-family: 'Noto Sans Devanagari', 'Segoe UI', sans-serif;
        }
        
        .contact-container {
            max-width: 1400px;
            margin: 40px auto;
            font-family: 'Noto Sans Devanagari', 'Segoe UI', sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            padding: 80px 0;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(255, 102, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .page-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .page-header h1 {
            font-weight: 800;
            font-size: 3rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .page-header p {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            line-height: 1.6;
        }
        
        .page-subtitle {
            color: var(--dark-orange);
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .breadcrumb-section {
            background: var(--light-gray);
            padding: 15px 0;
            border-radius: 8px;
            margin-bottom: 40px;
        }
        
        .breadcrumb {
            margin: 0;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: var(--dark-orange);
            text-decoration: underline;
        }
        
        .breadcrumb-item.active {
            color: var(--dark-gray);
            font-weight: 600;
        }
        
        .contact-wrapper {
            margin: 50px 0;
        }
        
        .contact-info-card {
            background: var(--white);
            border-radius: 10px;
            padding: 40px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-top: 5px solid var(--primary-orange);
        }
        
        .contact-info-card h3 {
            color: var(--dark-gray);
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-orange);
        }
        
        .contact-method {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 24px;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .contact-details h4 {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .contact-details p {
            color: #555;
            margin-bottom: 5px;
            line-height: 1.6;
        }
        
        .contact-details a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-details a:hover {
            color: var(--dark-orange);
            text-decoration: underline;
        }
        
        .contact-btn {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .contact-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 102, 0, 0.3);
            color: var(--white);
        }
        
        .contact-btn i {
            margin-right: 8px;
        }
        
        /* Contact Form */
        .contact-form-card {
            background: var(--white);
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-top: 5px solid var(--primary-orange);
        }
        
        .contact-form-card h3 {
            color: var(--dark-gray);
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-orange);
        }
        
        .form-label {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        }
        
        .form-select {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        /* Google Map */
        .map-container {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-top: 40px;
        }
        
        .map-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .map-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .rating-badge {
            background: var(--white);
            color: var(--primary-orange);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        
        .rating-badge i {
            margin-right: 5px;
        }
        
        .map-frame {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        .map-location {
            padding: 20px;
            background: var(--light-gray);
        }
        
        .map-location h4 {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .map-location p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 5px;
        }
        
        /* FAQ Section */
        .faq-section {
            background: var(--white);
            border-radius: 10px;
            padding: 50px;
            margin: 60px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .faq-title {
            color: var(--dark-gray);
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            padding-bottom: 20px;
        }
        
        .faq-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-orange);
            border-radius: 2px;
        }
        
        .accordion-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            background: var(--white);
        }
        
        .accordion-button {
            background: var(--light-gray);
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1.1rem;
            padding: 20px 25px;
            border: none;
            box-shadow: none;
        }
        
        .accordion-button:not(.collapsed) {
            background: var(--light-orange);
            color: var(--primary-orange);
        }
        
        .accordion-button:focus {
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.25);
            border-color: var(--primary-orange);
        }
        
        .accordion-body {
            padding: 25px;
            background: var(--white);
            color: #555;
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        .accordion-button:after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23FF6600'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        
        .accordion-button:not(.collapsed):after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23FF6600'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        
        /* Newsletter Section */
        .newsletter-section {
            background: linear-gradient(135deg, #1a237e, #283593);
            color: var(--white);
            padding: 60px 40px;
            border-radius: 10px;
            margin: 60px 0;
            text-align: center;
            box-shadow: 0 10px 30px rgba(26, 35, 126, 0.2);
        }
        
        .newsletter-section h3 {
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .newsletter-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }
        
        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .newsletter-input-group {
            display: flex;
            gap: 10px;
        }
        
        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
        }
        
        .newsletter-btn {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .newsletter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .newsletter-note {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 15px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 50px 20px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .contact-info-card,
            .contact-form-card,
            .faq-section {
                padding: 30px 20px;
            }
            
            .contact-method {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-icon {
                margin: 0 auto 15px;
            }
            
            .newsletter-input-group {
                flex-direction: column;
            }
            
            .newsletter-input,
            .newsletter-btn {
                width: 100%;
            }
            
            .map-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Breadcrumb Navigation -->
    <div class="container breadcrumb-section">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php"><i class="bi bi-house-door"></i> Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">संपर्क</li>
            </ol>
        </nav>
    </div>

    <div class="container contact-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h6 class="page-subtitle">Speak, Share, Engage</h6>
                <h1><i class="bi bi-chat-left-text"></i> आमच्याशी संपर्क साधा</h1>
                <p>आपल्या प्रश्नांना किंवा टिप्पण्या आमच्या सहयोगी टीमपर्यंत पोचवा. आम्ही तुमच्याशी लवकरच संपर्क साधू.</p>
            </div>
        </div>

        <!-- Contact Information & Form -->
        <div class="contact-wrapper">
            <div class="row g-4">
                <!-- Contact Information -->
                <div class="col-lg-6">
                    <div class="contact-info-card">
                        <h3><i class="bi bi-info-circle"></i> संपर्क माहिती</h3>
                        
                        <!-- Email -->
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>ई-मेल</h4>
                                <p>आमच्याशी संपर्क साधण्यासाठी खालील ई-मेल वापरा.</p>
                                <a href="mailto:info@mahaamrut.org.in">
                                    <i class="bi bi-envelope-fill"></i> info@mahaamrut.org.in
                                </a>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>फोन</h4>
                                <p>तुमच्या विचारांसाठी आमचा दुरध्वनी क्रमांक:</p>
                                <a href="tel:+919730151450">
                                    <i class="bi bi-telephone-fill"></i> +91 97301 51450
                                </a>
                            </div>
                        </div>
                        
                        <!-- Office -->
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>कार्यालय</h4>
                                <p>
                                    Academy Of Maharashtra Research, Upliftment & Training ( Amrut ),<br>
                                    Maharaja Sayajirao Gaikwad Udyog Bhavan, Fifth Floor,<br>
                                    Aundh, Pune 411067.
                                </p>
                                <a href="#" class="contact-btn mt-2" onclick="getDirections()">
                                    <i class="bi bi-compass"></i> मार्गदर्शन मिळवा
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-6">
                    <div class="contact-form-card">
                        <h3><i class="bi bi-pencil-square"></i> संपर्क फॉर्म</h3>
                        
                        <form id="contactForm" method="POST" action="submit_contact.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">नाव <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="आपले नाव प्रविष्ट करा" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">ई-मेल <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="आपला ई-मेल प्रविष्ट करा" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="service" class="form-label">सेवा निवडा <span class="text-danger">*</span></label>
                                    <select class="form-select" id="service" name="service" required>
                                        <option selected disabled>सेवा निवडा</option>
                                        <option value="news_inquiry">बातमी विचारणा</option>
                                        <option value="technical_support">तांत्रिक मदत</option>
                                        <option value="feedback">अभिप्राय</option>
                                        <option value="partnership">भागीदारी</option>
                                        <option value="other">इतर</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label for="message" class="form-label">संदेश <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" placeholder="आपला संदेश येथे लिहा" required></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                        <label class="form-check-label" for="newsletter">
                                            आमच्या न्यूजलेटरसाठी सदस्यता घ्या
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn contact-btn px-5 py-3">
                                        <i class="bi bi-send"></i> पाठवा
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Map Section -->
        <div class="map-container">
            <div class="map-header">
                <h3><i class="bi bi-geo-alt"></i> Academy of Maharashtra Research, Upliftment & Training (AMRUT)</h3>
                <div class="rating-badge">
                    <i class="bi bi-star-fill"></i> 5.0 ★★★★★ 5 reviews
                </div>
            </div>
            
            <!-- Google Map Embed -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3783.041172165445!2d73.8048423154016!3d18.52829458740771!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc2bf8b3e2a7a3f%3A0x3f6b6e7f8d2d2b1e!2sMaharaja%20Sayajirao%20Gaikwad%20Udyog%20Bhavan%2C%20Aundh%2C%20Pune%2C%20Maharashtra%20411067!5e0!3m2!1sen!2sin!4v1648625341234!5m2!1sen!2sin"
                class="map-frame"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            
            <div class="map-location">
                <h4><i class="bi bi-buildings"></i> ठिकाण</h4>
                <p>
                    <strong>पत्ता:</strong> 5th Floor, Maharaja Sayajirao Gaikwad Udyog Bhavan,<br>
                    Ward No. 8, Aundh Gaon, Aundh, Pune,<br>
                    Maharashtra 411067
                </p>
                <a href="https://maps.google.com/?q=Maharaja+Sayajirao+Gaikwad+Udyog+Bhavan,+Aundh,+Pune" 
                   target="_blank" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-map"></i> मोठा नकाशा पहा
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="faq-title"><i class="bi bi-question-circle"></i> सामान्य प्रश्न</h2>
            <p class="text-center mb-5" style="color: #666; font-size: 1.1rem;">
                आमच्या सेवा आणि सामग्रीबाबत सामान्य प्रश्नांची उत्तरं.
            </p>
            
            <div class="accordion" id="faqAccordion">
                <!-- FAQ Item 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="bi bi-search me-2"></i> आपण कोणती विषयक माहिती कशी मिळवू शकता?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" 
                         aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आपण आमच्या वेबसाइटवर प्रदर्शित केलेला सर्व माहिती थेट आमच्या मुख्यपृष्ठावरून किंवा श्रेणीनुसार वर्गीकृत लेखांद्वारे मिळवू शकता. तसेच, खासकरून महाराष्ट्रातील वाढीच्या किंवा लोकशाहीची माहिती, शासकीय योजनांची अद्ययावत माहिती इथे उपलब्ध आहे. आमच्या संकेतस्थळाच्या सर्च बारचा वापर करून आपण विशिष्ट माहिती शोधू शकता.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="bi bi-newspaper me-2"></i> आपणास कोणती स्थानिक बातमी महत्त्वाची आहे?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" 
                         aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आमच्या बातम्या स्थानिक स्तरावर संबंधित आणि महत्त्वाच्या असतात, विशेषत: आपल्या राज्यातील शासकीय धोरणे आणि विकास योजनांविषयी. आपल्याला संवाद साधताना एखाद्या विशिष्ट विषयात अधिक माहिती हवी असल्यास, कृपया आमच्या विशेष लेख आणि संपादकीय विभागाकडे पहा. आम्ही स्थानिक विकास, शासकीय योजना, आणि समुदायातील घडामोडींवर भर देतो.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="bi bi-share me-2"></i> आपण सोशल मीडियावर कसे संलग्न राहू शकता?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" 
                         aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आमचे सोशल मीडिया पेजेस नियमितपणे अद्ययावत केले जातात, जेथे आपल्याला ताज्या बातम्या, सामान्य चर्चा, आणि विविध उपक्रमांबद्दल माहिती मिळेल. फेसबुक, ट्विटर, इंस्टाग्राम आणि यूट्यूब या प्लॅटफॉर्मवर आमच्याशी जोडून राहा. आमच्या सोशल मीडिया वर आपण आपल्या विचारांना व्यक्त करू शकता, टिप्पण्या देऊ शकता आणि इतर वाचकांशी संवाद साधू शकता.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            <i class="bi bi-shield-check me-2"></i> आपण आमच्या वृत्तांकनात कशाप्रकारे विश्वास ठेवू शकता?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" 
                         aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आमच्या संपादकीय समूहाने वर्तमनातील घटनांवर प्रभावी आणि निष्पक्ष माहिती प्रदान करण्यासाठी खूप मेहनत घेतली आहे. आम्ही सत्यता आणि पारदर्शकतेवर भर देतो. प्रत्येक बातमी तपासणी केल्यानंतर आणि अधिकृत स्रोतांकडून मिळालेल्या माहितीच्या आधारे प्रकाशित केली जाते. आमची सर्व माहिती तथ्यांवर आधारित आणि संदर्भासह प्रसारित केली जाते.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            <i class="bi bi-bell me-2"></i> आपको नोटीफिकेशन्स कशा मिळवता येऊ शकतात?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" 
                         aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आपले अपडेट्स मिळवण्यासाठी, कृपया आमच्या नोटिफिकेशन सिस्टीमसाठी नोंदणी करा. आमच्या वेबसाइटवर 'सदस्यता घ्या' बटणावर क्लिक करून तुमचा ई-मेल पत्ता द्या. हे सुनिश्चित करेल की आपण नवीनतम बातम्या, महत्त्वाच्या सूचना आणि नवीन वैशिष्ट्यांबद्दल सुरू राहाल. आपणास ई-मेल, SMS किंवा ब्राउझर नोटिफिकेशन्सद्वारे सूचना मिळू शकतात.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Subscription -->
        <div class="newsletter-section">
            <h3><i class="bi bi-envelope-paper"></i> आपल्या सगळ्या महत्त्वाच्या बातम्यांसाठी सदस्यता घ्या</h3>
            <p>
                आमच्या न्यूजलेटरमध्ये सामील व्हा आणि महाराष्ट्र सरकारच्या विकास प्रकल्पांची सर्व नवीनतम माहिती आणि ताज्या बातम्या मिळवा. आपली ई-मेल पत्ता टाका आणि लाभ घ्या!
            </p>
            
            <form class="newsletter-form" id="newsletterForm">
                <div class="newsletter-input-group">
                    <input type="email" class="newsletter-input" placeholder="आपला ई-मेल प्रविष्ट करा" required>
                    <button type="submit" class="newsletter-btn">
                        <i class="bi bi-send"></i> सदस्यता घ्या
                    </button>
                </div>
                <p class="newsletter-note">
                    सदस्यता घेण्यासाठी क्लिक करून, आपण आमच्या अटी आणि शर्तींवर सहमत आहात.
                </p>
            </form>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="btn btn-primary rounded-circle position-fixed" 
            onclick="scrollToTop()" 
            id="backToTop"
            style="bottom: 30px; right: 30px; width: 50px; height: 50px; display: none; z-index: 1000;">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Get Directions function
    function getDirections() {
        const address = "Maharaja Sayajirao Gaikwad Udyog Bhavan, Aundh, Pune, Maharashtra 411067";
        const encodedAddress = encodeURIComponent(address);
        const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`;
        window.open(mapsUrl, '_blank');
    }
    
    // Contact form validation
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const service = document.getElementById('service').value;
        const message = document.getElementById('message').value.trim();
        
        // Basic validation
        if (!name || !email || !service || !message) {
            alert('कृपया सर्व आवश्यक फील्ड भरा');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('कृपया वैध ई-मेल पत्ता टाका');
            return;
        }
        
        // Show success message (in real implementation, this would submit to server)
        alert('आपला संदेश यशस्वीरित्या पाठवला गेला आहे! आम्ही लवकरच आपल्याशी संपर्क साधू.');
        
        // Reset form
        this.reset();
    });
    
    // Newsletter form
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('input[type="email"]');
        const email = emailInput.value.trim();
        
        if (!email) {
            alert('कृपया ई-मेल पत्ता टाका');
            return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('कृपया वैध ई-मेल पत्ता टाका');
            return;
        }
        
        alert('न्यूजलेटरसाठी यशस्वीरित्या नोंदणी झाली आहे! धन्यवाद.');
        emailInput.value = '';
    });
    
    // Back to top button
    window.onscroll = function() {
        const backToTopBtn = document.getElementById('backToTop');
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    };
    
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Initialize accordion
    document.addEventListener('DOMContentLoaded', function() {
        // Open first FAQ item
        const firstAccordion = document.getElementById('collapseOne');
        if (firstAccordion) {
            firstAccordion.classList.add('show');
        }
        
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
include 'components/footer.php';
$conn->close();
?>