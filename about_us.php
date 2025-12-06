<?php
include 'components/header.php';
include 'components/navbar.php';
?>

<style>
    /* About Us Page Styles */
    .about-us-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    /* Home Navigation Button */
    .home-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        color: #333;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        border: 2px solid #f97316;
        transition: all 0.3s ease;
        margin-bottom: 30px;
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .home-nav-btn i {
        color: #f97316;
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }
    
    .home-nav-btn:hover {
        background: linear-gradient(135deg, #fb923c, #f97316);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        border-color: #f97316;
    }
    
    .home-nav-btn:hover i {
        color: white;
    }
    
    /* About Header */
    .about-header {
        text-align: center;
        margin-bottom: 50px;
        padding: 30px;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border-radius: 15px;
        border: 2px solid #fed7aa;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .about-title {
        color: #c2410c;
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .about-subtitle {
        color: #7c2d12;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    /* About Content */
    .about-content {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
    }
    
    .about-section {
        margin-bottom: 35px;
    }
    
    .section-title {
        color: #f97316;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid #fed7aa;
        display: inline-block;
    }
    
    .section-content {
        font-size: 1.2rem;
        line-height: 1.8;
        color: #334155;
        text-align: justify;
    }
    
    .highlight-box {
        background: #fffbeb;
        border-left: 4px solid #f97316;
        padding: 25px;
        margin: 30px 0;
        border-radius: 0 10px 10px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .highlight-box p {
        margin: 0;
        font-size: 1.25rem;
        color: #7c2d12;
        font-weight: 600;
        line-height: 1.7;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .about-us-container {
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .about-header {
            padding: 20px;
        }
        
        .about-title {
            font-size: 2.2rem;
        }
        
        .about-subtitle {
            font-size: 1.1rem;
        }
        
        .about-content {
            padding: 25px;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .section-content {
            font-size: 1.1rem;
        }
        
        .home-nav-btn {
            padding: 8px 16px;
            font-size: 0.95rem;
        }
    }
    
    @media (max-width: 576px) {
        .about-title {
            font-size: 1.8rem;
        }
        
        .about-subtitle {
            font-size: 1rem;
        }
        
        .about-content {
            padding: 20px;
        }
        
        .section-content {
            font-size: 1.05rem;
            line-height: 1.7;
        }
        
        .highlight-box {
            padding: 20px;
        }
        
        .highlight-box p {
            font-size: 1.1rem;
        }
    }
</style>

<div class="about-us-container">
    <!-- Home Navigation Button -->
    <a href="index.php" class="home-nav-btn" title="मुख्यपृष्ठावर परत जा">
        <i class="bi bi-house-door"></i>
        <span>मुख्यपृष्ठ</span>
    </a>
    
    <!-- About Header -->
    <div class="about-header">
        <h1 class="about-title">आमच्याविषयी...</h1>
        <p class="about-subtitle">अमृत महाराष्ट्र - श्रमेव जयते</p>
    </div>
    
    <!-- About Content - EXACTLY AS PROVIDED -->
    <div class="about-content">
        <div class="about-section">
            <h2 class="section-title">अमृत महाराष्ट्र</h2>
            <div class="section-content">
                <p>महाराष्ट्र संशोधन, उन्नती आणि प्रशिक्षण प्रबोधिनी अर्थात अमृत ही महाराष्ट्र शासनाची स्वायत्त संस्था असून, अन्य कोणतेही सरकारी लाभ न मिळणाऱ्या खुल्या गटातील आर्थिकदृष्ट्या दुर्बल घटकांसाठी या संस्थेच्या विविध योजना आहेत.</p>
                
                <div class="highlight-box">
                    <p>श्री. विजय जोशी हे संस्थेचे व्यवस्थापकीय संचालक असून, ‘अमृत’च्या राज्यभरातील लक्ष्यित गटापर्यंत संस्थेच्या योजना पोहोचवण्याचे कार्य त्यांच्या मार्गदर्शनाखाली मोठ्या प्रमाणात सुरू आहे.</p>
                </div>
                
                <p>त्यांच्याच संकल्पनेतून ‘अमृत महाराष्ट्र’ हे पोर्टल सुरू करण्यात आले आहे. ‘अमृत महाराष्ट्र’ हा प्रेरणेचा सुधाकुंभ ठरावा आणि त्यातील सकारात्मक विचारांचे अमृत नवी उमेद देणारे, प्रगतीची दिशा दाखवणारे ठरावे, हा उद्देश त्यामागे आहे.</p>
                
                <p>सामाजिक बांधिलकी म्हणून सुरू केलेल्या या उपक्रमातून ‘अमृत’ संस्थेशी निगडित घटना-घडामोडी-योजनांची माहिती मिळेलच; पण ग्रामीण, सामाजिक आणि सांस्कृतिक जीवनाचे सकारात्मक दर्शनही घडवले जाणार आहे.</p>
                
                <p>अध्यात्मापासून व्यावसायिकतेपर्यंतच्या विषयांमधील जे जे उत्तम, उदात्त, उन्नत असेल, त्याचे दर्शन घडवण्याचा प्रयत्न केला जाईल. समाजाला प्रेरणा देणे, आशेचे नवे किरण दाखवणे, युवा पिढीचे सामाजिक संघटन उभारून एक सामर्थ्यवान पिढी घडवणे हा यामागचा उद्देश आहे.</p>
            </div>
        </div>
    </div>
</div>

<?php 
include 'components/footer.php'; 
?>