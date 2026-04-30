<?php

/**
 * Forms Carousel Modal - PROFESSIONAL VERSION 5.0
 * 
 * CHANGES:
 * - Removed 2-up printing (always 1 form = 1 page)
 * - Removed form selection checkboxes
 * - Removed download button
 * - Professional blue theme (manage-param.css colors)
 * - Smaller carousel buttons
 * - Modern clean UI
 * - Simplified print (all forms, user selects in print dialog)
 * 
 * @version 5.0 - PROFESSIONAL REDESIGN
 */

if (!isset($safData) || !isset($ackData) || !isset($analystData)) {
    echo '<h3>Error: Form data not loaded</h3>';
    exit;
}

$viewType = $_GET['type'] ?? 'carousel';

$isSafActive = ($viewType === 'carousel' || $viewType === 'saf') ? 'active' : '';
$isSacfActive = ($viewType === 'sacf') ? 'active' : '';
$isAifActive = ($viewType === 'aif') ? 'active' : '';

// Hide carousel controls if we are only viewing a single specific form
$showControls = ($viewType === 'carousel');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Forms </title>
    <!-- - <?= $sampleCode ?> -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ===================================
           CSS VARIABLES (manage-param.css)
           =================================== */
        :root {
            --primary-blue: #1e3a8a;
            --primary-blue-dark: #1e40af;
            --primary-blue-light: #3b82f6;
            --success-green: #16a34a;
            --light-gray: #f8f9fa;
            --border-color: #e2e8f0;
            --text-dark: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            background: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffffff;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* ===================================
           MODAL CONTAINER
           =================================== */
        .modal-container {
            width: 100%;
            height: 100vh;
            margin: 0 auto;
            background: white;
            overflow: visible;
            display: flex;
            flex-direction: column;
            border-radius: 8px 8px 0 0;
        }

        /* ===================================
           HEADER (Professional Blue)
           =================================== */
        .modal-header-custom {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2);
            flex-shrink: 0;
            border-radius: 8px 8px 0 0;
        }

        .modal-header-custom h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-print-all {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print-all:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-1px);
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 6px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .btn-close-modal:hover {
            background: white;
            color: var(--primary-blue);
            border-color: white;
            transform: translateY(-1px);
        }

        /* ===================================
           CAROUSEL CONTAINER
           =================================== */
        .carousel-container {
            flex: 1;
            padding: 40px 20px;
            background: #ffffff;
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
            padding: 20px 80px;
            min-height: 850px;
        }

        /* ===================================
           FORM CONTAINERS
           =================================== */
        .carousel-item .form-container {
            width: 210mm;
            margin: 0 auto;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            background: white;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            height: auto !important;
            min-height: auto !important;
        }

        /* ===================================
           CAROUSEL CONTROLS (Smaller Blue)
           =================================== */
        .carousel-control-prev,
        .carousel-control-next {
            width: 45px;
            /* ✅ Reduced from 70px */
            height: 45px;
            /* ✅ Reduced from 70px */
            background: var(--primary-blue) !important;
            border-radius: 50%;
            opacity: 0.9 !important;
            transition: all 0.2s ease-in-out;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.25);
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: var(--primary-blue-dark) !important;
            opacity: 1 !important;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.35);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 22px;
            /* ✅ Reduced from 35px */
            height: 22px;
            /* ✅ Reduced from 35px */
        }

        /* ===================================
           CAROUSEL INDICATORS (Blue)
           =================================== */
        .carousel-indicators {
            bottom: 20px;
            margin-bottom: 0;
            z-index: 15;

        }

        .carousel-indicators [data-bs-target] {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #cbd5e1;
            border: none;
            margin: 0 5px;
            transition: all 0.3s ease-in-out;
            opacity: 0.6;
        }

        .carousel-indicators [data-bs-target]:hover {
            opacity: 0.9;
            transform: scale(1.2);
        }

        .carousel-indicators .active {
            width: 32px;
            border-radius: 5px;
            background: var(--primary-blue);
            opacity: 1;
        }

        /* ===================================
           RESPONSIVE DESIGN
           =================================== */
        @media (max-width: 768px) {
            .carousel-item {
                padding: 20px 10px;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 40px;
                height: 40px;
            }

            .carousel-control-prev {
                left: 10px;
            }

            .carousel-control-next {
                right: 10px;
            }

            .modal-header-custom {
                padding: 15px 20px;
            }

            .modal-header-custom h2 {
                font-size: 1rem;
            }

            .header-actions {
                gap: 8px;
            }

            .btn-print-all,
            .btn-close-modal {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }

        /* ===================================
           PRINT STYLES
           =================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
            }

            /* Hide UI elements */
            .modal-header-custom,
            .carousel-control-prev,
            .carousel-control-next,
            .carousel-indicators {
                display: none !important;
                visibility: hidden !important;
            }

            .modal-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: none !important;
                width: 100% !important;
                background: white !important;
                height: auto !important;
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

            /* Each form is isolated for print via JS */
            .carousel-item {
                page-break-after: auto !important;
                page-break-inside: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                max-height: none !important;
                height: auto !important;
                position: relative !important;
                overflow: visible !important;
            }

            /* Remove page break from last item */
            .carousel-item:last-of-type {
                page-break-after: auto !important;
            }

            /* Form containers fit strictly to content */
            .carousel-item .form-container {
                width: 210mm !important;
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                margin: 0 auto !important;
                padding: 6mm !important;
                box-shadow: none !important;
                border: none !important;
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
            <h2>📋 Sample Forms</h2>
            <!-- - Sample ID: <?= $sampleCode ?> -->
            <div class="header-actions">
                <button class="btn-print-all" onclick="printActiveSlide()">
                    🖨️ Print
                </button>
                <button class="btn-close-modal" onclick="window.close()">
                    ✕ Close
                </button>
            </div>
        </div>

        <!-- Carousel -->
        <div class="carousel-container">
            <div id="formsCarousel" class="carousel slide" data-bs-ride="false">
                <?php if ($showControls): ?>
                    <!-- Indicators -->
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="SAF"></button>
                        <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="1" aria-label="SAcF"></button>
                        <button type="button" data-bs-target="#formsCarousel" data-bs-slide-to="2" aria-label="AIF"></button>
                    </div>
                <?php endif; ?>

                <!-- Slides -->
                <div class="carousel-inner">
                    <!-- Slide 1: SAF -->
                    <div class="carousel-item <?= $isSafActive ?>" id="slide-saf">
                        <?php
                        $data = $safData;
                        include __DIR__ . '/saf-template.php';
                        ?>
                    </div>

                    <!-- Slide 2: Sample Acknowledgement Form -->
                    <div class="carousel-item <?= $isSacfActive ?>" id="slide-sacf">
                        <?php
                        $data = $ackData;
                        include __DIR__ . '/acknowledgement-template.php';
                        ?>
                    </div>

                    <!-- Slide 3: Analyst Information Form -->
                    <div class="carousel-item <?= $isAifActive ?>" id="slide-aif">
                        <?php
                        $data = $analystData;
                        include __DIR__ . '/analyst-template.php';
                        ?>
                    </div>
                </div>

                <?php if ($showControls): ?>
                    <!-- Controls -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#formsCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#formsCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/assets/js/forms-carousel.js"></script>
</body>

</html>