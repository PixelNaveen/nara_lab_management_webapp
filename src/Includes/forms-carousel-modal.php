<?php
/**
 * Forms Carousel Modal - COMPLETELY FIXED VERSION 4.0
 * 
 * FIXES:
 * - Proper @page size for print
 * - Each form prints as 1 page
 * - Correct page breaks
 * - No stuck carousel after print
 * - A4 sizing enforced
 * 
 * @version 4.0 - ALL ISSUES FIXED
 */

if (!isset($safData) || !isset($ackData) || !isset($analystData)) {
    echo '<h3>Error: Form data not loaded</h3>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Forms - <?= $sampleId ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 15px;
            overflow-y: auto;
        }
        
        /* Modal Container */
        .modal-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: visible;
        }
        
        /* Header */
        .modal-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-header-custom h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .btn-close-modal {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-close-modal:hover {
            background: white;
            color: #667eea;
        }
        
        /* Form Selection */
        .form-selection {
            display: flex;
            gap: 15px;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .form-selection label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 12px 24px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 15px;
        }
        
        .form-selection label:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        
        .form-selection input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        /* Carousel Container */
        .carousel-container {
            padding: 40px 20px;
            background: #ffffff;
            min-height: 900px;
            max-height: 1200px;
            position: relative;
            display: flex;
            align-items: flex-start;
            overflow-y: auto;
        }
        
        .carousel {
            width: 100%;
        }
        
        .carousel-inner {
            width: 100%;
        }
        
        .carousel-item {
            padding: 20px 60px;
            min-height: 850px;
        }
        
        /* Form containers */
        .carousel-item .form-container {
            width: 210mm;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            background: white;
            height: auto !important;
            min-height: auto !important;
        }
        
        /* Carousel Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-radius: 50%;
            opacity: 1 !important;
            transition: all 0.3s;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .carousel-control-prev {
            left: 15px;
        }
        
        .carousel-control-next {
            right: 15px;
        }
        
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 35px;
            height: 35px;
        }
        
        /* Indicators */
        .carousel-indicators {
            bottom: 20px;
            margin-bottom: 0;
            z-index: 15;
        }
        
        .carousel-indicators [data-bs-target] {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #ccc;
            border: none;
            margin: 0 8px;
            transition: all 0.4s;
            opacity: 1;
        }
        
        .carousel-indicators .active {
            width: 50px;
            border-radius: 7px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            padding: 30px;
            background: #f8f9fa;
            border-top: 2px solid #e0e0e0;
            border-radius: 0 0 16px 16px;
        }
        
        .action-buttons button {
            padding: 16px 40px;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 250px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-download {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .carousel-item {
                padding: 20px 10px;
            }
            
            .form-selection {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons button {
                width: 100%;
                min-width: auto;
            }
            
            .carousel-control-prev {
                left: 5px;
            }
            
            .carousel-control-next {
                right: 5px;
            }
        }
        
        /* ====================================
           CRITICAL PRINT STYLES - ALL FIXES
           ==================================== */
        @media print {
            /* Define A4 page size */
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            /* Hide all UI elements */
            .modal-header-custom,
            .form-selection,
            .carousel-control-prev,
            .carousel-control-next,
            .carousel-indicators,
            .action-buttons {
                display: none !important;
                visibility: hidden !important;
            }
            
            .modal-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: none !important;
                width: 100% !important;
                background: white !important;
            }
            
            .carousel-container {
                padding: 0 !important;
                min-height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                background: white !important;
            }
            
            .carousel,
            .carousel-inner {
                overflow: visible !important;
                width: 100% !important;
            }
            
            /* CRITICAL: Each carousel-item = 1 page */
            .carousel-item {
                display: block !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 210mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                height: 297mm !important;
                position: relative !important;
                overflow: hidden !important;
            }
            
            /* Remove page break from last item */
            .carousel-item:last-of-type {
                page-break-after: auto !important;
            }
            
            /* Ensure form containers fit A4 perfectly */
            .carousel-item .form-container {
                width: 210mm !important;
                height: auto !important;
                min-height: auto !important;
                max-height: 277mm !important;
                margin: 0 !important;
                padding: 6mm !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                background: white !important;
            }
            
            /* Prevent content overflow */
            .form-container * {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="modal-container">
        <!-- Header -->
        <div class="modal-header-custom">
            <h2>📋 Sample Forms - Sample ID: <?= $sampleId ?></h2>
            <button class="btn-close-modal" onclick="window.close()">✕ Close</button>
        </div>
        
        <!-- Form Selection Checkboxes -->
        <div class="form-selection">
            <label>
                <input type="checkbox" id="selectSAF" checked>
                <span>📄 Sample Acceptance Form</span>
            </label>
            <label>
                <input type="checkbox" id="selectSAcF" checked>
                <span>📋 Sample Acknowledgement Form</span>
            </label>
            <label>
                <input type="checkbox" id="selectAIF" checked>
                <span>🔬 Analyst Information Form</span>
            </label>
        </div>
        
        <!-- Bootstrap Carousel -->
        <div class="carousel-container">
            <div id="formsCarousel" class="carousel slide" data-bs-ride="false">
                <!-- Indicators -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                    <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="2"></button>
                </div>
                
                <!-- Slides -->
                <div class="carousel-inner">
                    <!-- Slide 1: SAF -->
                    <div class="carousel-item active" id="slide-saf">
                        <?php 
                        $data = $safData;
                        include __DIR__ . '/saf-template.php'; 
                        ?>
                    </div>
                    
                    <!-- Slide 2: Sample Acknowledgement Form -->
                    <div class="carousel-item" id="slide-sacf">
                        <?php 
                        $data = $ackData;
                        include __DIR__ . '/acknowledgement-template.php'; 
                        ?>
                    </div>
                    
                    <!-- Slide 3: Analyst Information Form -->
                    <div class="carousel-item" id="slide-aif">
                        <?php 
                        $data = $analystData;
                        include __DIR__ . '/analyst-template.php'; 
                        ?>
                    </div>
                </div>
                
                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#formsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#formsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-print" onclick="printSelected()">
                🖨️ Print Selected Forms
            </button>
            <button class="btn-download" onclick="downloadSelectedPDF(<?= $sampleId ?>)">
                📥 Download Selected as PDF
            </button>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Forms Carousel JS -->
    <script src="/public/assets/js/forms-carousel.js"></script>
</body>
</html>