/**
 * Sample Submission JavaScript
 * Handles all frontend logic for 6-step wizard
 * 
 * @version 1.0
 */

// Global State
let currentStep = 1;
let sampleCount = 0;
let submissionType = '';
let selectedClient = null;
let availableParameters = [];

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    initializeDateRestrictions();
    initializeEventListeners();
    showStep(1);
});

// Set date restrictions
function initializeDateRestrictions() {
    const today = new Date().toISOString().split('T')[0];
    const fiveDaysAgo = new Date();
    fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);
    const minDate = fiveDaysAgo.toISOString().split('T')[0];
    
    document.getElementById('receivedDate').min = minDate;
    document.getElementById('receivedDate').max = today;
    document.getElementById('tentativeDate').min = today;
}

// Initialize all event listeners
function initializeEventListeners() {
    // Client search
    document.getElementById('clientSearch').addEventListener('input', debounce(handleClientSearch, 300));
    
    // Submission type selection
    document.querySelectorAll('.type-card').forEach(card => {
        card.addEventListener('click', selectSubmissionType);
    });
    
    // Payment status selection
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', selectPaymentStatus);
    });
    
    // Payment reference validation
    const paymentRef = document.getElementById('paymentReference');
    if (paymentRef) {
        paymentRef.addEventListener('blur', validatePaymentReferenceField);
    }
    
    // Add sample button
    document.getElementById('addSampleBtn').addEventListener('click', addSample);
    
    // Navigation buttons
    document.getElementById('nextBtn').addEventListener('click', handleNext);
    document.getElementById('prevBtn').addEventListener('click', handlePrev);
    document.getElementById('siForm').addEventListener('submit', handleSubmit);
}

// ==========================================
// STEP NAVIGATION
// ==========================================

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
    
    // Update progress indicator
    document.querySelectorAll('.progress-step').forEach((s, idx) => {
        s.classList.toggle('active', idx + 1 === step);
        s.classList.toggle('completed', idx + 1 < step);
    });
    
    // Update progress bar
    document.getElementById('progressBar').style.width = `${(step / 6) * 100}%`;
    
    // Show/hide navigation buttons
    document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = step === 6 ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = step === 6 ? 'inline-block' : 'none';
    
    currentStep = step;
    
    // Step-specific actions
    if (step === 4) initSamples();
    if (step === 5) loadTests();
    if (step === 6) showReview();
}

async function handleNext() {
    const isValid = await validateStep(currentStep);
    
    if (!isValid) {
        return;
    }
    
    // Auto-update client if modified (Step 1)
    if (currentStep === 1 && selectedClient) {
        await updateClientIfModified();
    }
    
    showStep(currentStep + 1);
}

function handlePrev() {
    showStep(currentStep - 1);
}

// ==========================================
// STEP VALIDATION
// ==========================================

async function validateStep(step) {
    const stepEl = document.querySelector(`.step[data-step="${step}"]`);
    const required = stepEl.querySelectorAll('[required]');
    
    for (let input of required) {
        if (input.type === 'radio') {
            const radioGroup = document.querySelector(`[name="${input.name}"]:checked`);
            if (!radioGroup) {
                showToast('Please select ' + input.name.replace('_', ' '), 'warning');
                return false;
            }
        } else if (!input.value.trim()) {
            showToast('Please fill in all required fields', 'warning');
            input.focus();
            return false;
        }
    }
    
    // Step-specific validations
    if (step === 3) {
        // Validate payment reference if paid
        const paymentStatus = document.querySelector('[name="payment_status"]:checked');
        if (paymentStatus && paymentStatus.value === 'Paid') {
            const paymentRef = document.getElementById('paymentReference');
            if (!paymentRef.value.trim()) {
                showToast('Payment reference is required when payment status is Paid', 'error');
                return false;
            }
            
            // Validate uniqueness
            const isValid = await validatePaymentReferenceAjax(paymentRef.value);
            if (!isValid) {
                return false;
            }
        }
    }
    
    if (step === 4 && sampleCount === 0) {
        showToast('Please add at least one sample', 'warning');
        return false;
    }
    
    if (step === 5) {
        const checked = document.querySelectorAll('input[name^="test_"]:checked');
        if (checked.length === 0) {
            showToast('Please select at least one test', 'warning');
            return false;
        }
    }
    
    return true;
}

// ==========================================
// CLIENT SEARCH & MANAGEMENT
// ==========================================

async function handleClientSearch() {
    const query = document.getElementById('clientSearch').value.trim();
    const resultsDiv = document.getElementById('clientResults');
    
    if (query.length < 2) {
        resultsDiv.innerHTML = '';
        resultsDiv.style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch(`src/Controllers/sample-controller.php?action=searchClients&query=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.clients.length > 0) {
            resultsDiv.innerHTML = data.clients.map(client => `
                <div class="client-item" onclick="selectClient(${client.client_id})">
                    <strong>${client.client_name}</strong><br>
                    <small class="text-muted">${client.city || 'N/A'} • ${client.phone_primary}</small>
                </div>
            `).join('');
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.innerHTML = '<div class="client-item">No clients found</div>';
            resultsDiv.style.display = 'block';
        }
    } catch (error) {
        console.error('Client search error:', error);
        showToast('Error searching clients', 'error');
    }
}

async function selectClient(clientId) {
    try {
        const response = await fetch(`src/Controllers/sample-controller.php?action=searchClients&query=${clientId}`);
        const data = await response.json();
        
        if (data.success && data.clients.length > 0) {
            const client = data.clients[0];
            selectedClient = client;
            
            // Fill form fields
            document.getElementById('selectedClientId').value = client.client_id;
            document.getElementById('clientName').value = client.client_name;
            document.getElementById('phoneInput').value = client.phone_primary;
            document.getElementById('addressInput').value = client.address_line1 || '';
            document.getElementById('cityInput').value = client.city || '';
            document.getElementById('contactPersonInput').value = client.contact_person || '';
            document.getElementById('emailInput').value = client.email || '';
            document.getElementById('mobileInput').value = client.mobile || '';
            
            // Store original values for comparison
            document.getElementById('originalClientName').value = client.client_name;
            document.getElementById('originalPhone').value = client.phone_primary;
            document.getElementById('originalEmail').value = client.email || '';
            document.getElementById('originalMobile').value = client.mobile || '';
            document.getElementById('originalContactPerson').value = client.contact_person || '';
            
            document.getElementById('clientResults').style.display = 'none';
            showToast('Client selected successfully', 'success');
        }
    } catch (error) {
        console.error('Select client error:', error);
        showToast('Error selecting client', 'error');
    }
}

async function updateClientIfModified() {
    if (!selectedClient) return;
    
    const currentName = document.getElementById('clientName').value;
    const currentPhone = document.getElementById('phoneInput').value;
    const currentEmail = document.getElementById('emailInput').value;
    const currentMobile = document.getElementById('mobileInput').value;
    const currentContact = document.getElementById('contactPersonInput').value;
    
    const originalName = document.getElementById('originalClientName').value;
    const originalPhone = document.getElementById('originalPhone').value;
    const originalEmail = document.getElementById('originalEmail').value;
    const originalMobile = document.getElementById('originalMobile').value;
    const originalContact = document.getElementById('originalContactPerson').value;
    
    const isModified = currentName !== originalName || 
                      currentPhone !== originalPhone || 
                      currentEmail !== originalEmail ||
                      currentMobile !== originalMobile ||
                      currentContact !== originalContact;
    
    if (isModified) {
        try {
            const formData = new FormData();
            formData.append('action', 'updateClient');
            formData.append('client_id', selectedClient.client_id);
            formData.append('client_name', currentName);
            formData.append('phone_primary', currentPhone);
            formData.append('address_line1', document.getElementById('addressInput').value);
            formData.append('city', document.getElementById('cityInput').value);
            formData.append('contact_person', currentContact);
            formData.append('email', currentEmail);
            formData.append('mobile', currentMobile);
            
            const response = await fetch('src/Controllers/sample-controller.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (!result.success) {
                showToast('Failed to update client: ' + result.message, 'error');
                throw new Error('Client update failed');
            }
            
            showToast('Client information updated', 'success');
        } catch (error) {
            console.error('Update client error:', error);
            showToast('Error updating client. Please try again.', 'error');
            throw error;
        }
    }
}

// ==========================================
// SUBMISSION TYPE
// ==========================================

function selectSubmissionType(event) {
    const card = event.currentTarget;
    document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    card.querySelector('input[type="radio"]').checked = true;
    submissionType = card.querySelector('input[type="radio"]').value;
}

// ==========================================
// PAYMENT MANAGEMENT
// ==========================================

function selectPaymentStatus(event) {
    const option = event.currentTarget;
    const status = option.dataset.status;
    
    document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
    option.classList.add('selected');
    option.querySelector('input[type="radio"]').checked = true;
    
    const refField = document.getElementById('paymentReferenceField');
    const refInput = document.getElementById('paymentReference');
    
    if (status === 'paid') {
        refField.style.display = 'block';
        refInput.required = true;
    } else {
        refField.style.display = 'none';
        refInput.required = false;
        refInput.value = '';
    }
}

async function validatePaymentReferenceField() {
    const input = document.getElementById('paymentReference');
    const validationDiv = document.getElementById('paymentRefValidation');
    const reference = input.value.trim();
    
    if (!reference) return true;
    
    return await validatePaymentReferenceAjax(reference);
}

async function validatePaymentReferenceAjax(reference) {
    const validationDiv = document.getElementById('paymentRefValidation');
    
    try {
        const response = await fetch(`src/Controllers/sample-controller.php?action=validatePaymentRef&reference=${encodeURIComponent(reference)}`);
        const result = await response.json();
        
        if (result.valid) {
            validationDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Valid reference</small>';
            return true;
        } else {
            validationDiv.innerHTML = `<small class="text-danger"><i class="fas fa-times-circle"></i> ${result.message}</small>`;
            showToast(result.message, 'error');
            return false;
        }
    } catch (error) {
        console.error('Payment reference validation error:', error);
        return false;
    }
}

// ==========================================
// SAMPLE MANAGEMENT
// ==========================================

function initSamples() {
    if (sampleCount === 0) addSample();
}

function addSample() {
    sampleCount++;
    const container = document.getElementById('samplesContainer');
    
    const card = document.createElement('div');
    card.className = 'sample-card';
    card.dataset.sampleId = sampleCount;
    card.innerHTML = `
        <div class="sample-card-header">
            <h4 class="mb-0">Sample ${sampleCount}</h4>
            ${sampleCount > 1 ? `<button type="button" class="remove-sample-btn" onclick="removeSample(${sampleCount})">
                <i class="fas fa-trash"></i> Remove
            </button>` : ''}
        </div>

        <input type="hidden" name="samples[${sampleCount}][sequence]" value="${sampleCount}">

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold">Sample Name *</label>
                <div class="position-relative">
                    <input type="text" class="form-control sample-name-input" 
                           name="samples[${sampleCount}][sample_name]" 
                           data-sample-id="${sampleCount}"
                           placeholder="Start typing..." 
                           autocomplete="off" required>
                    <div class="sample-name-autocomplete" id="autocomplete-${sampleCount}"></div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Measurement Value *</label>
                <input type="number" class="form-control" name="samples[${sampleCount}][value]" 
                       placeholder="e.g., 100, 250" step="0.01" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Unit *</label>
                <select class="form-select" name="samples[${sampleCount}][unit]" required>
                    <option value="">Select Unit</option>
                    <optgroup label="Weight">
                        <option value="g">g (gram)</option>
                        <option value="kg">kg (kilogram)</option>
                        <option value="mg">mg (milligram)</option>
                    </optgroup>
                    <optgroup label="Volume">
                        <option value="ml">ml (milliliter)</option>
                        <option value="L">L (liter)</option>
                    </optgroup>
                    <optgroup label="Area">
                        <option value="cm²">cm² (square centimeter)</option>
                        <option value="m²">m² (square meter)</option>
                    </optgroup>
                    ${submissionType === 'swab' ? '<optgroup label="Swab"><option value="swab">swab</option></optgroup>' : ''}
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Container Damage *</label>
                <select class="form-select" name="samples[${sampleCount}][container_damage]" required>
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Temperature Condition *</label>
                <select class="form-select" name="samples[${sampleCount}][temperature_condition]" required>
                    <option value="Ambient">Ambient</option>
                    <option value="Chilled">Chilled</option>
                    <option value="Frozen">Frozen</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Validity Status *</label>
                <select class="form-select" name="samples[${sampleCount}][validity_status]" required>
                    <option value="OK">OK</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Client Sample Code (Optional)</label>
                <input type="text" class="form-control" name="samples[${sampleCount}][client_sample_code]">
            </div>

            <div class="col-12">
                <label class="form-label">Sampling Location (Optional)</label>
                <input type="text" class="form-control" name="samples[${sampleCount}][sampling_location]" 
                       placeholder="e.g., Kitchen Area, Counter Top">
            </div>

            <div class="col-12">
                <label class="form-label">Reason for Analysis (Optional)</label>
                <textarea class="form-control" name="samples[${sampleCount}][reason_for_analysis]" rows="2" 
                          placeholder="Why is this sample being tested?"></textarea>
            </div>
        </div>
    `;
    
    container.appendChild(card);
    
    // Initialize autocomplete for this sample
    initSampleNameAutocomplete(sampleCount);
}

function removeSample(sampleId) {
    const card = document.querySelector(`.sample-card[data-sample-id="${sampleId}"]`);
    if (card && sampleCount > 1) {
        card.remove();
        renumberSamples();
    }
}

function renumberSamples() {
    const cards = document.querySelectorAll('.sample-card');
    sampleCount = 0;
    
    cards.forEach((card, index) => {
        sampleCount++;
        card.dataset.sampleId = sampleCount;
        card.querySelector('h4').textContent = `Sample ${sampleCount}`;
        
        card.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${sampleCount}]`);
            }
            if (input.dataset.sampleId) {
                input.dataset.sampleId = sampleCount;
            }
        });
        
        card.querySelector('[name^="samples"][name$="[sequence]"]').value = sampleCount;
        
        const removeBtn = card.querySelector('.remove-sample-btn');
        if (removeBtn) {
            removeBtn.onclick = () => removeSample(sampleCount);
        }
    });
}

// Sample name autocomplete
function initSampleNameAutocomplete(sampleId) {
    const input = document.querySelector(`[data-sample-id="${sampleId}"].sample-name-input`);
    const autocompleteDiv = document.getElementById(`autocomplete-${sampleId}`);
    
    input.addEventListener('input', debounce(async function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            autocompleteDiv.innerHTML = '';
            autocompleteDiv.classList.remove('show');
            return;
        }
        
        try {
            const response = await fetch(`src/Controllers/sample-controller.php?action=searchSampleNames&q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.results.length > 0) {
                autocompleteDiv.innerHTML = data.results.map(item => `
                    <div class="autocomplete-item" onclick="selectSampleName('${sampleId}', '${item.sample_name.replace(/'/g, "\\'")}')">
                        ${item.sample_name}
                        <span class="autocomplete-usage">(used ${item.usage_count}x)</span>
                    </div>
                `).join('');
                autocompleteDiv.classList.add('show');
            } else {
                autocompleteDiv.innerHTML = '';
                autocompleteDiv.classList.remove('show');
            }
        } catch (error) {
            console.error('Autocomplete error:', error);
        }
    }, 300));
    
    // Close autocomplete on outside click
    document.addEventListener('click', function(e) {
        if (!autocompleteDiv.contains(e.target) && e.target !== input) {
            autocompleteDiv.classList.remove('show');
        }
    });
}

function selectSampleName(sampleId, name) {
    const input = document.querySelector(`[data-sample-id="${sampleId}"].sample-name-input`);
    input.value = name;
    document.getElementById(`autocomplete-${sampleId}`).classList.remove('show');
}

// ==========================================
// TEST SELECTION & PRICING
// ==========================================

async function loadTests() {
    const container = document.getElementById('testsContainer');
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    try {
        const response = await fetch(`src/Controllers/sample-controller.php?action=getParameters&type=${submissionType}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error('Failed to load parameters');
        }
        
        availableParameters = data.parameters;
        container.innerHTML = '';
        
        for (let i = 1; i <= sampleCount; i++) {
            const sampleName = document.querySelector(`[name="samples[${i}][sample_name]"]`).value;
            
            const sampleSection = document.createElement('div');
            sampleSection.className = 'param-section';
            sampleSection.innerHTML = `
                <h4 class="param-category">
                    <i class="fas fa-vial"></i> Sample ${i}: ${sampleName}
                </h4>
            `;
            
            availableParameters.forEach(param => {
                const paramDiv = document.createElement('div');
                paramDiv.className = 'param-item';
                
                const price = submissionType === 'swab' && param.swab_price 
                    ? parseFloat(param.price) + parseFloat(param.swab_price)
                    : parseFloat(param.price);
                
                paramDiv.innerHTML = `
                    <div class="param-checkbox">
                        <input type="checkbox" 
                               name="test_${i}_${param.parameter_id}" 
                               id="test_${i}_${param.parameter_id}"
                               data-sample="${i}"
                               data-param="${param.parameter_id}"
                               data-price="${price}">
                        <label for="test_${i}_${param.parameter_id}" class="param-label">
                            ${param.parameter_code} – ${param.parameter_name}
                            ${submissionType === 'swab' && param.swab_enabled ? '<span class="swab-badge">SWAB</span>' : ''}
                        </label>
                        <span class="param-price">Rs. ${price.toFixed(2)}</span>
                    </div>
                `;
                
                sampleSection.appendChild(paramDiv);
            });
            
            container.appendChild(sampleSection);
        }
    } catch (error) {
        console.error('Load tests error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error loading tests. Please try again.</div>';
    }
}

// ==========================================
// REVIEW SUMMARY
// ==========================================

function showReview() {
    const container = document.getElementById('reviewSummary');
    const clientName = document.getElementById('clientName').value;
    const receivedDate = document.getElementById('receivedDate').value;
    const additionalCharges = parseFloat(document.querySelector('[name="additional_charges"]').value) || 0;
    
    let reviewHTML = `
        <div class="review-card">
            <h4><i class="fas fa-user"></i> Client Information</h4>
            <div class="review-item">
                <span><strong>Client Name:</strong></span>
                <span>${clientName}</span>
            </div>
            <div class="review-item">
                <span><strong>Submission Type:</strong></span>
                <span class="badge bg-primary">${submissionType.toUpperCase()}</span>
            </div>
            <div class="review-item">
                <span><strong>Received Date:</strong></span>
                <span>${formatDate(receivedDate)}</span>
            </div>
        </div>
    `;
    
    let grandTotal = 0;
    
    for (let i = 1; i <= sampleCount; i++) {
        const sampleName = document.querySelector(`[name="samples[${i}][sample_name]"]`).value;
        const value = document.querySelector(`[name="samples[${i}][value]"]`).value;
        const unit = document.querySelector(`[name="samples[${i}][unit]"]`).value;
        
        const selectedTests = document.querySelectorAll(`input[data-sample="${i}"]:checked`);
        let sampleTotal = 0;
        let testsHTML = '';
        
        selectedTests.forEach(test => {
            const price = parseFloat(test.dataset.price);
            sampleTotal += price;
            
            const param = availableParameters.find(p => p.parameter_id == test.dataset.param);
            const testName = param ? `${param.parameter_code} – ${param.parameter_name}` : 'Test';
            
            testsHTML += `
                <div class="review-item">
                    <span>${testName}</span>
                    <span class="text-success">Rs. ${price.toFixed(2)}</span>
                </div>
            `;
        });
        
        grandTotal += sampleTotal;
        
        reviewHTML += `
            <div class="review-card">
                <h5><i class="fas fa-flask"></i> ${sampleName}</h5>
                <div class="review-item">
                    <span><strong>Measurement:</strong></span>
                    <span>${value} ${unit}</span>
                </div>
                <h6 class="mt-3 mb-2"><i class="fas fa-list-check"></i> Selected Tests:</h6>
                ${testsHTML}
                <div class="review-item" style="background: #f8f9fa; padding: 0.75rem; border-radius: 4px; margin-top: 0.5rem;">
                    <span><strong>Sample Subtotal:</strong></span>
                    <span class="fw-bold text-primary">Rs. ${sampleTotal.toFixed(2)}</span>
                </div>
            </div>
        `;
    }
    
    reviewHTML += `
        <div class="review-card">
            <h4><i class="fas fa-calculator"></i> Financial Summary</h4>
            <div class="review-item">
                <span><strong>Total Test Charges:</strong></span>
                <span class="text-success fw-bold">Rs. ${grandTotal.toFixed(2)}</span>
            </div>
            <div class="review-item">
                <span><strong>Additional Charges:</strong></span>
                <span class="text-info fw-bold">Rs. ${additionalCharges.toFixed(2)}</span>
            </div>
            <div class="total-row">
                <div class="d-flex justify-content-between">
                    <span><i class="fas fa-money-bill-wave"></i> GRAND TOTAL:</span>
                    <span class="text-primary">Rs. ${(grandTotal + additionalCharges).toFixed(2)}</span>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = reviewHTML;
}

// ==========================================
// FORM SUBMISSION
// ==========================================

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    try {
        const formData = new FormData(e.target);
        formData.append('action', 'saveSample');
        
        // Collect selected tests
        const tests = [];
        document.querySelectorAll('input[name^="test_"]:checked').forEach(test => {
            tests.push({
                sample: parseInt(test.dataset.sample),
                parameter_id: parseInt(test.dataset.param),
                variant_id: test.dataset.variant ? parseInt(test.dataset.variant) : null,
                charge: parseFloat(test.dataset.price)
            });
        });
        
        formData.append('tests', JSON.stringify(tests));
        
        const response = await fetch('src/Controllers/sample-controller.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(`Sample submitted successfully! Form Number: ${result.form_number}`, 'success');
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = `index.php?page=dashboard`;
            }, 2000);
        } else {
            throw new Error(result.message || 'Submission failed');
        }
        
    } catch (error) {
        console.error('Submission error:', error);
        showToast('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Form';
    }
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

function showToast(message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    const toastBody = document.getElementById('toastMessage');
    const icon = toast.querySelector('.toast-icon');
    const colors = { success: 'text-success fa-check-circle', error: 'text-danger fa-times-circle', 
                     warning: 'text-warning fa-exclamation-triangle', info: 'text-info fa-info-circle' };
    icon.className = `fas ${colors[type] || colors.info} me-2 toast-icon`;
    toastBody.textContent = message;
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function formatDate(dateStr) {
    try {
        return new Date(dateStr).toLocaleDateString('en-GB');
    } catch { return dateStr; }
}