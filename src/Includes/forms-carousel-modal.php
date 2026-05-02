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
$sampleCode = $safData['sample_id'] ?? 'N/A';

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
    <!-- Sample ID: <?= $sampleCode ?> -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/forms-carousel-modal.css">
</head>

<body>
    <div class="modal-container">
        <!-- Header -->
        <div class="modal-header-custom">
            <h2>📋 Sample Forms</h2>
            <span class="ms-2 badge bg-light text-primary"><?= $sampleCode ?></span>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/assets/js/forms-carousel.js"></script>
</body>

</html>