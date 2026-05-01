<?php
// src/Includes/manage-extra-items.php - UPDATED WITHOUT DISPLAY_ORDER
?>

<link rel="stylesheet" href="../public/assets/css/manage-extra-items.css">

<div class="items-container">
    
    <!-- Statistics Cards -->
    <!-- <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Total Items</p>
                    <h3 class="stat-value" id="totalItems">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Active</p>
                    <h3 class="stat-value" id="activeItems">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-secondary">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <p class="stat-label">Inactive</p>
                    <h3 class="stat-value" id="inactiveItems">0</h3>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Filter Card -->
    <div class="items-card-filter items-filters">
        <input type="text" class="form-control" id="searchInput" 
               placeholder="Search items..." style="max-width: 250px;">

        <select class="form-select" id="filterStatus" style="max-width: 150px;">
            <option value="">All Status</option>
            <option value="1">Active Only</option>
            <option value="0">Inactive Only</option>
        </select>

        <select class="form-select" id="sortBy" style="max-width: 150px;">
            <option value="name">Sort by Name</option>
            <option value="price">Sort by Price</option>
            <option value="date">Sort by Date</option>
        </select>

        <button id="btnFilter" class="btn btn-items-filter">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="ms-auto">
            <button class="btn btn-items-new" id="btnNewItem">
                <i class="fas fa-plus"></i> New Item
            </button>
        </div>
    </div>

    <!-- Table - REMOVED ORDER COLUMN -->
    <div class="items-table-container">
        <table class="table items-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Item Name</th>
                    <th style="width: 15%;">Value</th>
                    <th style="width: 13%;">Price (Rs.)</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal - REMOVED DISPLAY ORDER FIELD -->
<div class="items-modal-overlay" id="modalOverlay">
    <div class="items-modal-form">
        <div class="items-modal-header">
            <h5 id="formTitle">Add New Item</h5>
            <button class="btn-close-modal" id="btnCloseModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="itemForm">
            <input type="hidden" id="itemId">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="items-form-label">
                        Item Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control items-form-control" 
                           id="itemName" name="itemName" required 
                           placeholder="e.g., Water Bottle">
                    <div class="invalid-feedback" id="itemNameError"></div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="items-form-label">
                        Value <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control items-form-control" 
                           id="itemValue" name="itemValue" required 
                           step="0.01" min="0.01" placeholder="500">
                    <div class="invalid-feedback" id="itemValueError"></div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="items-form-label">
                        Unit <span class="text-danger">*</span>
                    </label>
                    <select class="form-select items-form-select" 
                            id="itemUnit" name="itemUnit" required>
                        <option value="">Select</option>
                        <option value="mL">mL</option>
                        <option value="L">L</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="Piece">Piece</option>
                    </select>
                    <div class="invalid-feedback" id="itemUnitError"></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="items-form-label">
                    Price (Rs.) <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control items-form-control" 
                       id="itemPrice" name="itemPrice" required 
                       step="0.01" min="0.01" placeholder="50.00">
                <div class="invalid-feedback" id="itemPriceError"></div>
            </div>

            <div class="mb-3">
                <label class="items-form-label">Description</label>
                <textarea class="form-control items-form-control" 
                          id="itemDescription" name="itemDescription" 
                          rows="3" placeholder="Optional description"></textarea>
            </div>

            <div class="items-modal-footer-btns">
                <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                <button type="submit" class="btn btn-success" id="btnSave">Save Item</button>
                <button type="button" class="btn btn-warning d-none" id="btnUpdate">Update Item</button>
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
                <p>Are you sure you want to delete <strong><span id="deleteItemName"></span></strong>?</p>
                <p class="text-muted mb-0">This action can be undone by re-adding the item.</p>
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
<script src="public/assets/js/manage-extra-items.js"></script>