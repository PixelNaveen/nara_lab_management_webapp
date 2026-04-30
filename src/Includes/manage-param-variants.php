 <div class="page-manage-variants">
   <div class="variants-container container-fluide">
     <!-- Filter + New -->
     <div class="variants-card-filter">
       <input type="text" placeholder="Search by Variant Name" class="form-control" style="max-width:250px;">
       <select class="form-select" id="filterParameter" style="max-width:150px;">
         <option value="">All Parameters</option>
       </select>
       <select class="form-select" style="max-width:120px;">
         <option value="">All Status</option>
         <option value="Active">Active</option>
         <option value="Inactive">Inactive</option>
       </select>
       <button class="btn btn-variants-filter">Filter</button>
       <div class="ms-auto">
         <button class="btn-variants-new">+ New Variant</button>
       </div>
     </div>

     <!-- Table -->
     <div class="variants-table-container">
       <table class="variants-table table table-hover align-middle">
         <thead>
           <tr>
             <th>Parameter Name</th>
             <th>Variant Name</th>
             <th>Full Variant Name</th>
             <th>Status</th>
             <th style="width:120px;">Actions</th>
           </tr>
         </thead>
         <tbody>
         </tbody>
       </table>
     </div>
   </div>

   <!-- Add/Edit Modal -->
   <div class="variants-modal-overlay" id="variantsModal">
     <div class="variants-modal-form">
       <div class="variants-modal-header">
         <h5 id="variantsModalTitle">New Variant</h5>
         <button class="btn-close-modal">&times;</button>
       </div>
       <form id="variantForm" autocomplete="off">
          <input type="hidden" id="variantCsrf" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="mb-3">
            <label class="variants-form-label">Parameter <span class="text-danger">*</span></label>
            <select class="form-select" id="variantParameter" required>
              <option value="">Select Parameter</option>
            </select>
            <div class="invalid-feedback" id="variantParameterError"></div>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Variant Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="variantName" placeholder="Enter variant name" required>
            <div class="invalid-feedback" id="variantNameError"></div>
          </div>
          <div class="mb-3">
            <label class="variants-form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="variantStatus" required>
              <option value="">Select Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <div class="invalid-feedback" id="variantStatusError"></div>
          </div>
          <div class="variants-modal-footer-btns">
           <button type="button" class="btn btn-secondary">Cancel</button>
           <button type="submit" class="btn btn-success">Save</button>
         </div>
       </form>
     </div>
   </div>

   <!-- Delete Confirmation Modal -->
   <div class="variants-modal-overlay" id="deleteConfirmModal">
     <div class="variants-modal-form">
       <div class="variants-modal-header">
         <h5>Confirm Delete</h5>
         <button class="btn-close-modal">&times;</button>
       </div>
       <div style="padding:24px;">
         <p>Are you sure you want to delete this variant?</p>
         <div class="variants-modal-footer-btns">
           <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
           <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
         </div>
       </div>
     </div>
   </div>

 </div>

  <!-- Load External JS -->
  <script src="public/assets/js/manage-param-variants.js"></script>
