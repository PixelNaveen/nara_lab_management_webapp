<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Sample Submission Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add your CSS links here if available -->
    <!-- <link href="assets/css/index.css" rel="stylesheet"> -->
    <!-- <link href="assets/css/samples_styles.css" rel="stylesheet"> -->
    <!-- <link href="assets/css/review_styles.css" rel="stylesheet"> -->
    <style>
        .step { display: none; }
        .step.active { display: block; }
        .progress-step { cursor: pointer; background-color: #ddd; border-radius: 50%; width: 30px; height: 30px; text-align: center; line-height: 30px; }
        .progress-step.active { background-color: #0d6efd; color: white; }
        .container { max-width: 900px; margin: auto; padding: 2rem; }
        .card { padding: 1rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 8px; }
        .sample-card { border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; }
        .param-row { margin-bottom: 1rem; }
        .client-list { list-style: none; padding: 0; }
        .client-list li { margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
        .btn { margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Sample Submission Form Demo</h1>
        
        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar" id="progressBar" style="width: 14.29%;"></div>
        </div>
        <ul class="list-unstyled d-flex justify-content-between mb-4">
            <li class="progress-step active" data-step="1">1</li>
            <li class="progress-step" data-step="2">2</li>
            <li class="progress-step" data-step="3">3</li>
            <li class="progress-step" data-step="4">4</li>
            <li class="progress-step" data-step="5">5</li>
            <li class="progress-step" data-step="6">6</li>
            <li class="progress-step" data-step="7">7</li>
        </ul>

        <form id="submissionForm">
            <!-- Simulated Form Base (like generate_new_form_number preview) -->
            <input type="hidden" name="form_base" value="25/0001">

            <!-- Step 1: Client Selection/Creation (like index.php) -->
            <div class="step active card" data-step="1">
                <h5>Step 1: Select or Create Client</h5>
                <div class="mb-3">
                    <label>Search Existing Client</label>
                    <input type="text" class="form-control" id="clientSearch" placeholder="Search by name or phone">
                    <ul id="clientResults" class="client-list mt-3"></ul>
                </div>
                <hr>
                <h6>Or Create New Client</h6>
                <div class="row">
                    <div class="col-md-6"><input type="text" class="form-control" name="client_name" placeholder="Client Name *" required></div>
                    <div class="col-md-6"><input type="text" class="form-control" name="phone_primary" placeholder="Phone *" required></div>
                    <div class="col-12 mt-3"><input type="text" class="form-control" name="address_line1" placeholder="Address"></div>
                    <div class="col-md-6 mt-3"><input type="text" class="form-control" name="city" placeholder="City"></div>
                    <div class="col-md-6 mt-3"><input type="text" class="form-control" name="contact_person" placeholder="Contact Person"></div>
                </div>
                <p class="text-muted mt-3">Fill new client fields or select from search.</p>
            </div>

            <!-- Step 2: Test Type (swab/regular) -->
            <div class="step card" data-step="2">
                <h5>Step 2: Select Test Type</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="submission_type" id="swab" value="swab" required>
                            <label class="form-check-label" for="swab">SWAB Testing (e.g., Surface Swabs)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="submission_type" id="regular" value="regular" required>
                            <label class="form-check-label" for="regular">Regular Testing (e.g., Water/Liquid Samples)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Submission Details -->
            <div class="step card" data-step="3">
                <h5>Step 3: Submission Details</h5>
                <div class="row">
                    <div class="col-12"><label>Sampling Date & Time *</label><input type="datetime-local" class="form-control" name="sampling_datetime" required></div>
                    <div class="col-12 mt-3"><label>Sampling Location</label><input type="text" class="form-control" name="sampling_location"></div>
                    <div class="col-12 mt-3"><label>Reason for Analysis</label><textarea class="form-control" name="reason_for_analysis" rows="2"></textarea></div>
                    <div class="col-12 mt-3"><label>Additional Notes</label><textarea class="form-control" name="additional_notes" rows="3"></textarea></div>
                </div>
            </div>

            <!-- Step 4: Sample Items (like samples.php) -->
            <div class="step card" data-step="4">
                <h5>Step 4: Add Sample Items</h5>
                <div id="samplesContainer"></div>
                <button type="button" id="addSampleBtn" class="btn btn-secondary">+ Add Sample</button>
            </div>

            <!-- Step 5: Select Tests (like review.php) -->
            <div class="step card" data-step="5">
                <h5>Step 5: Select Tests for Each Sample</h5>
                <p id="testTypeHint" class="text-muted"></p>
                <div id="samplesTestsContainer"></div>
                <div class="alert alert-info mt-3">Select at least one test per sample.</div>
            </div>

            <!-- Step 6: Review & Submit -->
            <div class="step card" data-step="6">
                <h5>Step 6: Review</h5>
                <div id="reviewSummary" class="border p-3 rounded bg-light"></div>
            </div>

            <!-- Step 7: Consent & Submit (final) -->
            <div class="step card" data-step="7">
                <h5>Step 7: Consent & Submit</h5>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="consent" required>
                    <label class="form-check-label" for="consent">I confirm the information is accurate.</label>
                </div>
            </div>

            <!-- Navigation -->
            <div class="d-flex justify-content-between mt-5">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">Submit</button>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('submissionForm');
        const steps = document.querySelectorAll('.step');
        const progressBar = document.getElementById('progressBar');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressSteps = document.querySelectorAll('.progress-step');
        const formBase = form.querySelector('[name="form_base"]').value;

        let currentStep = 1;
        let sampleCount = 0;
        let submissionType = '';

        // Simulated Clients (replace with real DB data in production)
        const simulatedClients = [
            { client_id: 1, client_name: 'ABC Water Company', city: 'Colombo', phone_primary: '0112345678' },
            { client_id: 2, client_name: 'Ministry of Health', city: 'Colombo', phone_primary: '0117654321' }
        ];

        // Simulated Parameters/Variants (from your SQL; add all)
        const params = [
            { parameter_id: 1, parameter_code: 'APC', parameter_name: 'Aerobic Plate Count', has_variants: true },
            { parameter_id: 2, parameter_code: 'TC', parameter_name: 'Total Coliform', has_variants: false }
            // Add more from your test_parameters
        ];
        const variants = {
            1: [
                { variant_id: 1, variant_name: 'at 37°C', full_display_name: 'Aerobic Plate Count cfu/g at 37°C' },
                { variant_id: 2, variant_name: 'at 30°C', full_display_name: 'Aerobic Plate Count cfu/g at 30°C' },
                { variant_id: 3, variant_name: 'Swab Variant', full_display_name: 'Aerobic Plate Count per Swab' } // Swab-specific example
            ]
            // Add more
        };

        // Persistence
        if (localStorage.getItem('sampleFormData')) {
            const savedData = JSON.parse(localStorage.getItem('sampleFormData'));
            Object.entries(savedData).forEach(([key, val]) => {
                const el = form.querySelector(`[name="${key}"]`);
                if (el) el.value = val;
            });
        }

        function saveData() {
            const formData = new FormData(form);
            const dataObj = Object.fromEntries(formData);
            localStorage.setItem('sampleFormData', JSON.stringify(dataObj));
        }

        function showStep(n) {
            steps.forEach((s, i) => s.classList.toggle('active', i + 1 === n));
            progressSteps.forEach((p, i) => p.classList.toggle('active', i + 1 <= n));
            progressBar.style.width = `${(n / 7) * 100}%`;
            prevBtn.style.display = n === 1 ? 'none' : 'inline-block';
            nextBtn.style.display = n === 7 ? 'none' : 'inline-block';
            submitBtn.style.display = n === 7 ? 'inline-block' : 'none';
            currentStep = n;

            if (n === 1) initClientSearch();
            if (n === 4) initSamples();
            if (n === 5) updateTests();
            if (n === 6) showReview();
            saveData();
        }

        function validateStep(n) {
            const step = steps[n - 1];
            const requireds = step.querySelectorAll('[required]');
            let valid = true;
            requireds.forEach(input => { if (!input.value.trim()) valid = false; });
            if (n === 1 && !form.querySelector('[name="client_name"]').value) valid = false; // Require client
            if (n === 4 && sampleCount === 0) valid = false;
            if (n === 5) {
                const checkedTests = document.querySelectorAll('input[name^="samples_params"]:checked');
                if (checkedTests.length === 0) valid = false;
            }
            if (n === 7 && !document.getElementById('consent').checked) valid = false;
            return valid;
        }

        function initClientSearch() {
            const searchInput = document.getElementById('clientSearch');
            const resultsList = document.getElementById('clientResults');
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase();
                resultsList.innerHTML = '';
                simulatedClients.filter(c => c.client_name.toLowerCase().includes(query) || c.phone_primary.includes(query))
                    .forEach(c => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <strong>${c.client_name}</strong> (${c.city}, ${c.phone_primary})
                            <button type="button" class="btn btn-primary btn-sm selectClient">Select</button>
                        `;
                        li.querySelector('.selectClient').addEventListener('click', () => {
                            form.querySelector('[name="client_name"]').value = c.client_name;
                            form.querySelector('[name="phone_primary"]').value = c.phone_primary;
                            form.querySelector('[name="city"]').value = c.city;
                            // Fill others if available
                            alert('Client selected!');
                        });
                        resultsList.appendChild(li);
                    });
            });
        }

        function initSamples() {
            document.getElementById('addSampleBtn').addEventListener('click', addSample);
            if (sampleCount === 0) addSample();
        }

        function addSample() {
            sampleCount++;
            const seq = ('0' + sampleCount).slice(-2);
            const sampleCode = formBase + '/' + seq;
            const container = document.getElementById('samplesContainer');

            const wrapper = document.createElement('div');
            wrapper.className = 'sample-card';
            wrapper.dataset.seq = sampleCount;
            wrapper.innerHTML = `
                <h3>Sample ${sampleCount} - Code: ${sampleCode}</h3>
                <input type="hidden" name="samples[${sampleCount}][sequence]" value="${sampleCount}">
                <input type="hidden" name="samples[${sampleCount}][sample_code]" value="${sampleCode}">
                <label>Sample Name *</label>
                <input type="text" class="form-control" name="samples[${sampleCount}][sample_name]" required>
                <label>Measurement *</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="samples[${sampleCount}][value]" placeholder="Value" required>
                    <select class="form-select" name="samples[${sampleCount}][unit]" required>
                        <option value="">Unit</option>
                        <option value="cm2">cm²</option>
                        <option value="m2">m²</option>
                        <option value="ml">ml</option>
                        <option value="l">L</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        ${submissionType === 'swab' ? '<option value="swab">swab</option>' : ''}
                    </select>
                </div>
                <label>Client Sample Reference (optional)</label>
                <input type="text" class="form-control" name="samples[${sampleCount}][client_sample_code]">
                <button type="button" class="btn btn-danger mt-2 removeSampleBtn">Remove</button>
            `;
            container.appendChild(wrapper);
            wrapper.querySelector('.removeSampleBtn').addEventListener('click', () => {
                wrapper.remove();
                sampleCount--;
            });
        }

        function updateTests() {
            submissionType = form.querySelector('[name="submission_type"]:checked')?.value || '';
            document.getElementById('testTypeHint').textContent = `Selected Type: ${submissionType.toUpperCase()}`;
            const container = document.getElementById('samplesTestsContainer');
            container.innerHTML = '';
            for (let i = 1; i <= sampleCount; i++) {
                const sampleDiv = document.createElement('div');
                sampleDiv.className = 'mb-4';
                sampleDiv.innerHTML = `<h4>Tests for Sample ${i}</h4>`;
                params.forEach(p => {
                    const pid = p.parameter_id;
                    const hasVariants = p.has_variants;
                    const paramRow = document.createElement('div');
                    paramRow.className = 'param-row';
                    if (hasVariants && variants[pid]) {
                        paramRow.innerHTML = `<label>${p.parameter_code} — ${p.parameter_name}</label>`;
                        variants[pid].forEach(v => {
                            const showVariant = (submissionType === 'swab' && v.variant_name.includes('Swab')) || 
                                                (submissionType === 'regular' && !v.variant_name.includes('Swab')) ||
                                                true; // Default show all; adjust based on your variants
                            if (showVariant) {
                                paramRow.innerHTML += `
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="samples_params[${i}][variant][${v.variant_id}]" id="s${i}_v${v.variant_id}">
                                        <label class="form-check-label" for="s${i}_v${v.variant_id}">${v.full_display_name}</label>
                                    </div>
                                `;
                            }
                        });
                    } else {
                        paramRow.innerHTML = `
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="samples_params[${i}][param][${pid}]" id="s${i}_p${pid}">
                                <label class="form-check-label" for="s${i}_p${pid}">${p.parameter_code} — ${p.parameter_name}</label>
                            </div>
                        `;
                    }
                    if (paramRow.querySelector('input')) sampleDiv.appendChild(paramRow);
                });
                container.appendChild(sampleDiv);
            }
        }

        function showReview() {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            let summary = `
                <h6><strong>Type:</strong> ${data.submission_type ? data.submission_type.toUpperCase() : 'N/A'}</h6>
                <h6><strong>Client:</strong> ${data.client_name}, ${data.phone_primary}</h6>
                <h6><strong>Sampling:</strong> ${data.sampling_datetime} at ${data.sampling_location || 'N/A'}</h6>
                <p><strong>Reason:</strong> ${data.reason_for_analysis || 'N/A'}</p>
                <p><strong>Notes:</strong> ${data.additional_notes || 'N/A'}</p>
            `;
            for (let i = 1; i <= sampleCount; i++) {
                summary += `<h6><strong>Sample ${i}:</strong> ${data[`samples[${i}][sample_name]`] || 'N/A'} (${data[`samples[${i}][value]`] || ''} ${data[`samples[${i}][unit]`] || ''})</h6>`;
            }
            document.getElementById('reviewSummary').innerHTML = summary;
        }

        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if (currentStep === 2) submissionType = form.querySelector('[name="submission_type"]:checked').value;
                showStep(currentStep + 1);
            } else {
                alert('Please complete all required fields.');
            }
        });

        prevBtn.addEventListener('click', () => showStep(currentStep - 1));

        progressSteps.forEach(step => {
            step.addEventListener('click', () => {
                const target = parseInt(step.dataset.step);
                if (target <= currentStep + 1 && validateStep(currentStep)) showStep(target);
            });
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateStep(7)) {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                console.log('Submitted Data:', data); // Simulate insert
                alert('Form Submitted Successfully! (Demo - Check console for data)');
                localStorage.removeItem('sampleFormData');
                form.reset();
                sampleCount = 0;
                document.getElementById('samplesContainer').innerHTML = '';
                document.getElementById('samplesTestsContainer').innerHTML = '';
                showStep(1);
            } else {
                alert('Please confirm consent.');
            }
        });

        // Init
        showStep(1);
    </script>
</body>
</html>