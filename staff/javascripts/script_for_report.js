    // API base relative to staff pages
    const API_BASE_URL = '../backend/api/';
        const CUSTOMER_APPLIANCE_API_URL = API_BASE_URL + 'customer_appliance_api.php';
        const PARTS_API_URL = API_BASE_URL + 'parts_api.php';
        const SERVICES_API_URL = API_BASE_URL + 'service_api.php';
        const STAFF_API_URL = API_BASE_URL + 'staff_api.php';
        const SERVICE_PRICE_API_URL = API_BASE_URL + 'service_price_api.php';

        $(document).ready(function() {
            initializeServiceReport();
            calculateTotals();

            // Load service reports on page load to check for pending reports
            loadServiceReportsOnInit();

            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            // Use event delegation for edit and delete buttons to prevent duplication
            $(document).on('click', '.edit-report', function(e) {
                e.preventDefault();
                const reportId = $(this).data('id');
                loadReportForEditing(reportId);
            });

            $(document).on('click', '.delete-report', function(e) {
                e.preventDefault();
                const reportId = $(this).data('id');
                deleteReport(reportId);
            });
        });

        // New function to load service reports on page initialization
        async function loadServiceReportsOnInit() {
            try {
                const response = await callServiceAPI('getAll');
                if (response.success && response.data) {
                    updateBadgeStatus(response.data);
                }
            } catch (error) {
                console.error("Failed to load service reports on init: ", error);
            }
        }

        // Function to update badge status based on reports data
        function updateBadgeStatus(reports) {
            let hasPendingReports = false;

            if (reports && reports.length > 0) {
                reports.forEach(report => {
                    if (report.status === 'Pending') {
                        hasPendingReports = true;
                    }
                });
            }

            const $badge = $('#report-badge');
            if (hasPendingReports) {
                $badge.show().addClass('blink-badge');
                console.log('Badge shown - Pending reports found'); // Debug log
            } else {
                $badge.hide().removeClass('blink-badge');
                console.log('Badge hidden - No pending reports'); // Debug log
            }
        }

        async function initializeServiceReport() {
            try {
                await loadServicePrices();
                bindEventHandlers();
                await loadInitialData();
            } catch (error) {
                console.error("Initialization failed: ", error);
                showAlert('danger', 'Failed to initialize application: ' + error.message);
            }
        }

        function bindEventHandlers() {
            $('#serviceReportForm').on('submit', handleFormSubmit);

            $(document).on('click', '#add-part', function() {
                addPartRow();
            });
            $(document).on('click', '.remove-part', removePartRow);

            $(document).on('change', '.part-select', function() {
                const partId = $(this).val();
                if (partId && isPartAlreadyUsed(partId, $(this))) {
                    showAlert('warning', 'This part is already added/included in the service form');
                    $(this).val('');
                    return;
                }
                updatePartsDetails($(this).closest('.parts-row'));
                calculateTotals();
            });

            $(document).on('input', '.quantity-input', function() {
                updatePartsDetails($(this).closest('.parts-row'));
            });

            $('#installation, #repair, #cleaning, #checkup').on('change', calculateTotals);
            $('#labor-amount').on('input', calculateTotals);
            $('#pullout-delivery').on('input', calculateTotals);

            $('#cancel-button').click(resetForm);

            $('#serviceReportListModal').on('show.bs.modal', loadServiceReports);

            $('select[name="status"]').on('change', function() {
                const reportId = $('#report_id').val();
                if (reportId) {
                    updateSubmitButton($(this).val(), reportId);
                }
            });

            // Ensure modal closes properly when clicking close button or backdrop
            $('#serviceReportListModal').on('hidden.bs.modal', function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
        }

        async function callServiceAPI(action, data = null, id = null) {
            try {
                const url = `${SERVICES_API_URL}?action=${action}${id ? `&id=${id}` : ''}`;

                switch (action) {
                    case 'create':
                        return await createService(url, data);
                    case 'update':
                        return await updateService(url, data);
                    case 'delete':
                        return await deleteService(url);
                    case 'getAll':
                        return await fetchService(url);
                    case 'getById':
                        return await fetchService(url);
                    default:
                        throw new Error(`Unknown action: ${action}`);
                }
            } catch (error) {
                console.error(`${action} Error: `, error);
                throw new Error(error.responseJSON?.message || error.statusText || 'API request failed');
            }
        }

        async function createService(url, data) {
            try {
                console.log('Creating service with data:', data);
                const response = await $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                console.log('Create service response:', response);
                return validateResponse(response, 'create');
            } catch (error) {
                console.error('Create service error:', error);
                const errorMessage = error.responseJSON?.message || error.statusText || 'Failed to create service report';
                throw new Error(errorMessage);
            }
        }

        async function updateService(url, data) {
            const response = await $.ajax({
                url: url,
                method: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify(data),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            return validateResponse(response, 'update');
        }

        async function deleteService(url) {
            const response = await $.ajax({
                url: url,
                method: 'DELETE',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            return validateResponse(response, 'delete');
        }

        async function fetchService(url) {
            const response = await $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            return validateResponse(response, 'fetch');
        }

        function validateResponse(response, action) {
            if (!response.success) {
                const defaultMessages = {
                    'create': 'Failed to create service report',
                    'update': 'Failed to update service report',
                    'delete': 'Failed to delete service report',
                    'fetch': 'Failed to fetch service reports\' data'
                };
                throw new Error(response.message || defaultMessages[action] || 'Operation failed');
            }
            return response;
        }

        function gatherFormData() {
            const formatDateForPHP = (dateStr) => {
                // Handle null, undefined, empty string, or whitespace
                if (!dateStr || (typeof dateStr === 'string' && dateStr.trim() === '')) {
                    return null;
                }
                try {
                    const date = new Date(dateStr);
                    // Check if date is valid
                    if (isNaN(date.getTime())) {
                        return null;
                    }
                    return date.toISOString().split('T')[0];
                } catch (e) {
                    console.error('Date formatting error:', e);
                    return null;
                }
            };

            const formData = {
                //service report - REQUIRED FIELDS
                customer_name: $('#customer-select option:selected').text() || '',
                appliance_name: $('#appliance-select option:selected').text() || '',
                date_in: formatDateForPHP($('#date-in').val()),
                status: $('select[name="status"]').val() || '',
                
                //service report - OPTIONAL FIELDS (can be empty)
                dealer: $('input[name="dealer"]').val() || '',
                findings: $('input[name="findings"]').val() || '',
                remarks: $('input[name="remarks"]').val() || '',
                location: [],

                //service details - ALL OPTIONAL
                service_types: [],
                complaint: $('textarea[name="complaint"]').val() || '',
                labor: parseFloat($('#labor-amount').val()) || 0,
                pullout_delivery: parseFloat($('#pullout-delivery').val()) || 0,
                parts_total_charge: parseFloat($('input[name="parts_charge"]').val()) || 0,
                service_charge: parseFloat($('#total-serviceCharge').val()) || 0,
                total_amount: parseFloat($('#total-amount-2').val()) || 0,
                receptionist: $('#receptionist-select option:selected').text() || '',
                manager: $('#manager-select option:selected').text() || '',
                technician: $('#technician-select option:selected').text() || '',
                released_by: $('#released-by-select option:selected').text() || '',

                parts: []
            };
            
            // Add optional date fields only if they have valid values
            const dopVal = formatDateForPHP($('input[name="dop"]').val());
            const datePulledVal = formatDateForPHP($('input[name="date_pulled_out"]').val());
            const dateRepairedVal = formatDateForPHP($('input[name="date_repaired"]').val());
            const dateDeliveredVal = formatDateForPHP($('input[name="date_delivered"]').val());
            
            if (dopVal) formData.dop = dopVal;
            if (datePulledVal) formData.date_pulled_out = datePulledVal;
            if (dateRepairedVal) formData.date_repaired = dateRepairedVal;
            if (dateDeliveredVal) formData.date_delivered = dateDeliveredVal;

            if ($('#shop').is(':checked')) formData.location.push('shop');
            if ($('#field').is(':checked')) formData.location.push('field');
            if ($('#out_wty').is(':checked')) formData.location.push('out_wty');

            if ($('#installation').is(':checked')) formData.service_types.push('installation');
            if ($('#repair').is(':checked')) formData.service_types.push('repair');
            if ($('#cleaning').is(':checked')) formData.service_types.push('cleaning');
            if ($('#checkup').is(':checked')) formData.service_types.push('checkup');

            $('.parts-row').each(function() {
                const $partSelect = $(this).find('.part-select');
                const $selectedOption = $partSelect.find('option:selected');
                const partText = $selectedOption.text();
                const partName = partText.split(' (')[0];
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const unitPrice = parseFloat($selectedOption.data('price')) || 0;

                if (partName && quantity > 0) {
                    formData.parts.push({
                        part_name: partName,
                        quantity: quantity,
                        unit_price: unitPrice,
                        parts_total: quantity * unitPrice,
                        part_id: $partSelect.val() // Add the part ID from the select element
                    });
                }
            });

            const reportId = $('#report_id').val();
            if (reportId) {
                formData.report_id = reportId;
            }
            
            console.log('Staff form data before sending:', formData);
            
            return formData;
        }

        //create process
        async function handleFormSubmit(e) {
            const $submitBtn = $('#submit-report-btn');
            if ($submitBtn.text().includes('Create Transaction')) {
                e.preventDefault();
                const reportId = $('#report_id').val();
                if (reportId) {
                    await createTransactionFromReport(reportId);
                }
                return;
            }

            e.preventDefault();
            const isValid = await validateForm();
            if (!isValid) return;

            try {
                showLoading(true);
                const formData = gatherFormData();

                let action = 'create';
                const reportId = $('#report_id').val();

                if(reportId) {
                    action = 'update';
                }

                console.log('Submitting form data: ', formData);
                
                const response = await callServiceAPI(action, formData, reportId);
                
                console.log('API Response:', response);
                
                if(!response || !response.success) {
                    const errorMsg = response?.message || 'Failed to process report';
                    console.error('Save failed:', errorMsg);
                    throw new Error(errorMsg);
                }
                
                // Only update cache AFTER successful save
                if (reportId && typeof window.updateReportInCache === 'function') {
                    window.updateReportInCache(reportId, formData);
                }

                if(!reportId && response.data?.report_id) {
                    $('#report_id').val(response.data.report_id);
                }

                updateSubmitButton(formData.status, $('#report_id').val());

                let successMessage = reportId ? 'Report updated successfully' : 'Report created successfully';
                showAlert('success', successMessage);

                await loadDropdown('parts', '.part-select');
                
                if (!reportId) {
                    resetForm();
                }
                
                // Refresh the service reports list from server to ensure accuracy
                await loadServiceReportsOnInit();
                if (typeof window.refreshServiceReports === 'function') {
                    await window.refreshServiceReports();
                } else if (typeof loadServiceReports === 'function') {
                    await loadServiceReports();
                }

                // Trigger dashboard refresh for all open tabs/windows
                // Method 1: BroadcastChannel (modern browsers, works immediately)
                if ('BroadcastChannel' in window) {
                    const channel = new BroadcastChannel('dashboard-refresh');
                    channel.postMessage({ action: 'refresh', timestamp: Date.now() });
                    channel.close();
                    console.log('Dashboard refresh signal sent via BroadcastChannel');
                }
                
                // Method 2: localStorage (fallback for older browsers and cross-tab)
                localStorage.setItem('dashboardRefreshNeeded', Date.now().toString());
                setTimeout(() => {
                    localStorage.setItem('dashboardRefreshNeeded', Date.now().toString());
                }, 100);
                
                // Method 3: Dispatch custom event for same-tab refresh
                window.dispatchEvent(new CustomEvent('dashboardRefresh', { 
                    detail: { timestamp: Date.now() } 
                }));
                
                console.log('Dashboard refresh signals sent (all methods)');

            } catch (error) {
                console.error('Form submission error:', error);
                showAlert('danger', error.message || 'An error occurred while processing the service report');
            } finally {
                showLoading(false);
            }
        }

        //read process 
        async function loadInitialData() {
            try {
                await Promise.all([
                    loadDropdown('customer', '.customer-select'),
                    loadDropdown('parts', '.part-select')
                ]);

                //load specific staff dropdowns
                await Promise.all([
                    ...$('.staff-select').map(function() {
                        return loadDropdown('staff', $(this));
                    }).get()
                ]);

                const $applianceSelect = $('.appliance-select');
                $applianceSelect.empty()
                    .append($('<option></option>').val('').text('Select Appliance'));

                $(document).on('change', '#customer-select', function() {
                    const customerId = $(this).val();
                    if (customerId) {
                        loadDropdown('appliance', '.appliance-select', customerId);
                    } else {
                        $applianceSelect.empty()
                            .append($('<option></option>').val('').text('Select Appliance'));
                    }
                });

                // Auto-populate date_in when appliance is selected
                $(document).on('change', '#appliance-select', function() {
                    const $selectedOption = $(this).find('option:selected');
                    const dateIn = $selectedOption.data('date-in');
                    if (dateIn) {
                        $('#date-in').val(dateIn);
                    } else {
                        $('#date-in').val('');
                    }
                });

                $applianceSelect.on('mousedown', function() {
                    if (!$('#customer-select').val()) {
                        showAlert('warning', 'Please select a customer first');
                        $(this).val('');
                        return false;
                    }
                });

            } catch (error) {
                console.error("Failed to load initial data:", error);
                showAlert('danger', 'Failed to load initial data');
            }
        }

        async function loadDropdown(type, selector, customerId = null) {
            try {
                let url, transformFn, dependent = false;
                let currentValues = new Map(); // Store current selections
                
                // Save current selections if reloading parts dropdown
                if (type === 'parts') {
                    $(selector).each(function() {
                        const $select = $(this);
                        if ($select.val()) {
                            currentValues.set($select.closest('.parts-row').index(), {
                                value: $select.val(),
                                quantity: $select.closest('.parts-row').find('.quantity-input').val()
                            });
                        }
                    });
                }

                switch (type) {
                    case 'customer':
                        url = CUSTOMER_APPLIANCE_API_URL + '?action=getAllCustomers&page=1&itemsPerPage=1000';

                        transformFn = item => ({
                            value: item.customer_id,
                            text: `${item.FullName}`
                        });
                        break;

                    case 'appliance':
                        if (customerId) {
                            //dependent appliance looking for specific customer
                            url = CUSTOMER_APPLIANCE_API_URL + `?action=getAppliancesByCustomerId&customerId=${customerId}`;

                            transformFn = item => ({
                                value: item.appliance_id,
                                text: `${item.brand} - ${item.serial_no || item.model_no || 'No Serial'} (${item.category || 'No Model'})`,
                                dateIn: item.date_in || '',
                                serial: item.serial_no || ''
                            });
                            dependent = true;
                        } else {
                            //independent appliance loading(show all appliance)
                            url = CUSTOMER_APPLIANCE_API_URL + '?action=getAllAppliances';

                            transformFn = item => ({
                                value: item.appliance_id,
                                text: `${item.brand} - ${item.serial_no || item.model_no || 'No Serial'} (${item.category || ''})`,
                                dateIn: item.date_in || '',
                                serial: item.serial_no || ''
                            });
                        }
                        break;

                    case 'parts':
                        url = PARTS_API_URL + '?action=getAllParts&page=1&itemsPerPage=1000';

                        transformFn = item => ({
                            value: item.part_id,
                            text: `${item.part_no} (${item.quantity_stock} available - ₱${item.price})`,
                            price: item.price
                        });
                        break;

                    case 'staff':
                        const role = $(selector).data('role');
                        if (role) {
                            url = STAFF_API_URL + `?action=getStaffsByRole&role=${encodeURIComponent(role)}`;
                        } else {
                            url = STAFF_API_URL + '?action=getAllStaffs&page=1&itemsPerPage=1000';
                        }

                        transformFn = item => ({
                            value: item.staff_id,
                            text: `${item.full_name || item.username} (${item.role})`
                        });
                        break;

                    default:
                        return;
                }

                const response = await $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json'
                });

                if (response?.success && response.data) {
                    let items = [];
                    // Normalize various payload shapes into a flat array
                    if (Array.isArray(response.data)) {
                        items = response.data;
                    } else if (Array.isArray(response.data.data)) {
                        items = response.data.data;
                    } else if (Array.isArray(response.data.customers)) {
                        items = response.data.customers;
                    } else if (Array.isArray(response.data.parts)) {
                        items = response.data.parts;
                    } else if (Array.isArray(response.data.staffs)) {
                        items = response.data.staffs;
                    } else if (Array.isArray(response.data.services)) {
                        items = response.data.services;
                    }
                    const $dropdowns = $(selector);

                    $dropdowns.each(function() {
                        const $dropdown = $(this);
                        const defaultText = dependent ? 'Select an Appliance' : `Select ${type}`;

                        $dropdown.empty().append(`<option value="">${defaultText}</option>`);

                        if (items.length === 0 && dependent) {
                            $dropdown.append('<option value="">No appliances found</option>');
                        } else {
                            items.forEach(item => {
                                const optionData = transformFn(item);
                                const $option = $('<option></option>')
                                    .val(optionData.value)
                                    .text(optionData.text);

                                if (optionData.price) {
                                    $option.data('price', optionData.price);
                                }
                                if (optionData.dateIn) {
                                    $option.data('date-in', optionData.dateIn);
                                }
                                $dropdown.append($option);
                            });
                        }

                        //enable/disable based on dependency and content
                        if (type === 'appliance') {
                            $dropdown.prop('disabled', !customerId || items.length === 0);
                        }

                        // Populate customer suggestions list if loading customers
                        if (type === 'customer') {
                            window.customersList = items.map(item => ({
                                id: item.customer_id,
                                name: item.FullName
                            }));
                            // Dedupe by name (case-insensitive)
                            const seen = new Set();
                            window.customersList = window.customersList.filter(c => {
                                const key = (c.name || '').toLowerCase().trim();
                                if (seen.has(key)) return false;
                                seen.add(key);
                                return true;
                            });
                            // Initialize customer search after populating list
                            if (typeof initCustomerSearch === 'function') {
                                initCustomerSearch();
                            }
                        }

                        // Keep a global list of appliances for matching when editing
                        if (type === 'appliance') {
                            window.applianceList = items.map(item => ({
                                id: item.appliance_id,
                                name: transformFn(item).text,
                                dateIn: transformFn(item).dateIn
                            }));
                        }
                    });
                }

            } catch (error) {
                console.error(`Error loading ${type}:`, error);
                const $dropdowns = $(selector);
                $dropdowns.empty().append(`<option value="">Error loading ${type}</option>`).prop('disabled', true);

                if (type === 'appliance') {
                    showAlert('danger', `Error loading appliances: ${error.statusText || 'Unknown error'}`);
                }
            }
        }

        //update process
        async function loadReportForEditing(reportId) {
            try {
                showLoading(true, '#serviceReportForm');

                const response = await callServiceAPI('getById', null, reportId);
                if (!response.success || !response.data) {
                    throw new Error(response.message || 'Report not found');
                }

                const report = response.data;
                console.log('Report Data: ', report);

                resetForm();

                //basic report info
                $('#report_id').val(report.report_id);
                $('#date-in').val(report.date_in);
                $('select[name="status"]').val(report.status);
                $('input[name="dealer"]').val(report.dealer || '');
                $('input[name="dop"]').val(report.dop || '');
                $('input[name="date_pulled_out"]').val(report.date_pulled_out || '');
                $('input[name="findings"]').val(report.findings || '');
                $('input[name="remarks"]').val(report.remarks || '');
                $('textarea[name="complaint"]').val(report.complaint || '');

                updateSubmitButton(report.status, reportId);

                //location checkbox
                const location = Array.isArray(report.location) ? report.location : JSON.parse(report.location || '[]');
                $('#shop').prop('checked', location.includes('shop'));
                $('#field').prop('checked', location.includes('field'));
                $('#out_wty').prop('checked', location.includes('out_wty'));

                //service checkbox
                const serviceTypes = Array.isArray(report.service_types) ? report.service_types : JSON.parse(report.service_types || '[]');
                $('#installation').prop('checked', serviceTypes.includes('installation'));
                $('#repair').prop('checked', serviceTypes.includes('repair'));
                $('#cleaning').prop('checked', serviceTypes.includes('cleaning'));
                $('#checkup').prop('checked', serviceTypes.includes('checkup'));

                //numeric fields
                $('#labor-amount').val(report.labor || '0.00');
                $('#pullout-delivery').val(report.pullout_delivery || '0.00');
                $('#total-serviceCharge').val(report.service_charge || '0.00');
                $('#total-amount').val(report.total_amount || '0.00');
                $('#total-amount-2').val(report.total_amount || '0.00');
                $('input[name="parts_charge"]').val(report.parts_total_charge || '0.00');

                //dates
                $('input[name="date_repaired"]').val(report.date_repaired || '');
                $('input[name="date_delivered"]').val(report.date_delivered || '');

                await Promise.all([
                    loadDropdown('customer', '.customer-select'),
                    loadDropdown('parts', '.part-select'),
                    loadDropdown('staff', '#receptionist-select'),
                    loadDropdown('staff', '#manager-select'),
                    loadDropdown('staff', '#technician-select'),
                    loadDropdown('staff', '#released-by-select')
                ]);

                //small delay to ensure DOM is update
                await new Promise(resolve => setTimeout(resolve, 100));

                setDropdownValue('#customer-select', report.customer_name);
                // Trigger change so appliances auto-load via the event listener
                $('#customer-select').trigger('change');
                
                // Wait for appliances to load after customer change event
                await new Promise(resolve => setTimeout(resolve, 150));
                
                setDropdownValue('#appliance-select', report.appliance_name);
                // Don't trigger change here - let user select appliance manually to populate date_in

                setDropdownValue('#receptionist-select', report.receptionist);
                $('#receptionist-select').trigger('change');
                setDropdownValue('#manager-select', report.manager);
                $('#manager-select').trigger('change');
                setDropdownValue('#technician-select', report.technician);
                $('#technician-select').trigger('change');
                setDropdownValue('#released-by-select', report.released_by);
                $('#released-by-select').trigger('change');

                if (report.parts && report.parts.length > 0) {
                    $('#parts-container .parts-row:not(:first)').remove();

                    for (let i = 0; i < report.parts.length; i++) {
                        const part = report.parts[i];
                        let $row;

                        if (i === 0) {
                            $row = $('#parts-container .parts-row:first');
                        } else {
                            $row = addPartRow();
                        }

                        const $select = $row.find('.part-select');
                        let $option = $select.find(`option:contains("${part.part_name}")`);

                        if ($option.length === 0) {
                            $select.append(`
                                <option value="custom" selected data-price="${part.unit_price}">
                                    ${part.part_name} - ₱${part.unit_price}
                                </option>`);
                            $option = $select.find('option:last');
                        }

                        $option.prop('selected', true);
                        $row.find('.quantity-input').val(part.quantity);
                        $row.find('.amount-input').val((part.quantity * part.unit_price).toFixed(2));
                    }
                }

                
                calculateTotals();
                
                // Close the modal immediately
                $('#serviceReportListModal').modal('hide');
                // Ensure backdrop is removed and body is reset
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');

            } catch (error) {
                console.error('Error loading report:', error);
                showAlert('danger', 'Failed to load report: ' + error.message);
            } finally {
                showLoading(false, '#serviceReportForm');
            }
        }

        function setDropdownValue(selector, value) {
            if (!value) return;

            // Skip placeholder/default values
            const placeholders = ['Select Appliance', 'Select an Appliance', 'Select staff', 'Receptionist', 'Manager', 'Technician', 'Released By'];
            if (placeholders.includes(value.trim())) {
                console.log(`Skipping placeholder value "${value}" for dropdown ${selector}`);
                return;
            }

            const $dropdown = $(selector);
            const $options = $dropdown.find('option');

            // Step 1: Extract the actual username/name (remove role in parentheses)
            // Stored value might be "admin123 (Manager)" or "brand - serial (category)" or just "admin123"
            const storedNameMatch = value.match(/^([^(]+)/);
            const cleanStoredName = storedNameMatch ? storedNameMatch[1].trim().toLowerCase() : value.toLowerCase();

            console.log(`Setting dropdown ${selector} - Original value: "${value}", Clean name: "${cleanStoredName}"`);

            // Step 2: Try exact match on clean names
            let $option = $options.filter((i, el) => {
                const optionText = $(el).text();
                // Extract name from "Name (Role)" or "Brand - Serial (Category)" format
                const optionNameMatch = optionText.match(/^([^(]+)/);
                const cleanOptionName = optionNameMatch ? optionNameMatch[1].trim().toLowerCase() : optionText.toLowerCase();
                return cleanOptionName === cleanStoredName;
            });

            // Step 3: Try case-insensitive partial match if exact match failed
            if ($option.length === 0) {
                $option = $options.filter((i, el) => {
                    const optionText = $(el).text().toLowerCase();
                    return optionText.includes(cleanStoredName);
                });
            }

            // Set the selected option
            if ($option.length > 0) {
                $option.first().prop('selected', true);
                console.log(`Successfully set dropdown ${selector} to: "${$option.first().text()}"`);
            } else {
                console.warn(`Could not match value "${value}" in dropdown ${selector}. Available options:`, 
                    $options.map((i, el) => $(el).text()).get());
            }
        }

        //delete process
        async function deleteReport(reportId) {
            if (!confirm('Are you sure you want to delete this report?\nThis action cannot be undone')) return;

            try {
                showLoading(true, '#serviceReportListModal .modal-body');
                await callServiceAPI('delete', null, reportId);
                showAlert('success', 'Report deleted successfully');
                
                // Refresh badge status after deletion
                await loadServiceReportsOnInit();
                await loadServiceReports();
                
            } catch (error) {
                showAlert('danger', error.message);
            } finally {
                showLoading(false, '#serviceReportListModal .modal-body');
            }
        }

        async function loadServiceReports() {
            try {
                showLoading(true, '#serviceReportListModal .modal-body');

                const response = await callServiceAPI('getAll');

                if (!response.success || !response.data) {
                    throw new Error(response.message || 'No service reports found');
                }

                const $tbody = $('#serviceReportListModal tbody').empty();

                response.data.forEach(report => {
                    let serviceTypes = 'N/A';

                    if (report.service_types && Array.isArray(report.service_types)) {
                        serviceTypes = report.service_types.join(', ');
                    } else if (typeof report.service_types === 'string') {
                        serviceTypes = report.service_types;
                    }

                    const dateIn = report.date_in ? new Date(report.date_in).toLocaleDateString() : 'N/A';
                    
                    let statusBadge = '';
                    switch(report.status) {
                        case 'Pending':
                            statusBadge = '<span class="badge badge-warning">Pending</span>';
                            break;
                        case 'Under Repair':
                            statusBadge = '<span class="badge badge-info">Under Repair</span>';
                            break;
                        case 'Unrepairable':
                            statusBadge = '<span class="badge badge-danger">Unrepairable</span>';
                            break;
                        case 'Release Out':
                            statusBadge = '<span class="badge badge-secondary">Release Out</span>';
                            break;
                        case 'Completed':
                            statusBadge = '<span class="badge badge-success">Completed</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge badge-light">' + report.status + '</span>';
                    }

                    const totalAmount = parseFloat(report.total_amount || 0);

                    $tbody.append(`
                        <tr>
                            <td>${report.report_id}</td>
                            <td>${report.customer_name}</td>
                            <td>${report.appliance_name}</td>
                            <td>${serviceTypes}</td>
                            <td>${dateIn}</td>
                            <td>${statusBadge}</td>
                            <td>${totalAmount.toFixed(2)}</td>
                            <td class="actions-col">
                                <a href="#" class="edit-report" data-id="${report.report_id}">
                                    <i class="material-icons text-primary">edit</i>
                                </a>
                                <a href="#" class="delete-report" data-id="${report.report_id}">
                                    <i class="material-icons text-danger">delete</i>
                                </a>
                            </td>
                        </tr>
                    `);
                });

                // Update badge status
                updateBadgeStatus(response.data);

                // REMOVED DUPLICATE EVENT HANDLERS - Using event delegation instead

            } catch (error) {
                console.error("Failed to load service reports: ", error);
                showAlert('danger', 'Failed to load service reports: ' + error.message);
            } finally {
                showLoading(false, '#serviceReportListModal .modal-body');
            }
        }

        function updateSubmitButton(status, reportId = '') {
            const $submitBtn = $('#submit-report-btn');

            if(status === 'Completed' && reportId) {
                $submitBtn.html('Submit Report');
                $submitBtn.removeClass('btn-primary').addClass('btn-success');

                $submitBtn.off('click').on('click', function(e) {
                    e.preventDefault();
                    createTransactionFromReport(reportId);
                });
            } else {
                const buttonText = reportId ? 'Update Report' : 'Create Report';
                $submitBtn.html(buttonText);
                $submitBtn.removeClass('btn-success').addClass('btn-primary');
            }
            
        }

        function addPartRow(partData = {}) {
            let newRow;

            if ($('#parts-container .parts-row').length === 0) {
                newRow = $(`
                    <div class="row mb-2 align-items-center g-2 parts-row">
                        <div class="col-md-3 pe-1">
                            <select class="form-control part-select" name="part_used[]">
                                <option value="">Select Part</option>
                            </select>
                        </div>
                        <div class="col-md-2 px-1">
                            <input type="number" class="form-control quantity-input" name="quantity[]" placeholder="Quantity" min="1">
                        </div>
                        <div class="col-md-2 px-1">
                            <input type="number" class="form-control amount-input" name="amount[]" placeholder="Amount" readonly>
                        </div>
                        <div class="col-md-1 px-1 d-flex justify-content-end align-items-center">
                            <button type="button" class="btn btn-danger remove-part d-flex align-items-center justify-content-center" style="display:none;">
                                <span class="material-icons" style="font-size: 1.2em;">delete</span>
                            </button>
                        </div>
                    </div>
                `);
            } else {
                newRow = $('.parts-row').first().clone(true, true);
                newRow.find('select').val('');
                newRow.find('input').val('');
                newRow.find('.remove-part').show();

                $('#parts-container').append(newRow);
                updateRemoveButtons();

                return newRow;
            }
        }

        function removePartRow() {
            if ($('.parts-row').length > 1) {
                $(this).closest('.parts-row').remove();
                updateRemoveButtons();
                calculateTotals();
            }
        }

        function updateRemoveButtons() {
            const rows = $('#parts-container .parts-row');
            rows.find('.remove-part').toggle(rows.length > 1);
        }

        function updatePartsDetails(row) {
            const selectedOption = row.find('.part-select option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            const amount = price * quantity;

            // Get the selected part's current stock from API
            if (selectedOption.val() && quantity > 0) {
                const partNo = selectedOption.text().split(' (')[0];

                // Get real-time stock level
                $.ajax({
                    url: PARTS_API_URL + '?action=getPartsById&id=' + selectedOption.val(),
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const availableStock = response.data.quantity_stock;

                            if (quantity > availableStock) {
                                showAlert('warning', `Quantity exceeds available stock (${availableStock})`);
                                row.find('.quantity-input').val(availableStock);
                                row.find('.amount-input').val((price * availableStock).toFixed(2));
                            } else {
                                row.find('.amount-input').val(amount.toFixed(2));
                            }
                            calculateTotals();
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Failed to verify stock quantity');
                    }
                });
            } else {
                row.find('.amount-input').val(amount.toFixed(2));
                calculateTotals();
            }
        }

        function calculateTotals() {
            const laborCharge = parseFloat($('#labor-amount').val()) || 0;
            const deliveryCharge = parseFloat($('#pullout-delivery').val()) || 0;
            const serviceCharge = calculateServiceCharge();
            const partsTotal = calculatePartsTotal();

            const grandTotal = (
                parseFloat(laborCharge.toFixed(2)) +
                parseFloat(deliveryCharge.toFixed(2)) +
                parseFloat(serviceCharge.toFixed(2)) +
                parseFloat(partsTotal.toFixed(2))
            ).toFixed(2);

            $('input[name="parts_charge"]').val(partsTotal.toFixed(2));
            $('#total-serviceCharge').val(serviceCharge.toFixed(2));
            $('#total-amount').val(grandTotal);
            $('#total-amount-2').val(grandTotal);
        }

        // use a global servicePrices to avoid duplicate declarations across staff scripts
        window.servicePrices = window.servicePrices || {};

        async function loadServicePrices() {
            try {
                const response = await $.ajax({
                    url: SERVICE_PRICE_API_URL + '?action=getAll',
                    type: 'GET',
                    dataType: 'json'
                });

                if(response && response.success) {
                    window.servicePrices = response.data;
                    console.log('Loaded service prices: ', window.servicePrices);
                } else {
                    // fallback to default prices 
                    window.servicePrices = {
                        installation: 500,
                        repair: 300,
                        cleaning: 200,
                        checkup: 150
                    };
                    console.warn('Using fallback service prices');
                }

            } catch (error) {
                console.error('Failed to load service prices: ', error);

                // fallback to default prices 
                window.servicePrices = {
                    installation: 500,
                    repair: 300,
                    cleaning: 200,
                    checkup: 150
                };
                console.warn('Using fallback service prices');
            }
        }

        function calculateServiceCharge() {
            let total = 0;

            const prices = window.servicePrices || {};
            if ($('#installation').is(':checked')) total += parseFloat(prices.installation || 0);
            if ($('#repair').is(':checked')) total += parseFloat(prices.repair || 0);
            if ($('#cleaning').is(':checked')) total += parseFloat(prices.cleaning || 0);
            if ($('#checkup').is(':checked')) total += parseFloat(prices.checkup || 0);

            return parseFloat(total.toFixed(2));
        }

        function calculatePartsTotal() {
            let total = 0;

            $('.parts-row').each(function() {
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.part-select option:selected').data('price')) || 0;
                const amount = quantity * price;

                $(this).find('.amount-input').val(amount.toFixed(2));
                total += amount;
            });

            return parseFloat(total.toFixed(2));
        }

        function formatDateForInput(dateString) {
            if (!dateString) return '';

            if (dateString.includes('-')) {
                return dateString;
            }

            const parts = dateString.split('/');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
            }
            return dateString;
        }

        function resetForm() {
            $('#serviceReportForm')[0].reset();

            const $firstRow = $('#parts-container .parts-row').first();
            $firstRow.find('select').val('');
            $firstRow.find('input').val('');
            if ($('#parts-container .parts-row').length > 1) {
                $('#parts-container .parts-row:not(:first)').remove();
            }

            $('.staff-select').val('');
            $('input[type="checkbox"]').prop('checked', false);
            $('#report_id').val('');
        
            updateSubmitButton('', '');
            calculateTotals();
            updateRemoveButtons();
        }

        function showAlert(type, message) {
            $('.alert-notification').remove();
            const alertHtml = `
                <div class="alert-notification alert alert-${type} alert-disimissible fade show">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `;
            $('body').prepend(alertHtml);
            setTimeout(() => $('.alert-notification').alert('close'), 5000);
        }

        function showLoading(show, element) {
            const $element = $(element);
            if (show) {
                $element.addClass('loading');
                $element.append('<div class="loading-overlay"><div class="spinner-border"></div></div>');
            } else {
                $element.removeClass('loading');
                $element.find('.loading-overlay').remove();
            }
        }

        async function validateForm() {
            // Only validate the 4 required fields: customer, appliance, date_in, status
            if (!$('#customer-select').val()) {
                showAlert('danger', 'Please select a customer');
                return false;
            }

            if (!$('#appliance-select').val()) {
                showAlert('danger', 'Please select an appliance');
                return false;
            }

            // Ensure Date In is provided — backend requires this field
            if (!$('#date-in').val()) {
                showAlert('danger', 'Please select Date In');
                return false;
            }

            if (!$('select[name="status"]').val()) {
                showAlert('danger', 'Please select a status');
                return false;
            }

            // Only validate parts quantities if parts are actually added
            const hasParts = $('.parts-row .part-select').filter(function() {
                return $(this).val() !== '';
            }).length > 0;

            if (hasParts) {
                const partsValid = await validatePartsQuantities();
                if (!partsValid) {
                    return false;
                }
            }

            return true;
        }

        async function validatePartsQuantities() {
            const parts = [];
            let isValid = true;

            $('.parts-row').each(function() {
                const partSelect = $(this).find('.part-select option:selected');
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;

                if (partSelect.val() && quantity > 0) {
                    parts.push({
                        id: partSelect.val(),
                        quantity: quantity,
                        name: partSelect.text().split(' (')[0]
                    });
                }
            });

            if (parts.length === 0) {
                return true;
            }

            // Verify all parts quantities
            for (const part of parts) {
                try {
                    const response = await $.ajax({
                        url: PARTS_API_URL + '?action=getPartsById&id=' + part.id,
                        type: 'GET'
                    });

                    if (response.success && response.data) {
                        const availableStock = response.data.quantity_stock;
                        if (part.quantity > availableStock) {
                            showAlert('danger', `Insufficient stock for ${part.name}. Available: ${availableStock}`);
                            isValid = false;
                            break;
                        }
                    }
                } catch (error) {
                    showAlert('danger', `Failed to verify stock for ${part.name}`);
                    isValid = false;
                    break;
                }
            }

            return isValid;
        }

        function isPartAlreadyUsed(partId, currentSelect) {
            let used = false;
            $('.part-select').each(function() {
                if (this === currentSelect[0]) return true;

                if ($(this).val() === partId && $(this).val() !== '') {
                    used = true;
                    return false;
                }
            });
            return used;
        }

        async function createTransactionFromReport(reportId) {
            try {
                showLoading(true, '#serviceReportListModal .modal-body');

                // First check if transaction already exists
                const checkResponse = await $.ajax({
                    url: '../backend/api/transaction_api.php?action=getAll',
                    method: 'GET',
                    dataType: 'json'
                });

                if (checkResponse.success && checkResponse.data) {
                    // Handle different response structures
                    let transactions = [];
                    if (Array.isArray(checkResponse.data)) {
                        transactions = checkResponse.data;
                    } else if (Array.isArray(checkResponse.data.data)) {
                        transactions = checkResponse.data.data;
                    } else if (Array.isArray(checkResponse.data.transactions)) {
                        transactions = checkResponse.data.transactions;
                    }
                    
                    const existingTransaction = transactions.find(t => t.report_id == reportId);
                    if (existingTransaction) {
                        showAlert('info', 'Transaction already exists for this report');
                        return;
                    }
                }

                // first get the complete report data 
                const response = await callServiceAPI('getById', null, reportId);
                if(!response.success || !response.data) {
                    throw new Error('Failed to load report data');
                }

                const reportData = response.data;

                const transactionData = {
                    report_id: reportId,
                    customer_name: reportData.customer_name,
                    appliance_name: reportData.appliance_name,
                    total_amount: reportData.total_amount || 0,
                    service_types: reportData.service_types || [],
                    payment_status: 'Pending' 
                };

                const transactionResponse = await $.ajax({
                    url: '../backend/api/transaction_api.php?action=createFromReport',
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(transactionData),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if(!transactionResponse.success) {
                    throw new Error(transactionResponse.message || 'Failed to create transaction');
                }

                    showAlert('success', 'Transaction created successfully');
                    // Update button after successful creation
                    updateSubmitButton('Completed', reportId);

            } catch (error) {
                console.error('Error creating transaction:', error);
                showAlert('danger', 'Failed to create transaction: ' + error.message);
            } finally {
                showLoading(false, '#serviceReportListModal .modal-body');
            }
        }
