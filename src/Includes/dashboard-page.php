<?php
// pages/dashboard.php
?>



<div class="page-transition">

    <!-- Big Wrapper Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <!-- Label at top -->
            <p class="quick-info-label mb-4">📊 Monthly Sample Overview</p>

            <!-- Inner 4 cards in a row -->
            <div class="row g-3 g-md-4">
                
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="#">
                        <div class="card card-stat border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1 small">Pending Samples</p>
                                        <h3 class="mb-0 fw-bold">12</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-box-seam text-warning" style="font-size: 1.5rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                         </a>
                    </div>
               


                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="#">
                    <div class="card card-stat border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">In Testing</p>
                                    <h3 class="mb-0 fw-bold">8</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-gear text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="#">
                    <div class="card card-stat border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Completed Today</p>
                                    <h3 class="mb-0 fw-bold">5</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="#">
                    <div class="card card-stat border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Rejected Samples</p>
                                    <h3 class="mb-0 fw-bold">3</h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>


    <!-- Recent Samples and Pending Tasks -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Recent Samples</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-3 small text-uppercase">Sample Code</th>
                                    <th class="px-3 py-3 small text-uppercase">Client</th>
                                    <th class="px-3 py-3 small text-uppercase">Status</th>
                                    <th class="px-3 py-3 small text-uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-3 py-3">
                                        <span class="text-primary fw-semibold">25/0008/01</span>
                                    </td>
                                    <td class="px-3 py-3">Ministry of Health</td>
                                    <td class="px-3 py-3">
                                        <span class="badge bg-warning text-dark badge-status">Submitted</span>
                                    </td>
                                    <td class="px-3 py-3 text-muted">2025-10-09</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-3">
                                        <span class="text-primary fw-semibold">25/0007/01</span>
                                    </td>
                                    <td class="px-3 py-3">ABC Water Company</td>
                                    <td class="px-3 py-3">
                                        <span class="badge bg-primary badge-status">Testing</span>
                                    </td>
                                    <td class="px-3 py-3 text-muted">2025-10-08</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-3">
                                        <span class="text-primary fw-semibold">25/0006/01</span>
                                    </td>
                                    <td class="px-3 py-3">XYZ Pharmaceuticals</td>
                                    <td class="px-3 py-3">
                                        <span class="badge bg-success badge-status">Completed</span>
                                    </td>
                                    <td class="px-3 py-3 text-muted">2025-10-07</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-3">
                                        <span class="text-primary fw-semibold">25/0005/01</span>
                                    </td>
                                    <td class="px-3 py-3">National Hospital</td>
                                    <td class="px-3 py-3">
                                        <span class="badge bg-danger badge-status">Overdue</span>
                                    </td>
                                    <td class="px-3 py-3 text-muted">2025-10-05</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top text-center py-3">
                    <a href="index.php?page=samples" class="text-primary text-decoration-none small fw-semibold">
                        View All Samples <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Pending Tasks</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div class="alert alert-custom alert-warning mb-0 d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-circle fs-5 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-semibold small">3 Overdue Tests</p>
                                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Require immediate attention</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-custom alert-info mb-0 d-flex align-items-start gap-2">
                            <i class="bi bi-clock fs-5 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-semibold small">5 Tests Due Today</p>
                                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Complete by end of day</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-custom alert-success mb-0 d-flex align-items-start gap-2">
                            <i class="bi bi-receipt fs-5 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-semibold small">8 Pending Payments</p>
                                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Awaiting acknowledgement</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 g-md-4">
        <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?page=sample-submission" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-primary text-white h-100 btn-action">
                    <div class="card-body p-4">
                        <i class="bi bi-file-text display-4 mb-3"></i>
                        <h5 class="card-title fw-bold mb-2">New Sample</h5>
                        <p class="card-text opacity-75 mb-0">Submit a new sample form</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?page=form-acceptance" class="text-decoration-none">
                <div class="card border-0 shadow-sm bg-success text-white h-100 btn-action">
                    <div class="card-body p-4">
                        <i class="bi bi-check-circle display-4 mb-3"></i>
                        <h5 class="card-title fw-bold mb-2">Accept Sample</h5>
                        <p class="card-text opacity-75 mb-0">Process sample acceptance</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4 col-lg-4">
            <a href="index.php?page=test-results" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-white h-100 btn-action" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4">
                        <i class="bi bi-file-earmark-check display-4 mb-3"></i>
                        <h5 class="card-title fw-bold mb-2">Enter Results</h5>
                        <p class="card-text opacity-75 mb-0">Record test results</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>