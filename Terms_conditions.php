<?php
// terms_conditions.php
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
    <title>अटी आणि शर्ती - अमृत महाराष्ट्र</title>
    
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
        
        .terms-container {
            max-width: 1200px;
            margin: 40px auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            padding: 60px 0;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(255, 102, 0, 0.2);
        }
        
        .page-header h1 {
            font-weight: 700;
            font-size: 2.8rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .page-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 800px;
            margin: 0 auto;
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
        
        .info-section {
            background: var(--light-orange);
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 40px;
            border-left: 5px solid var(--primary-orange);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .info-section h2 {
            color: var(--dark-gray);
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .info-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-orange);
            border-radius: 2px;
        }
        
        .info-section p {
            color: var(--dark-gray);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        /* Cards Section */
        .cards-section {
            margin: 50px 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            margin-bottom: 30px;
            border-top: 4px solid var(--primary-orange);
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--white);
            font-size: 30px;
        }
        
        .card-title {
            color: var(--dark-gray);
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .card-text {
            color: #555;
            line-height: 1.7;
            text-align: center;
        }
        
        /* FAQ Section */
        .faq-section {
            background: var(--white);
            border-radius: 10px;
            padding: 50px;
            margin: 60px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
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
        
        .accordion {
            margin-top: 30px;
        }
        
        .accordion-item {
            border: 1px solid #ddd;
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
        
        /* Contact CTA */
        .contact-cta {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: var(--white);
            padding: 60px 40px;
            border-radius: 10px;
            text-align: center;
            margin: 60px 0;
            box-shadow: 0 5px 25px rgba(255, 102, 0, 0.3);
        }
        
        .contact-cta h3 {
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .contact-cta p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .contact-btn {
            background: var(--white);
            color: var(--primary-orange);
            border: 2px solid var(--white);
            font-weight: 600;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .contact-btn:hover {
            background: transparent;
            color: var(--white);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .back-to-top:hover {
            background: var(--dark-orange);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 102, 0, 0.4);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 40px 20px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .info-section,
            .faq-section {
                padding: 30px 20px;
            }
            
            .contact-cta {
                padding: 40px 20px;
            }
            
            .card {
                margin-bottom: 20px;
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
                <li class="breadcrumb-item active" aria-current="page">अटी आणि शर्ती</li>
            </ol>
        </nav>
    </div>

    <div class="container terms-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-file-text"></i> अटी आणि शर्ती</h1>
            <p>आपल्या अधिकारांचे संरक्षण करण्यासाठी या अटी वाचा.</p>
        </div>

        <!-- Information Section -->
        <div class="info-section">
            <h2>समाजासाठी माहिती मांडणे</h2>
            <p>आपल्या शासनाच्या बातम्या आणि विकास कार्यक्रमांची यथार्थ माहिती प्रदान करणे हा आमचा मुख्य उद्देश आहे. आम्ही महाराष्ट्र राज्याच्या विकास, सरकारी योजना आणि जनकल्याणकारी कार्यक्रमांविषयी अद्ययावत माहिती प्रसारित करतो.</p>
            <p>आमच्या पोर्टलद्वारे नागरिकांना त्यांच्या हक्कांबद्दल, सरकारी सेवांबद्दल आणि विकास कार्यक्रमांबद्दल पारदर्शक माहिती उपलब्ध करून दिली जाते. प्रत्येक नागरिकाला अचूक आणि विश्वसनीय माहिती मिळावी यासाठी आम्ही सतत प्रयत्नशील आहोत.</p>
        </div>

        <!-- Cards Section -->
        <div class="cards-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <h3 class="card-title">सत्यता आणि विश्वासार्हता</h3>
                            <p class="card-text">
                                आमच्या पोर्टलवर आपल्याला महाराष्ट्रातील शासनाच्या कामकाजाची अद्ययावत माहिती मिळेल. यामध्ये विकास कार्यक्रम, सार्वजनिक धोरणे आणि महत्त्वाच्या घडामोडींचा समावेश आहे. आम्ही सत्यता व निष्पक्षतेवर जोर देतो, त्यामुळे वाचकांना योग्य आणि विश्वसनीय माहिती मिळेल.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="card-title">समुदाय आणि विकास</h3>
                            <p class="card-text">
                                आमचा लक्ष केंद्रित विषय म्हणजे स्थानिक सरकार, शासकीय कार्यवाही, आणि विकासाच्या उपाययोजना. आपण आपल्या समुदायाला समजून घेऊ इच्छित असाल, किंवा आपले विचार मांडू इच्छित असाल, तर हा प्लॅटफॉर्म आपल्यासाठी उपयुक्त ठरेल. आमच्या अद्वितीय दृष्टिकोनामुळे वाचकांना हवे असल्याप्रमाणे माहिती मिळवता येईल.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="faq-title">सामान्य प्रश्न</h2>
            <p class="text-center mb-5" style="color: #666; font-size: 1.1rem;">
                आपल्या प्रश्नांची स्पष्ट उत्तरे मिळवा. सर्वसामान्य प्रश्नांची उत्तरे येथे दिली आहेत.
            </p>
            
            <div class="accordion" id="faqAccordion">
                <!-- FAQ Item 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="bi bi-question-circle me-2"></i> तुमच्या जबाबदाऱ्या काय आहेत?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" 
                         aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            वापरकर्त्यांनी तुमच्या जबाबदाऱ्या समजून घेणे अत्यावश्यक आहे. आमची प्रमुख जबाबदारी म्हणजे नागरिकांना अचूक, अद्ययावत आणि विश्वसनीय माहिती पुरवणे. आम्ही सरकारी धोरणे, योजना आणि विकास कार्यक्रमांविषयी माहिती पारदर्शक पद्धतीने मांडतो. नागरिकांच्या अधिकारांबाबत जागरूकता निर्माण करणे आणि सार्वजनिक सेवांचा प्रभावी वापर सुनिश्चित करणे हे आमचे कर्तव्य आहे. सामान्य उत्तरे आणि आवश्यक माहिती जाणून घेण्यासाठी, आमच्याशी संपर्क साधा.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="bi bi-file-text me-2"></i> साइटवर काय सामग्री आहे?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" 
                         aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            आमच्या साइटवर महाराष्ट्राच्या विकास, सरकारी धोरणे आणि स्थानिक घडामोडींसाठी समर्थन करणारी माहिती आहे. यामध्ये सरकारी योजना, विकास प्रकल्प, सार्वजनिक सेवा, नागरिक अधिकार, आणि राज्यातील महत्त्वाच्या घडामोडींची माहिती समाविष्ट आहे. तुमच्या अनुभवाला समृद्ध करणारे साधन म्हणून आम्ही येथे आहोत. प्रत्येक माहिती तपासून, सत्यापित करून आणि संदर्भासह प्रसारित केली जाते.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="bi bi-share me-2"></i> सामाजिक जाळ्यात कसे सामील व्हाल?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" 
                         aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            तुम्ही आमच्या सामाजिक जाळ्यात सामील होऊन सर्व अपडेट आणि संवाद मिळवू शकता. आमच्या अधिकृत सोशल मीडिया खात्यांवरून (Facebook, Twitter, Instagram, YouTube) आमच्याशी कनेक्ट होऊ शकता. विविध प्लॅटफॉर्मवर आपले विचार शेअर करा आणि सामुदायिक संवादात भाग घ्या. सामाजिक जाळ्यांवर आमच्याशी जोडलेले राहून नवीनतम माहिती, सरकारी अधिसूचना आणि महत्त्वाच्या सूचना तुम्हाला वेळेवर मिळतील.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            <i class="bi bi-chat-left-text me-2"></i> तुमचे विचार आमच्याशी कसे शेअर कराल?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" 
                         aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            तुम्ही आमच्या कमेंट सेक्शनमध्ये तुमचे विचार व्यक्त करू शकता. प्रत्येक लेखाखाली कमेंट बॉक्स उपलब्ध आहे जिथे तुम्ही तुमचे मत, सूचना किंवा प्रश्न नोंदवू शकता. तसेच 'संपर्क' पेजवरून थेट ईमेल किंवा संपर्क फॉर्मद्वारे आमच्याशी संपर्क साधू शकता. आमच्या लेखांवर तुमचा मत आणि अभिप्राय आम्हाला महत्त्वाचा आहे. तुमच्या प्रतिक्रिया आम्हाला आमची सेवा सुधारण्यास मदत करतात.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            <i class="bi bi-envelope me-2"></i> आमच्या न्यूज़लेटरसाठी कसे नोंदणी कराल?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" 
                         aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            तुम्ही आमच्या वेबसाइटवर नेहमीच्या अद्यतने मिळवण्यासाठी न्यूज़लेटरसाठी नोंदणी करू शकता. होमपेजवर किंवा फुटरमध्ये न्यूज़लेटर सबस्क्रिप्शन बॉक्स उपलब्ध आहे. तुमचा ईमेल पत्ता प्रविष्ट करून 'सबस्क्राइब' बटणावर क्लिक करा. नवीनतम माहिती, सार्वजनिक धोरणांबाबत अपडेट, सरकारी योजना आणि महत्त्वाच्या सूचना तुमच्या ईमेलवर नियमितपणे पाठवल्या जातील. तुम्ही कधीही अनसबस्क्राइब करू शकता.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact CTA -->
        <div class="contact-cta">
            <h3><i class="bi bi-question-circle"></i> तुमच्याकडे वेगळे प्रश्न आहेत का?</h3>
            <p>
                तुमच्या मनात कोणतेही प्रश्न असतील तर आम्हाला जरूर संपर्क करा. आमचा उद्देश तुमच्यासाठी स्पष्टता आणणे आहे. तुमच्या प्रश्नांवर वेगवान प्रतिसाद देण्याचा आमचा प्रयत्न राहील. आमची संपूर्ण टीम तुमच्या सेवेसाठी उपलब्ध आहे.
            </p>
            <a href="contact.php" class="btn contact-btn">
                <i class="bi bi-telephone"></i> संपर्क साधा
            </a>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Back to Top Functionality
    window.onscroll = function() {
        const backToTopBtn = document.getElementById('backToTop');
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopBtn.style.display = 'flex';
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
    
    // FAQ Accordion - Open first by default
    document.addEventListener('DOMContentLoaded', function() {
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
    
    // Card hover effects
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
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