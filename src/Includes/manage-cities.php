<?php
// src/Includes/manage-cities.php - FINAL COMPLETE VERSION
?>

<link rel="stylesheet" href="../public/assets/css/manage-cities.css">

<div class="cities-container">

    <!-- Statistics Cards - ONLY 3 CARDS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Cities</p>
                    <h3 class="stat-value" id="totalCities">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Pre-defined</p>
                    <h3 class="stat-value" id="predefinedCities">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">User-added</p>
                    <h3 class="stat-value" id="userAddedCities">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="cities-card-filter cities-filters">
        <input type="text" class="form-control" id="searchInput"
            placeholder="Search cities..." style="max-width: 250px;">

        <select class="form-select" id="filterType" style="max-width: 180px;">
            <option value="all">All Cities</option>
            <option value="predefined">Pre-defined Only</option>
            <option value="user-added">User-added Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 150px;">
            <option value="usage">Sort by Usage</option>
            <option value="name">Sort by Name</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-cities-filter">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-cities-new" id="btnNewCity">
                <i class="fas fa-plus"></i> New City
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="cities-table-container">
        <table class="table cities-table">
            <thead>
                <tr>
                    <th style="width: 22%;">City Name</th>
                    <th style="width: 13%;">Usage Count</th>
                    <th style="width: 13%;">Status</th>
                    <th style="width: 18%;">Added Date</th>
                    <th style="width: 15%;">Added By</th>
                    <th style="width: 19%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="citiesTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="cities-modal-overlay" id="modalOverlay">
    <div class="cities-modal-form">
        <div class="cities-modal-header">
            <h5 id="formTitle">Add New City</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="cityForm">
            <input type="hidden" id="cityId">

            <div class="mb-3">
                <label class="cities-form-label">
                    City Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control cities-form-control"
                    id="cityName" name="cityName" required
                    placeholder="Enter city name (e.g., Colombo)">
                <small class="text-muted">Will be auto-formatted (e.g., "colombo" → "Colombo")</small>
            </div>

            <div class="cities-modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save City</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update City</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><span id="deleteCityName"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the city.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastContainer"></div>
</div>

<!-- CSRF Token for AJAX -->
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

<!-- Load External JS -->
<script src="public/assets/js/manage-cities.js"></script>