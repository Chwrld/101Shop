<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin'); 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Service Report</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <style>
                /* Print styles */
                @media print {
                    body {
                        visibility: hidden;
                        margin: 0 !important;
                        padding: 0 !important;
                        color: #000000 !important;
                    }
                    
                    .wrapper,
                    .xp-menubar,
                    .body-overlay,
                    #sidebar,
                    #content .main-content .row .col-md-12 .card,
                    .modal-header,
                    .modal-footer,
                    #serviceReportListModal,
                    .modal-header button,
                    .ms-auto,
                    .d-flex {
                        display: none !important;
                    }
                    
                    #printReportModal {
                        position: static !important;
                        display: block !important;
                        width: 100% !important;
                        height: 100% !important;
                        background: white !important;
                        border: none !important;
                        opacity: 1 !important;
                    }
                    
                    #printReportModal .modal-dialog {
                        position: static !important;
                        display: block !important;
                        width: 100% !important;
                        height: auto !important;
                        margin: 0 !important;
                        max-width: 100% !important;
                    }
                    
                    #printReportModal .modal-content {
                        border: none !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                        height: auto !important;
                        background: white !important;
                        opacity: 1 !important;
                    }
                    
                    #printReportModal .modal-body {
                        padding: 0 !important;
                        display: block !important;
                        height: auto !important;
                        background: white !important;
                        opacity: 1 !important;
                    }
                    
                    #print-report-body {
                        display: block !important;
                        width: 100% !important;
                        height: auto !important;
                        background: white !important;
                        page-break-inside: avoid !important;
                        opacity: 1 !important;
                        color: #000000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                    
                    /* Force ALL text to be pure black - not gray */
                    #print-report-body,
                    #print-report-body * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                        color: #000000 !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    }
                    
                    /* Specific text elements - force pure black */
                    #print-report-body h1,
                    #print-report-body h2,
                    #print-report-body h3,
                    #print-report-body h4,
                    #print-report-body h5,
                    #print-report-body h6 {
                        color: #000000 !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        text-shadow: none !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    #print-report-body p,
                    #print-report-body span,
                    #print-report-body strong,
                    #print-report-body b,
                    #print-report-body em,
                    #print-report-body i {
                        color: #000000 !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        text-shadow: none !important;
                        -webkit-text-fill-color: #000000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Force table text to pure black */
                    #print-report-body table {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        color: #000000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    #print-report-body tbody,
                    #print-report-body thead,
                    #print-report-body tfoot {
                        color: #000000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    #print-report-body tr {
                        page-break-inside: avoid !important;
                        color: #000000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    #print-report-body td,
                    #print-report-body th {
                        color: #000000 !important;
                        border-color: #000000 !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    /* Force div and section text to black */
                    #print-report-body div {
                        color: #000000 !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    /* Remove any background colors and shadows when printing so text remains visible */
                    #print-report-body,
                    #print-report-body * {
                        background: transparent !important;
                        background-color: transparent !important;
                        box-shadow: none !important;
                        -webkit-box-shadow: none !important;
                        filter: none !important;
                    }
                    
                    @page {
                        size: A4;
                        margin: 0.3in;
                    }
                }

        .print-content {
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
            background: transparent;
        }

        // Render staff suggestions for a given input (native, clickable list)
        function renderStaffSuggestions($input, filterText) {
            const inputId = $input.attr('id');
            if (!inputId) return;
            const selectId = $input.data('target') ? $($input.data('target')).attr('id') : (inputId.replace('-input', '-select'));
            try {
                console.debug && console.debug('renderStaffSuggestions called for', inputId, '->', selectId, 'filter="' + (filterText || '') + '"');
            } catch (e) { }
            const suggestionsId = `${inputId}-suggestions`;
            const $suggestions = $(`#${suggestionsId}`);
            if (!$suggestions.length) return;

            const list = (window.staffLists && window.staffLists[selectId]) ? window.staffLists[selectId] : [];
            const text = (filterText || '').toLowerCase().trim();

            try {
                console.debug && console.debug('staff list length for', selectId, '=', (list && list.length) || 0);
            } catch (e) { }

            let matches = [];
            if (!text) {
                matches = list.slice(0, 20);
            } else {
                // start-with first
                matches = list.filter(s => s.text && s.text.toLowerCase().startsWith(text));
                if (matches.length === 0) {
                    matches = list.filter(s => s.text && s.text.toLowerCase().includes(text));
                }
                matches = matches.slice(0, 20);
            }

            if (!matches || matches.length === 0) {
                try {
                    console.debug && console.debug('renderStaffSuggestions - no matches for', text, 'in', selectId);
                } catch (e) { }
                $suggestions.hide();
                return;
            }

            $suggestions.empty();
            matches.forEach(s => {
                const $item = $(`<button type="button" class="list-group-item list-group-item-action">${s.text}</button>`);
                $item.data('id', s.id);
                $item.data('text', s.text);
                $item.on('click', function() {
                    setStaffFromSuggestion(s.id, s.text, `#${inputId}`, `#${selectId}`);
                });
                $suggestions.append($item);
            });

            // position and size
            const $wrapper = $input.parent();
            const width = $wrapper.length ? $wrapper.innerWidth() : $input.outerWidth();
            $suggestions.css({
                display: 'block',
                width: width + 'px'
            });
        }

        function setStaffFromSuggestion(id, text, inputSelector, selectSelector) {
            const $input = $(inputSelector);
            const $select = $(selectSelector);
            if ($input.length) $input.val(text);
            if ($select.length) {
                let $opt = $select.find(`option[value="${id}"]`);
                if ($opt.length === 0) {
                    $opt = $(`<option></option>`).val(id).text(text);
                    $select.append($opt);
                }
                $select.val(id).trigger('change');
            }
            const suggestionsId = `${$input.attr('id')}-suggestions`;
            $(`#${suggestionsId}`).hide();
        }

                // Reusable searchable input initializer
                // inputSelector: jQuery selector for the visible input
                // getItemsFn: function that returns array of items {id, text} or array of strings
                // options: { selectSelector: '#hidden-select', maxItems: 20 }
                function createSearchableInput(inputSelector, getItemsFn, options = {}) {
                    const $input = $(inputSelector);
                    if (!$input.length) return;

                    const inputId = $input.attr('id');
                    const suggestionsId = `${inputId}-suggestions`;
                    const $parent = $input.parent();

                    // ensure parent positioned so absolute suggestions position correctly
                    if ($parent.length && $parent.css('position') === 'static') {
                        $parent.css('position', 'relative');
                    }

                    // create suggestions container if missing
                    if ($parent.length && $(`#${suggestionsId}`).length === 0) {
                        $parent.append(`<div id="${suggestionsId}" class="staff-suggestions list-group" style="display:none; max-height:220px; overflow-y:auto; position:absolute; left:0; top:calc(100% + 6px); z-index:2000; width:100%;"></div>`);
                    }

                    const $suggestions = $(`#${suggestionsId}`);
                    const selectSelector = options.selectSelector || $input.data('target') || null;
                    const maxItems = options.maxItems || 1000;

                    // If the staff list is loaded later, re-render suggestions when available
                    $(document).off(`staffListLoaded.${inputId}`).on(`staffListLoaded.${inputId}`, function(e, loadedSelectId, loadedArray) {
                        try {
                            const selId = (selectSelector || '').replace('#','');
                            if (selId && selId === loadedSelectId) {
                                // if input is focused, show suggestions; otherwise keep list ready
                                if (document.activeElement && document.activeElement.id === inputId) {
                                    render($input.val() || '');
                                }
                            }
                        } catch (err) { /* ignore */ }
                    });

                    function normalize(items) {
                        if (!items) return [];
                        return items.map(it => {
                            if (typeof it === 'string') return { id: null, text: it };
                            if (it && typeof it === 'object') return { id: it.id || it.value || null, text: it.text || it.name || it.full_name || '' };
                            return { id: null, text: String(it) };
                        }).filter(i => i.text && i.text.trim());
                    }

                    function render(filterText) {
                        const raw = (typeof getItemsFn === 'function') ? getItemsFn() : (getItemsFn || []);
                        const list = normalize(raw || []);
                        const text = (filterText || '').toString().toLowerCase().trim();

                        let matches = [];
                        if (!text) {
                            matches = list.slice(0, maxItems);
                        } else {
                            // start-with priority
                            matches = list.filter(i => i.text.toLowerCase().startsWith(text));
                            if (matches.length === 0) {
                                matches = list.filter(i => i.text.toLowerCase().includes(text));
                            }
                            matches = matches.slice(0, maxItems);
                        }

                        if (!matches || matches.length === 0) {
                            $suggestions.hide();
                            return;
                        }

                        $suggestions.empty();
                        matches.forEach(m => {
                            const $item = $(`<button type="button" class="list-group-item list-group-item-action">${m.text}</button>`);
                            $item.data('id', m.id);
                            $item.data('text', m.text);
                            $item.on('click', function() {
                                // set visible input
                                $input.val(m.text);
                                // sync hidden select if available
                                if (selectSelector) {
                                    const $select = $(selectSelector);
                                    if ($select.length) {
                                        let $opt = $select.find(`option[value="${m.id}"]`);
                                        if ($opt.length === 0 && m.id != null) {
                                            $opt = $(`<option></option>`).val(m.id).text(m.text);
                                            $select.append($opt);
                                        }
                                        if (m.id != null) $select.val(m.id).trigger('change');
                                        else {
                                            // if no id, attempt to find option by text and set its value
                                            const $byText = $select.find('option').filter((i,el) => $(el).text().trim() === m.text.trim());
                                            if ($byText.length) $select.val($byText.first().val()).trigger('change');
                                        }
                                    }
                                }
                                $suggestions.hide();
                            });
                            $suggestions.append($item);
                        });

                        // size and show
                        const wrapperWidth = $parent.length ? $parent.innerWidth() : $input.outerWidth();
                        $suggestions.css({ display: 'block', width: wrapperWidth + 'px' });
                    }

                    // events
                    $input.off('.searchable').on('input.searchable', function() {
                        render($(this).val() || '');
                    });

                    // show full list on focus/click
                    $input.off('.searchable-focus').on('focus.searchable', function() {
                        render('');
                    });

                    // keyboard: Enter selects first suggestion
                    $input.off('.searchable-key').on('keydown.searchable', function(e) {
                        if (e.key === 'Enter') {
                            const first = $suggestions.find('.list-group-item').first();
                            if (first.length) {
                                e.preventDefault();
                                first.trigger('click');
                            }
                        }
                    });

                    // hide on blur (allow clicks)
                    $input.off('.searchable-blur').on('blur.searchable', function() {
                        setTimeout(() => $suggestions.hide(), 150);
                    });
                }

        // Hide staff suggestions on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.staff-input, .staff-suggestions').length) {
                $('.staff-suggestions').hide();
            }
        });

        // Show suggestions when input focused (if user interacted)
        $(document).on('focus', '.staff-input', function(e) {
            const $input = $(this);
            // small delay to allow click events (if any)
            setTimeout(() => renderStaffSuggestions($input, $input.val() || ''), 50);
        });

        // Hide suggestions on blur (allow click)
        $(document).on('blur', '.staff-input', function() {
            const $input = $(this);
            setTimeout(() => {
                const id = $input.attr('id');
                if (id) $(`#${id}-suggestions`).hide();
            }, 150);
        });

        /* Removed conflicting print styles - using inline styles instead */

        .icon-btn {
            background: #ececec;
            border: 1.5px solid #ececec;
            border-radius: 8px;
            transition: background 0.2s, border 0.2s, box-shadow 0.2s;
        }

        // Staff inputs use plain dropdown selects; no autocomplete initialization required

        /* Keep the print modal visually white on-screen (restore default white background) */
        #printReportModal .modal-content,
        #printReportModal .modal-body,
        #printReportModal .modal-header,
        #printReportModal .modal-footer {
            background: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
        }

        .icon-btn:hover,
        .icon-btn:focus {
            background: #f8f9fa;
            border-color: #bdbdbd;
            box-shadow: 0 2px 8px rgba(53, 59, 72, 0.10);
        }

        .blink-badge {
            animation: blink-badge 1.2s infinite alternate;
        }

        @keyframes blink-badge {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        .actions-col {
            min-width: 180px;
            /* Adjust as needed */
        }

        .actions-col a {
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }

        .actions-col a:last-child {
            margin-right: 0;
        }

        .alert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading {
            position: relative;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .actions-col a {
            display: inline-flex;
            align-items: center;
            margin-right: 8px;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .actions-col a:hover {
            background-color: #f8f9fa;
        }

        .actions-col a span {
            margin-left: 4px;
            font-size: 12px;
        }

        .actions-col a.edit-report-completed {
            color: #28a745;
        }

        .actions-col a.edit-report-completed:hover {
            background-color: #e8f5e8;
        }

        .actions-col a.edit-report {
            color: #007bff;
        }

        .actions-col a.edit-report:hover {
            background-color: #e3f2fd;
        }

        .actions-col a.delete-report {
            color: #dc3545;
        }

        .actions-col a.delete-report:hover {
            background-color: #fde8e8;
        }

        /* New styles for the Part Used section */
        .part-used-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #ffffffff;
        }

        .part-used-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .parts-container-wrapper {
            margin-bottom: 15px;
        }

        .add-part-btn-container {
            display: flex;
            justify-content: flex-start;
            margin-top: 10px;
        }

        .section-border {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        /* Fix modal positioning */
        #serviceReportListModal .modal-dialog {
            margin: 1.75rem auto;
            max-height: 90vh;
            display: flex;
            align-items: center;
        }

        #serviceReportListModal .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        #serviceReportListModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        /* Service Report List Modal Header Styles */
        #serviceReportListModal .modal-header {
            background-color: #0066e6 !important;
        }

        #serviceReportListModal .modal-title {
            color: white !important;
        }

        /* support both bootstrap .close and our custom close-modal-report */
        #serviceReportListModal .close,
        #serviceReportListModal .close-modal-report {
            color: white !important;
            opacity: 1 !important;
            font-size: 1.8rem;
            background: none;
            border: none;
        }

        #serviceReportListModal .close:hover,
        #serviceReportListModal .close-modal-report:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Customer search suggestion styles */
        .customer-search-wrapper {
            position: relative;
        }
        .customer-suggestions {
            position: absolute;
            left: 0;
            top: calc(100% + 6px);
            width: 100%;
            z-index: 2000;
            box-shadow: 0 10px 30px rgba(50,50,93,0.12);
            border-radius: 8px;
            overflow: auto;
        }
        .customer-suggestions .list-group-item {
            cursor: pointer;
            background-color: #ffffff !important; /* force white background */
            color: #212529 !important; /* dark text */
            border: none; /* cleaner look */
        }
        .customer-suggestions .list-group-item:hover,
        .customer-suggestions .list-group-item:focus,
        .customer-suggestions .list-group-item.active {
            background-color: #f8f9fa !important; /* subtle hover */
            color: #212529 !important; /* ensure readable text */
        }

        /* Status Progress Indicator Styles */
        .status-progress-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .status-progress-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-bar-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .progress-step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .progress-step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-bottom: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .progress-step-number.inactive {
            background-color: #e0e0e0;
            color: #666;
        }

        .progress-step-number.active {
            background-color: #ffc107;
            color: #000;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3);
        }

        .progress-step-number.completed {
            background-color: #28a745;
            color: white;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }

        .progress-step-label {
            font-size: 12px;
            font-weight: 500;
            color: #666;
            text-align: center;
            max-width: 80px;
        }

        .progress-step-label.active {
            color: #ffc107;
            font-weight: 600;
        }

        .progress-step-label.completed {
            color: #28a745;
            font-weight: 600;
        }

        .progress-connector {
            flex: 1;
            height: 3px;
            background-color: #e0e0e0;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .progress-connector.active,
        .progress-connector.completed {
            background-color: #28a745;
        }

        .status-timeline {
            margin-top: 15px;
            padding: 12px;
            background-color: #ffffff;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #007bff;
            margin-right: 10px;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .timeline-text {
            flex: 1;
        }

        .timeline-text strong {
            color: #333;
        }

        .timeline-text span {
            color: #666;
        }

        .status-dropdown-toggle {
            cursor: pointer;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .status-dropdown-toggle:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .status-dropdown-toggle.collapsed::after {
            content: '▼';
            display: inline-block;
            transition: transform 0.2s;
        }

        .status-dropdown-toggle:not(.collapsed)::after {
            content: '▲';
            display: inline-block;
            transition: transform 0.2s;
        }

        /* Comment Styles */
        .timeline-comment-section {
            margin-top: 15px;
            padding: 12px;
            background-color: #f0f8ff;
            border-radius: 6px;
            border-left: 4px solid #17a2b8;
        }

        .comment-btn {
            background-color: #17a2b8;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .comment-btn:hover {
            background-color: #138496;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
        }

        .comments-container {
            margin-top: 12px;
            max-height: 300px;
            overflow-y: auto;
        }

        .comment-item {
            background-color: #ffffff;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 3px solid #17a2b8;
        }

        .comment-item:last-child {
            margin-bottom: 0;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .comment-author {
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }

        .comment-time {
            font-size: 11px;
            color: #999;
        }

        .comment-text {
            font-size: 12px;
            color: #555;
            line-height: 1.4;
            word-break: break-word;
        }

        .no-comments {
            font-size: 12px;
            color: #999;
            font-style: italic;
            padding: 8px;
            text-align: center;
        }

        .progress-comments-list {
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .progress-timeline-content {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px 0;
        }

    </style>
</head>

<body>
    <div class="wrapper">
        <div class="body-overlay"></div>

        <!-- Sidebar -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = 'Service Report';
            $breadcrumb = 'Service Report';
            include __DIR__ . '/../layout/navbar.php';
            ?>

            <!-- Main Content -->
            <div class="main-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Service Report Form</h5>
                                <button type="button" class="btn icon-btn position-relative p-2" data-toggle="modal" data-target="#serviceReportListModal">
                                    <i class="material-icons align-middle" style="font-size: 2em; color: #353b48;">list</i>
                                    <span id="report-badge" class="position-absolute blink-badge" style="display:none; top: 2px; right: 2px; width: 12px; height: 12px; background: #ff6b6b; border-radius: 50%;"></span>
                                </button>
                            </div>
                            <div class="card-body">
                                <form id="serviceReportForm" method="post" action="">
                                    <div class="container-fluid">
                                        <!-- First Row: Customer, Appliance, Date In, Status -->
                                        <div class="row mb-2">
                                            <div class="col-md-3">
                                                <label>Customer</label>
                                                <!-- <input type="text" class="form-control" name="customer" placeholder="Enter Name">-->
                                                <!-- Searchable customer input + hidden select to store id -->
                                                <div class="customer-search-wrapper" style="position: relative;">
                                                    <input type="text" id="customer-search" class="form-control" placeholder="Search customer by name" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off">
                                                    <select class="form-control customer-select d-none" name="customer" id="customer-select" aria-hidden="true" tabindex="-1">
                                                        <option value="">Select Customer</option>
                                                    </select>
                                                    <div id="customer-suggestions" class="list-group customer-suggestions" style="display:none; max-height: 260px; overflow-y: auto; position: absolute; left: 0; top: calc(100% + 6px); width: 100%; z-index: 2000;"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Appliance</label>
                                                <select class="form-control appliance-select" name="appliance" id="appliance-select">
                                                    <option>Select Appliance</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Date In</label>
                                                <input type="date" class="form-control" name="date_in" id="date-in" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Status</label>
                                                <select class="form-control" name="status" id="status-select" required>
                                                    <option value="">Select Status</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Under Repair">Under Repair</option>
                                                    <option value="Unrepairable">Unrepairable</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Status Progress Indicator -->
                                        <div id="status-progress-container" class="status-progress-container" style="display: none;">
                                            <div class="status-progress-title">Repair Progress</div>
                                            
                                            <!-- Main Progress Bar -->
                                            <div class="progress-bar-container">
                                                <!-- Pending Step -->
                                                <div class="progress-step">
                                                    <div class="progress-step-number inactive" id="step-1">1</div>
                                                    <div class="progress-step-label" id="step-1-label">Pending</div>
                                                </div>
                                                
                                                <!-- Connector 1 -->
                                                <div class="progress-connector inactive" id="connector-1"></div>
                                                
                                                <!-- Under Repair Step -->
                                                <div class="progress-step">
                                                    <div class="progress-step-number inactive" id="step-2">2</div>
                                                    <div class="progress-step-label" id="step-2-label">Under Repair</div>
                                                </div>
                                                
                                                <!-- Connector 2 -->
                                                <div class="progress-connector inactive" id="connector-2"></div>
                                                
                                                <!-- Completed Step -->
                                                <div class="progress-step">
                                                    <div class="progress-step-number inactive" id="step-3">3</div>
                                                    <div class="progress-step-label" id="step-3-label">Completed</div>
                                                </div>
                                            </div>

                                            <!-- Status Timeline with Dropdown -->
                                            <div style="text-align: center; margin-top: 10px;">
                                                <a href="#" class="status-dropdown-toggle collapsed" id="status-timeline-toggle" data-toggle="collapse" data-target="#status-timeline-content">
                                                    View Progress Timeline
                                                </a>
                                            </div>

                                            <div id="status-timeline-content" class="collapse">
                                                <div class="status-timeline">
                                                    <div class="progress-timeline-content" id="progress-timeline-items">
                                                        <div class="timeline-item">
                                                            <div class="timeline-dot"></div>
                                                            <div class="timeline-text">
                                                                <strong>Status Created</strong><br>
                                                                <span id="timeline-created-date">Not yet created</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Second Row: Dealer, DOP, Date Pulled-Out, Service Type -->
                                        <div class="row mb-2">
                                            <div class="col-md-3">
                                                <label>Dealer</label>
                                                <input type="text" class="form-control" name="dealer">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Date of Purchase</label>
                                                <input type="date" class="form-control" name="dop">
                                            </div>
                                        </div>
                                        <div class="row mb-0 align-items-end">
                                            <div class="col-md-7">
                                                <label>Findings</label>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <label class="form-label p-0 m-0 w-100" style="font-weight: normal; font-size: 0.9rem;">Shop</label>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">Field</label>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">In WTY</label>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">Out WTY</label>
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-md-7">
                                                <input type="text" class="form-control" name="findings">
                                            </div>
                                            <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="shop" id="shop" style="width: 1.4em; height: 1.4em;">
                                            </div>
                                            <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="field" id="field" style="width: 1.4em; height: 1.4em;">
                                            </div>
                                            <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="in_wty" id="in_wty" style="width: 1.4em; height: 1.4em;">
                                            </div>
                                            <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                                <input class="form-check-input" type="checkbox" name="out_wty" id="out_wty" style="width: 1.4em; height: 1.4em;">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-9">
                                                <label>Remarks</label>
                                                <input type="text" class="form-control" name="remarks">
                                            </div>
                                        </div>
                                        
                                        <!-- Part Used Section with Border -->
                                        <div class="part-used-section">
                                            <div class="row mb-2 pl-3">
                                               <button type="button" class="btn btn-primary add-part d-flex align-items-center justify-content-center" id="add-part">
                                                    <i class="material-icons me-1">add</i> Add Part
                                                </button>
                                            </div>
                                            
                                            <!-- Parts Container for dynamic rows -->
                                            <div id="parts-container" class="parts-container-wrapper">
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
                                                    <div class="col-md-1 px-1 d-flex justify-content-end align-items-center ml-3">
                                                        <button type="button" class="btn btn-danger remove-part d-flex align-items-center justify-content-center" style="display:none;">
                                                            <span class="material-icons" style="font-size: 1.2em;">delete</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <!-- Checkboxes Row with Border -->
                                        
                                            <div class="row mb-1">
                                                <div class="col-md-12">
                                                    <div id="service-type-checkboxes" class="d-flex flex-row" style="gap: 1rem; flex-wrap:wrap;">
                                                        <!-- Dynamic service type checkboxes will be rendered here by JS -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Complaint with Date Repaired & Date Delivered (single responsive row) -->
                                        <div class="row mb-2 align-items-start">
                                            <div class="col-md-8 pe-1 d-flex flex-column">
                                                <!-- Total Amount (hidden, not used for form submission) -->
                                                <input type="hidden" id="total-amount" readonly>
                                                <label class="mb-1">Complaint</label>
                                                <textarea class="form-control" name="complaint" id="complaint" style="height:calc(2 * 38px + 8px);" aria-label="complaint"></textarea>
                                            </div>
                                            <div class="col-md-4 ps-1 d-flex flex-column" style="gap:8px;">
                                                <div>
                                                    <label class="mb-1">Date Repaired</label>
                                                    <input type="date" class="form-control" name="date_repaired">
                                                </div>
                                                <div>
                                                    <label class="mb-1">Date Delivered</label>
                                                    <input type="date" class="form-control" name="date_delivered">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Charged Details Section Header -->
                                        <div class="row mt-3 mb-1">
                                            <div class="col-md-12">
                                                <h5 class="fw-bold mb-1">Charged Details</h5>
                                            </div>
                                        </div>
                                       <!-- Charged Details Row -->
                                        <div class="row mb-2 align-items-end">
                                            <div class="col-md-3 pe-1">
                                                <label class="mb-1">Labor:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" name="labor" id="labor-amount" value="0.00" min="0" step="1.00">
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-1">
                                                <label class="mb-1">Pull-Out Delivery:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" name="pullout_delivery" id="pullout-delivery" value="0.00" min="0" step="1.00">
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-1">
                                                <label class="mb-1">Total:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" name="total_amount" id="total-amount-2" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3 ps-1">
                                                <label class="mb-1">Parts Charge:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control" name="parts_charge" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Signature Fields Row -->
                                        <div class="row mb-2">

                                            <div class="col-md-3 pe-1 d-flex flex-column justify-content-end">
                                                <label class="mb-1">Secretary:</label>
                                                <select name="receptionist" id="receptionist-select" class="form-control staff-select" data-role="Secretary">
                                                    <option value="">Receptionist</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                                <label class="mb-1">Manager:</label>
                                                <select name="manager" id="manager-select" class="form-control staff-select" data-role="Manager">
                                                    <option value="">Manager</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                                <label class="mb-1">Technician:</label>
                                                <select name="technician" id="technician-select" class="form-control staff-select" data-role="Technician">
                                                    <option value="">Technician</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 ps-1 d-flex flex-column justify-content-end">
                                                <label class="mb-1">Released By:</label>
                                                <select name="released_by" id="released-by-select" class="form-control staff-select" data-role="Secretary">
                                                    <option value="">Released By</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Buttons -->
                                        <div class="d-flex justify-content-end mt-4" style="gap: 1rem;">
                                            <button type="button" class="btn btn-secondary px-4" id="cancel-button">Cancel</button>
                                            <input type="hidden" name="report_id" id="report_id">
                                            <button type="submit" class="btn btn-primary px-4" id="submit-report-btn" style="background-color: #0066e6; border: none;">Create Report</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Edit Report Modal -->
                <div class="modal fade" id="editReportModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form>
                                <div class="modal-header">
                                    <h4 class="modal-title">Update Service Report</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="report_id" id="edit_report_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" id="edit_status" class="form-control" required>
                                                    <option value="">Select Status</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Under Repair">Under Repair</option>
                                                    <option value="Unrepairable">Unrepairable</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cost</label>
                                                <input type="number" name="cost" id="edit_cost" class="form-control" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date Repaired</label>
                                                <input type="date" name="date_repaired" id="edit_date_repaired" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date Out (Auto-filled when Completed)</label>
                                                <input type="date" name="date_pulled_out" id="edit_date_pulled_out" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date Delivered</label>
                                                <input type="date" name="date_delivered" id="edit_date_delivered" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Service Report List Modal -->
                <div class="modal fade" id="serviceReportListModal" tabindex="-1" aria-labelledby="serviceReportListModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1200px; width: 95%;">
                        <div class="modal-content" style="border-radius: 18px;">
                            <div class="modal-header justify-content-center" style="background-color: #0066e6;">
                                <h4 class="modal-title w-100 text-center text-white" id="serviceReportListModalLabel">Service Report List</h4>
                                <button type="button" class="close-modal-report position-absolute" style="right: 20px; top: 18px; color: white; background: none; border: none; cursor: pointer; font-size: 1.8rem; font-weight: 300; padding: 0;" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="px-3 py-2 border-bottom">
                                    <div class="row">
                                            <div class="col-md-6 d-flex align-items-center">
                                                <div class="input-group input-group-sm me-2" style="min-width: 240px;">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="serviceReportSearchIcon">🔍</span>
                                                    </div>
                                                    <input type="text" class="form-control form-control-sm" id="service-report-search" placeholder="Search reports by ID, customer, appliance, or type" aria-label="Search reports" aria-describedby="serviceReportSearchIcon" autocomplete="off">
                                                </div>
                                                <div>
                                                    <select id="service-report-filter" class="form-control form-control-sm" style="width: 160px;">
                                                        <option value="All">All</option>
                                                        <option value="Completed">Completed</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Under Repair">Under Repair</option>
                                                        <option value="Unrepairable">Unrepairable</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0" style="font-family: 'Poppins', sans-serif;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="white-space: nowrap;">Report ID</th>
                                                <th>Customer</th>
                                                <th>Appliance</th>
                                                <th>Service Type</th>
                                                <th>Date In</th>
                                                <th>Date Released Out</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!--Data are placed here-->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Comment Modal -->
    <div class="modal fade" id="progressCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #17a2b8; color: white;">
                    <h5 class="modal-title">
                        <span class="material-icons align-middle" style="font-size: 20px; margin-right: 8px;">add_comment</span>
                        Add Comment to <span id="progress-title-modal">Progress</span>
                    </h5>
                    <button type="button" class="close btn-close-white" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="progress-comment-text"><strong>Your Comment</strong></label>
                        <textarea class="form-control" id="progress-comment-text" rows="4" placeholder="Add a comment about this progress..."></textarea>
                        <small class="form-text text-muted">This comment will be displayed under the progress item.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-progress-comment-btn" onclick="saveProgressComment()">Save Comment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Report Modal -->
    <div class="modal fade" id="printReportModal" tabindex="-1" aria-labelledby="printReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 900px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printReportModalLabel">Print Service Report</h5>
                    <div class="ms-auto d-flex gap-2">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button id="print-report-btn" class="btn btn-primary">Print</button>
                    </div>
                </div>
                <div class="modal-body" style="max-height: 85vh; overflow-y: auto; padding: 0; background: #ffffff;">
                    <div id="print-report-body" class="print-section" style="background: #ffffff;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <!-- Select2 for searchable selects -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script type="text/javascript">
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

            // allow custom close button to hide the service report list modal
            $(document).on('click', '.close-modal-report', function(e) {
                e.preventDefault();
                $('#serviceReportListModal').modal('hide');
                setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 200);
            });

            // Use event delegation for action buttons; hide the list modal when an action is clicked
            $(document).on('click', '.edit-report', function(e) {
                e.preventDefault();
                const reportId = $(this).data('id');
                // hide the list modal immediately
                $('#serviceReportListModal').modal('hide');
                setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 300);
                loadReportForEditing(reportId);
            });

            $(document).on('click', '.delete-report', function(e) {
                e.preventDefault();
                const reportId = $(this).data('id');
                $('#serviceReportListModal').modal('hide');
                setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 300);
                deleteReport(reportId);
            });

            // Auto-fill date_pulled_out when status changes to "Completed"
            $(document).on('change', '#status-select, #edit_status', function() {
                const $statusSelect = $(this);
                const selectedStatus = $statusSelect.val();
                
                // Determine which date_pulled_out field to fill based on which status select changed
                let $datePulledOutField;
                if ($statusSelect.attr('id') === 'status-select') {
                    $datePulledOutField = $('input[name="date_pulled_out"]').not('#edit_date_pulled_out');
                } else {
                    $datePulledOutField = $('#edit_date_pulled_out');
                }
                
                if (selectedStatus === 'Completed') {
                    // Auto-fill with today's date
                    const today = new Date().toISOString().split('T')[0];
                    $datePulledOutField.val(today);
                    console.log('Auto-filled date_pulled_out with today\'s date:', today);
                } else {
                    // Do not clear the date if status is changed away from Completed
                    console.log('Status changed to:', selectedStatus);
                }
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
                    let status = report.status;
                    // Map numeric status codes to text
                    if (typeof status === 'number' || /^\d+$/.test(status)) {
                        const statusMap = {'0': 'Completed', '1': 'Pending', '2': 'Under Repair', '3': 'Unrepairable', '4': 'Release Out'};
                        status = statusMap[String(status)] || status;
                    }
                    if (status === 'Pending') {
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

        // Status Progress Functions
        function updateStatusProgress(status) {
            const container = $('#status-progress-container');
            if (!status) {
                container.hide();
                return;
            }

            container.show();

            // Define status order and steps
            const statusFlow = {
                'Pending': { step: 1, isCompleted: false },
                'Under Repair': { step: 2, isCompleted: false },
                'Completed': { step: 3, isCompleted: true },
                'Unrepairable': { step: 2, isCompleted: false, isAlternate: true },
                'Release Out': { step: 3, isCompleted: true, isAlternate: true }
            };

            const currentStatus = statusFlow[status] || { step: 0, isCompleted: false };

            // Update progress steps display
            updateProgressSteps(currentStatus.step, currentStatus.isCompleted);

            // Update timeline
            updateProgressTimeline(status);
        }

        function updateProgressSteps(currentStep, isCompleted) {
            // Reset all steps
            for (let i = 1; i <= 3; i++) {
                const $stepNumber = $(`#step-${i}`);
                const $stepLabel = $(`#step-${i}-label`);
                const $connector = $(`#connector-${i}`);

                $stepNumber.removeClass('active completed inactive');
                $stepLabel.removeClass('active completed');
                if ($connector.length) {
                    $connector.removeClass('active completed');
                }

                $stepNumber.addClass('inactive');
            }

            // Set completed steps
            for (let i = 1; i < currentStep; i++) {
                $(`#step-${i}`).removeClass('inactive').addClass('completed');
                $(`#step-${i}-label`).addClass('completed');
                if ($(`#connector-${i}`).length) {
                    $(`#connector-${i}`).removeClass('inactive').addClass('completed');
                }
            }

            // Set current step as active
            if (currentStep > 0 && currentStep <= 3) {
                $(`#step-${currentStep}`).removeClass('inactive').addClass('active');
                $(`#step-${currentStep}-label`).addClass('active');
            }

            // Set remaining steps
            for (let i = currentStep + 1; i <= 3; i++) {
                $(`#step-${i}`).removeClass('completed').addClass('inactive');
                $(`#step-${i}-label`).removeClass('completed');
            }
        }

        function updateProgressTimeline(status) {
            const timelineContainer = $('#progress-timeline-items');
            const currentDate = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            let timelineHTML = '';

            // Map statuses to timeline events
            const timelineEvents = {
                'Pending': {
                    title: 'Received for Service',
                    description: 'Awaiting repair technician',
                    key: 'pending'
                },
                'Under Repair': {
                    title: 'Under Repair',
                    description: 'Technician is working on the unit',
                    key: 'under_repair'
                },
                'Unrepairable': {
                    title: 'Unit is Unrepairable',
                    description: 'Unable to repair - marked as unrepairable',
                    key: 'unrepairable'
                },
                'Release Out': {
                    title: 'Released to Customer',
                    description: 'Unit has been released out',
                    key: 'release_out'
                },
                'Completed': {
                    title: 'Repair Completed',
                    description: 'Service completed and ready for delivery',
                    key: 'completed'
                }
            };

            const event = timelineEvents[status] || { title: 'Status Unknown', description: '', key: 'unknown' };

            // Current status item with comment section
            timelineHTML += `
                <div class="timeline-item" id="timeline-${event.key}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-text">
                        <strong>${event.title}</strong><br>
                        <span>${event.description}</span><br>
                        <small style="color: #999;">${currentDate}</small>
                        
                        <!-- Comment Button for this progress -->
                        <div style="margin-top: 10px;">
                            <button type="button" class="comment-btn" onclick="openProgressCommentModal('${event.key}', '${event.title}')">
                                <span class="material-icons" style="font-size: 14px;">add_comment</span>
                                <span>Comment</span>
                            </button>
                        </div>
                        
                        <!-- Comments Container for this progress -->
                        <div id="comments-${event.key}" class="progress-comments-list" style="margin-top: 10px;">
                            <div class="no-comments" style="font-size: 11px;">No comments</div>
                        </div>
                    </div>
                </div>
            `;

            // Add previous status tracking (if available from form)
            const statusSelect = $('select[name="status"]');
            const reportId = $('#report_id').val();
            if (reportId) {
                timelineHTML += `
                    <div class="timeline-item" id="timeline-report-created" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                        <div class="timeline-dot"></div>
                        <div class="timeline-text">
                            <strong>Report Created</strong><br>
                            <span>Service report initiated</span><br>
                            <small style="color: #999;">Report ID: #${reportId}</small>
                            
                            <!-- Comment Button for report creation -->
                            <div style="margin-top: 10px;">
                                <button type="button" class="comment-btn" onclick="openProgressCommentModal('report_created', 'Report Created')">
                                    <span class="material-icons" style="font-size: 14px;">add_comment</span>
                                    <span>Comment</span>
                                </button>
                            </div>
                            
                            <!-- Comments Container for report creation -->
                            <div id="comments-report_created" class="progress-comments-list" style="margin-top: 10px;">
                                <div class="no-comments" style="font-size: 11px;">No comments</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            timelineContainer.html(timelineHTML);
            
            // Refresh comments display for all items
            displayAllProgressComments();
        }

        function generateStatusProgressHTML(status) {
            // Define status order and steps
            const statusFlow = {
                'Pending': { step: 1, isCompleted: false },
                'Under Repair': { step: 2, isCompleted: false },
                'Completed': { step: 3, isCompleted: true },
                'Unrepairable': { step: 2, isCompleted: false, isAlternate: true },
                'Release Out': { step: 3, isCompleted: true, isAlternate: true }
            };

            const currentStatus = statusFlow[status] || { step: 0, isCompleted: false };
            let html = '<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">';

            // Define steps
            const steps = [
                { num: 1, label: 'Pending' },
                { num: 2, label: 'Under Repair' },
                { num: 3, label: 'Completed' }
            ];

            // Generate progress visualization
            steps.forEach((step, index) => {
                let stepColor = '#e0e0e0';
                let textColor = '#666';
                let fontWeight = 'normal';

                if (step.num < currentStatus.step) {
                    stepColor = '#28a745';
                    textColor = '#28a745';
                    fontWeight = 'bold';
                } else if (step.num === currentStatus.step) {
                    stepColor = '#ffc107';
                    textColor = '#ffc107';
                    fontWeight = 'bold';
                }

                // Step circle
                html += `
                    <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background-color: ${stepColor}; display: flex; align-items: center; justify-content: center; color: ${stepColor === '#28a745' ? 'white' : '#000'}; font-weight: bold; font-size: 11px; margin-bottom: 4px;">
                            ${step.num < currentStatus.step ? '✓' : step.num}
                        </div>
                        <span style="font-size: 10px; color: ${textColor}; font-weight: ${fontWeight}; text-align: center; max-width: 60px;">${step.label}</span>
                    </div>
                `;

                // Connector line
                if (index < steps.length - 1) {
                    let connectorColor = '#e0e0e0';
                    if (step.num < currentStatus.step) {
                        connectorColor = '#28a745';
                    }
                    html += `<div style="flex: 0.5; height: 2px; background-color: ${connectorColor}; margin-top: 10px; margin-bottom: 10px;"></div>`;
                }
            });

            html += '</div>';
            return html;
        }

        // Progress Comment Functions
        let progressComments = {};  // Format: { reportId: { progressKey: [comments] } }
        let currentProgressKey = null;
        let currentProgressTitle = null;

        function openProgressCommentModal(progressKey, progressTitle) {
            currentProgressKey = progressKey;
            currentProgressTitle = progressTitle;
            $('#progress-title-modal').text(progressTitle);
            $('#progress-comment-text').val('');
            $('#progressCommentModal').modal('show');
        }

        function saveProgressComment() {
            const commentText = $('#progress-comment-text').val().trim();
            const reportId = $('#report_id').val();

            if (!commentText) {
                showAlert('warning', 'Please enter a comment');
                return;
            }

            if (!reportId) {
                showAlert('warning', 'Please save the report first before adding comments');
                return;
            }

            if (!currentProgressKey) {
                showAlert('warning', 'Progress item not selected');
                return;
            }

            // Create comment object
            const comment = {
                id: Date.now(),
                text: commentText,
                timestamp: new Date().toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }),
                author: '<?php echo isset($userSession["name"]) ? htmlspecialchars($userSession["name"]) : "User"; ?>'
            };

            // Initialize storage if needed
            if (!progressComments[reportId]) {
                progressComments[reportId] = {};
            }
            if (!progressComments[reportId][currentProgressKey]) {
                progressComments[reportId][currentProgressKey] = [];
            }

            // Add comment
            progressComments[reportId][currentProgressKey].push(comment);

            // Update display for this progress item
            displayProgressItemComments(reportId, currentProgressKey);

            // Close modal and show success message
            $('#progressCommentModal').modal('hide');
            showAlert('success', `Comment added to ${currentProgressTitle}!`);
        }

        function displayProgressItemComments(reportId, progressKey) {
            const container = $(`#comments-${progressKey}`);
            const comments = (progressComments[reportId] && progressComments[reportId][progressKey]) || [];

            if (comments.length === 0) {
                container.html('<div class="no-comments" style="font-size: 11px;">No comments</div>');
                return;
            }

            let html = '';
            comments.forEach(comment => {
                html += `
                    <div class="comment-item" style="margin-bottom: 8px;">
                        <div class="comment-header">
                            <span class="comment-author" style="font-size: 11px;">${comment.author}</span>
                            <span class="comment-time" style="font-size: 10px;">${comment.timestamp}</span>
                        </div>
                        <div class="comment-text" style="font-size: 11px; margin-top: 4px;">${comment.text}</div>
                    </div>
                `;
            });

            container.html(html);
        }

        function displayAllProgressComments() {
            const reportId = $('#report_id').val();
            if (!reportId || !progressComments[reportId]) return;

            Object.keys(progressComments[reportId]).forEach(progressKey => {
                displayProgressItemComments(reportId, progressKey);
            });
        }

        function loadProgressComments(reportId) {
            // Load comments from storage (if implemented with database)
            // For now, just display existing comments in memory
            displayAllProgressComments();
        }

        async function initializeServiceReport() {
            try {
                await loadServicePrices();
                bindEventHandlers();
                initCustomerSearch();
                initStaffSearch();
                await loadInitialData();
            } catch (error) {
                console.error("Initialization failed: ", error);
                showAlert('danger', 'Failed to initialize application: ' + error.message);
            }
        }

        function bindEventHandlers() {
            $('#serviceReportForm').on('submit', handleFormSubmit);

            // Status change handler - update progress
            $('select[name="status"]').on('change', function() {
                const status = $(this).val();
                updateStatusProgress(status);
                
                // Auto-fill date_pulled_out when status changes to 'Completed'
                if (status === 'Completed') {
                    const today = new Date().toISOString().split('T')[0];
                    // Note: Date input field has been removed from form, but auto-fill logic preserved
                    // for potential future use or if field is restored
                }
                
                const reportId = $('#report_id').val();
                if (reportId) {
                    updateSubmitButton(status, reportId);
                }
            });

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

            // dynamic service checkboxes (rendered after loadServicePrices)
            $(document).on('change', '.service-type-checkbox', calculateTotals);
            $('#labor-amount').on('input', calculateTotals);
            $('#pullout-delivery').on('input', calculateTotals);

            $('#cancel-button').click(resetForm);

            $('#serviceReportListModal').on('show.bs.modal', loadServiceReports);

            // Service report list search input
            $('#service-report-search').on('input', function() {
                filterServiceReports($(this).val());
            });

            $('#service-report-filter').on('change', function() {
                // Re-render based on the selected status filter and current search query
                const q = $('#service-report-search').val();
                applyStatusAndSearch();
                if (q) {
                    filterServiceReports(q);
                }
            });

            // Print report button within the list - hide list then load and show print modal
            $(document).on('click', '.print-report', async function(e) {
                e.preventDefault();
                const reportId = $(this).data('id');
                if (!reportId) return;
                // hide the list modal immediately
                $('#serviceReportListModal').modal('hide');
                setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 300);
                await renderPrintModal(reportId);
                $('#printReportModal').modal('show');
            });

            // Print button inside modal
            $(document).on('click', '#print-report-btn', function() {
                window.print();
            });

                // Modal on-screen transparency handlers removed - keep modal white on-screen
                // (Previously saved/restored styles to force transparency; no longer needed.)

            // Ensure modal closes properly when clicking close button or backdrop
            $('#serviceReportListModal').on('hidden.bs.modal', function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                // Clear search input and re-render full list
                $('#service-report-search').val('');
                filterServiceReports('');
            });

            // Staff input: listen for typing and sync to hidden select
            $(document).on('input', '.staff-input', function() {
                const $input = $(this);
                const targetSelector = $input.data('target');
                if (!targetSelector) return;
                const $select = $(targetSelector);
                const val = ($input.val() || '').trim();

                if (!val) {
                    $select.val('');
                    return;
                }

                // Do NOT auto-select any name on input
                // Only show suggestions, selection happens on click or Enter
                $select.val('');
                try {
                    renderStaffSuggestions($input, val);
                } catch (err) {
                    // no-op
                }
            });

            // Sync select -> input so programmatic setDropdownValue updates visible input
            $(document).on('change', '.staff-select', function() {
                const $sel = $(this);
                const selId = $sel.attr('id') || '';
                const inputSelector = `#${selId.replace('-select', '-input')}`;
                const text = $sel.find('option:selected').text() || '';
                $(inputSelector).val(text);
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
                const message = error.responseJSON?.message || error.responseText || error.statusText || 'API request failed';
                throw new Error(message);
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
                
                // Handle duplicate record error (HTTP 409 Conflict)
                if (error.status === 409 && error.responseJSON) {
                    const duplicateData = error.responseJSON.data || {};
                    const existingReportId = duplicateData.existing_report_id;
                    const errorMsg = error.responseJSON.message || 'This service report already exists';
                    
                    // Create a user-friendly notification for duplicate
                    const fullMessage = `${errorMsg}\n\nClick "View Existing" to view the existing record instead of creating a duplicate.`;
                    
                    // Show a warning alert with action buttons
                    showAlert('warning', `<strong>Duplicate Record Detected</strong><br>${errorMsg}`);
                    
                    // Log for debugging
                    console.warn('Duplicate service report detected:', {
                        existingReportId: existingReportId,
                        message: errorMsg
                    });
                    
                    // Create a custom error object to identify this as a duplicate error
                    const duplicateError = new Error(errorMsg);
                    duplicateError.isDuplicate = true;
                    duplicateError.existingReportId = existingReportId;
                    throw duplicateError;
                }
                
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
                customer_id: $('#customer-select').val() ? parseInt($('#customer-select').val()) : null,
                appliance_name: $('#appliance-select option:selected').text() || '',
                appliance_id: $('#appliance-select').val() ? parseInt($('#appliance-select').val()) : null,
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
                service_charge: 0,
                total_amount: parseFloat($('#total-amount-2').val()) || 0,
                receptionist: ($('#receptionist-input').length && $('#receptionist-input').is(':visible') ? $('#receptionist-input').val().trim() : ($('#receptionist-select option:selected').text() || '')),
                manager: ($('#manager-input').length && $('#manager-input').is(':visible') ? $('#manager-input').val().trim() : ($('#manager-select option:selected').text() || '')),
                technician: ($('#technician-input').length && $('#technician-input').is(':visible') ? $('#technician-input').val().trim() : ($('#technician-select option:selected').text() || '')),
                released_by: ($('#released-by-input').length && $('#released-by-input').is(':visible') ? $('#released-by-input').val().trim() : ($('#released-by-select option:selected').text() || '')),

                parts: []
            };
            
            // Add optional date fields only if they have valid values
            const dateRepairedVal = formatDateForPHP($('input[name="date_repaired"]').val());
            const dateDeliveredVal = formatDateForPHP($('input[name="date_delivered"]').val());
            
            if (dateRepairedVal) formData.date_repaired = dateRepairedVal;
            if (dateDeliveredVal) formData.date_delivered = dateDeliveredVal;

            if ($('#shop').is(':checked')) formData.location.push('shop');
            if ($('#field').is(':checked')) formData.location.push('field');
            if ($('#out_wty').is(':checked')) formData.location.push('out_wty');

            // Collect all dynamic selected service types
            $('.service-type-checkbox:checked').each(function() {
                const serviceName = $(this).val();
                if (serviceName) formData.service_types.push(serviceName);
            });

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

            // Add optional date fields only if provided (prevent sending empty/null keys)
            const dopVal = formatDateForPHP($('input[name="dop"]').val());
            const datePulledVal = formatDateForPHP($('input[name="date_pulled_out"]').val());
            if (dopVal) formData.dop = dopVal;
            if (datePulledVal) formData.date_pulled_out = datePulledVal;
            
            console.log('Admin form data before sending:', formData);
            
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
                if (reportId && typeof updateReportInCache === 'function') {
                    updateReportInCache(reportId, formData);
                }

                if(!reportId && (response.data?.report_id || response.data?.ReportID)) {
                    $('#report_id').val(response.data.report_id || response.data.ReportID);
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
                await refreshServiceReports();
                
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
                
                // Open modal so the user can see the updated report list (only for new reports)
                if (!reportId) {
                    $('#serviceReportListModal').modal('show');
                }

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
                        // Clear date-in when no appliance/customer selected
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
                                date_in: item.date_in || '',
                                serial: item.serial_no || ''
                            });
                            dependent = true;
                        } else {
                            //independent appliance loading(show all appliance)
                            url = CUSTOMER_APPLIANCE_API_URL + '?action=getAllAppliances';

                            transformFn = item => ({
                                value: item.appliance_id,
                                text: `${item.brand} - ${item.serial_no || item.model_no || 'No Serial'} (${item.category || ''})`,
                                date_in: item.date_in || '',
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
                            // Deduplicate items by part_no/name for parts dropdown to prevent showing same part multiple times
                            const seenNames = new Set();
                            const dedupedItems = items.filter(item => {
                                // For parts, deduplicate by part_no (name) to avoid showing duplicates with same name
                                // For other dropdowns, use ID
                                let dedupeKey;
                                if (type === 'parts') {
                                    dedupeKey = (item.part_no || '').toLowerCase().trim();
                                } else {
                                    dedupeKey = item.part_id || item.appliance_id || item.staff_id || item.customer_id;
                                }
                                
                                if (seenNames.has(dedupeKey)) {
                                    return false;
                                }
                                seenNames.add(dedupeKey);
                                return true;
                            });

                            dedupedItems.forEach(item => {
                                const optionData = transformFn(item);
                                const $option = $('<option></option>')
                                    .val(optionData.value)
                                    .text(optionData.text)
                                    .attr('data-date-in', optionData.date_in || '')
                                    .attr('data-serial', optionData.serial || '');

                                if (optionData.price) {
                                    $option.data('price', optionData.price);
                                }
                                $dropdown.append($option);
                            });

                        // Store customers list globally for the search input
                        if (type === 'customer') {
                            // Populate global customers list but do not show suggestions by default
                            // Suggestions should only appear when the user taps/clicks the input.
                            let customers = items.map(item => ({
                                id: item.customer_id,
                                name: item.FullName
                            }));
                            // Dedupe by name (case-insensitive) to avoid duplicate names in suggestions
                            const seen = new Set();
                            window.customersList = customers.filter(c => {
                                const key = (c.name || '').toLowerCase().trim();
                                if (seen.has(key)) return false;
                                seen.add(key);
                                return true;
                            });
                        }

                        // If loading staff dropdown, also populate the matching datalist and suggestion data for the visible input
                        if (type === 'staff') {
                            try {
                                const selectId = $dropdown.attr('id') || '';
                                const inputId = selectId.replace('-select', '-input');
                                const listId = inputId ? `${inputId}-list` : '';

                                // Ensure datalist exists in DOM (some were added server-side in HTML); create near the input if missing
                                if (inputId && !$(`#${listId}`).length) {
                                    const $input = $(`#${inputId}`);
                                    if ($input.length) {
                                        $input.after(`<datalist id="${listId}"></datalist>`);
                                    }
                                }

                                const $dlist = listId ? $(`#${listId}`) : $();
                                // Build an in-memory staff list for richer suggestions (per select)
                                window.staffLists = window.staffLists || {};
                                const staffArray = items.map(item => {
                                    const optionData = transformFn(item);
                                    return {
                                        id: optionData.value,
                                        text: optionData.text,
                                        role: item.role || ''
                                    };
                                });
                                if (selectId) {
                                    window.staffLists[selectId] = staffArray;

                                    // Debug: log loaded staff list summary for troubleshooting
                                    try {
                                        console.debug && console.debug('Loaded staff list for', selectId, 'count=', (staffArray && staffArray.length) || 0, staffArray && staffArray.slice ? staffArray.slice(0,5) : staffArray);
                                    } catch (e) {
                                        /* ignore logging errors */
                                    }

                                    // Trigger a global event so any searchable input can re-render suggestions
                                    try {
                                        $(document).trigger('staffListLoaded', [selectId, staffArray]);
                                    } catch (e) { /* ignore */ }
                                }

                                if ($dlist.length) {
                                    $dlist.empty();
                                    staffArray.forEach(st => {
                                        $dlist.append(`<option value="${st.text}"></option>`);
                                    });
                                }

                                // Ensure a suggestion container exists inside the input's parent for richer UI
                                if (inputId) {
                                    const suggestionsId = `${inputId}-suggestions`;
                                    const $input = $(`#${inputId}`);
                                    const $parent = $input.parent();
                                    if ($parent.length && $parent.css('position') === 'static') {
                                        $parent.css('position', 'relative');
                                    }
                                    if ($input.length && $(`#${suggestionsId}`).length === 0) {
                                        $parent.append(`<div id="${suggestionsId}" class="staff-suggestions list-group" style="display:none; max-height:220px; overflow-y:auto; position:absolute; left:0; top:calc(100% + 6px); z-index:2000; width:100%;"></div>`);
                                    }
                                }
                            } catch (err) {
                                console.warn('Failed to populate staff datalist/suggestions:', err);
                            }
                        }
                        }

                        //enable/disable based on dependency and content
                        if (type === 'appliance') {
                            $dropdown.prop('disabled', !customerId || items.length === 0);
                            // If only one appliance is available for the selected customer, auto-select it
                            if (items.length === 1 && customerId) {
                                const onlyOptionVal = $dropdown.find('option:not([value=""])').first().val();
                                if (onlyOptionVal) {
                                    $dropdown.val(onlyOptionVal).trigger('change');
                                }
                            }
                            // Attach event to populate date-in when an appliance is selected
                            $dropdown.off('change.autoDate').on('change.autoDate', function() {
                                const selected = $(this).find('option:selected');
                                const dateIn = selected.attr('data-date-in') || selected.data('dateIn') || '';
                                if (dateIn) {
                                    $('#date-in').val(formatDateForInput(dateIn));
                                } else {
                                    $('#date-in').val('');
                                }
                            });
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
                console.log('Staff Fields - Receptionist:', report.receptionist, 'Manager:', report.manager, 'Technician:', report.technician, 'Released By:', report.released_by);

                resetForm();

                //basic report info
                $('#report_id').val(report.report_id);
                $('#date-in').val(report.date_in);
                $('select[name="status"]').val(report.status);
                updateStatusProgress(report.status);  // Update status progress display
                loadProgressComments(report.report_id);  // Load progress comments
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
                // Clear all existing dynamic checkboxes, then set checked based on report.service_types
                $('.service-type-checkbox').prop('checked', false);
                if (serviceTypes && serviceTypes.length > 0) {
                    for (const st of serviceTypes) {
                        const $checkbox = $(`#service-type-checkboxes input[type=checkbox][value="${st}"]`);
                        if ($checkbox.length) {
                            $checkbox.prop('checked', true);
                        } else {
                            // If a service type from the report does not exist in current list (custom or removed), add it
                            const labelText = st.charAt(0).toUpperCase() + st.slice(1);
                            const $newCheckbox = $(`<div class="form-check"><input class="form-check-input service-type-checkbox" type="checkbox" value="${st}" data-price="0"><label class="form-check-label">${labelText}</label></div>`);
                            $('#service-type-checkboxes').append($newCheckbox);
                            $newCheckbox.find('input').prop('checked', true);
                        }
                    }
                }

                //numeric fields
                $('#labor-amount').val(report.labor || '0.00');
                $('#pullout-delivery').val(report.pullout_delivery || '0.00');
                // total service charge UI removed - skipping service_charge display
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
                // Trigger change so the visible search input updates but do NOT open suggestions
                $('#customer-select').trigger('change');
                if (report.customer_name) {
                    const customerVal = $('#customer-select').val();
                    if (customerVal) {
                        await loadDropdown('appliance', '.appliance-select', customerVal);
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }
                }
                setDropdownValue('#appliance-select', report.appliance_name);
                // Ensure we trigger change so date-in populates from selected option
                $('#appliance-select').trigger('change');

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
                
                // FIX: Properly close the modal with better handling
                $('#serviceReportListModal').modal('hide');
                // Remove backdrop and reset body classes
                setTimeout(() => {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }, 500);

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

        // Customer search helpers
        function initCustomerSearch() {
            const $input = $('#customer-search');
            const $hiddenSelect = $('#customer-select');
            const $suggestions = $('#customer-suggestions');

            if (!$input.length) return;

            $suggestions.hide();

            let allowSuggestionsOnFocus = false;
            $input.on('input', function() {
                const text = $(this).val().trim();
                renderCustomerSuggestions(text);
            });

            $input.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = $suggestions.find('.list-group-item').first();
                    if (first.length) {
                        first.trigger('click');
                    }
                }
            });

            // Only show suggestions if the user physically interacted with the input
            // Support pointerdown, touchstart and mousedown for broad device coverage
            $input.on('pointerdown touchstart mousedown', function() {
                allowSuggestionsOnFocus = true;
            });

            $input.on('focus', function() {
                // Only show suggestions on focus if the user actually clicked the input
                if (allowSuggestionsOnFocus) {
                    renderCustomerSuggestions('');
                }
                allowSuggestionsOnFocus = false;
            });

            $hiddenSelect.on('change', function() {
                const text = $(this).find('option:selected').text();
                if (text && text !== 'Select Customer') {
                    $input.val(text);
                }
            });

            $input.on('blur', function() {
                setTimeout(() => $suggestions.hide(), 150);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#customer-search, #customer-suggestions').length) {
                    $suggestions.hide();
                }
            });
        }

        // Initialize staff search behavior (same UX as customer suggestions)
        function initStaffSearch() {
                const $inputs = $('.staff-input');
                if (!$inputs.length) return;

                $inputs.each(function() {
                    const $input = $(this);
                    const selectSelector = $input.data('target') || (`#${$input.attr('id').replace('-input','-select')}`);
                    const $hiddenSelect = $(selectSelector);

                    // Ensure suggestion container exists
                    const suggestionsId = `${$input.attr('id')}-suggestions`;
                    if ($input.parent().find(`#${suggestionsId}`).length === 0) {
                        $input.parent().append(`<div id="${suggestionsId}" class="list-group staff-suggestions" style="display:none; max-height:220px; overflow-y:auto; position:absolute; left:0; top:calc(100% + 6px); z-index:2000; width:100%;"></div>`);
                    }

                    $input.on('input', function() {
                        const text = $(this).val().trim();
                        renderStaffSuggestions($(this), text);
                    });

                    $input.on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const sugg = $(`#${suggestionsId}`).find('.list-group-item').first();
                            if (sugg.length) sugg.trigger('click');
                        }
                    });

                    // Always show suggestions on focus
                    $input.on('focus', function() {
                        renderStaffSuggestions($input, $input.val() || '');
                    });

                    $hiddenSelect.on('change', function() {
                        const text = $(this).find('option:selected').text();
                        if (text && text !== 'Select staff') {
                            $input.val(text);
                        }
                    });

                    $input.on('blur', function() {
                        setTimeout(() => $(`#${suggestionsId}`).hide(), 150);
                    });
                });

                // Ensure suggestions hide when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.staff-input, .staff-suggestions').length) {
                        $('.staff-suggestions').hide();
                    }
                });
        }

        function renderCustomerSuggestions(filterText) {
            const $suggestions = $('#customer-suggestions');
            const $input = $('#customer-search');

            if (!window.customersList || window.customersList.length === 0) {
                $suggestions.hide();
                return;
            }

            const text = (filterText || '').toLowerCase();
            let matches;
            if (!text) {
                // No filter: show top 20 customers
                matches = window.customersList.slice(0, 20);
            } else {
                // Start-with filtering
                matches = window.customersList.filter(c => c.name && c.name.toLowerCase().startsWith(text)).slice(0, 20);
            }

            if (matches.length === 0) {
                $suggestions.hide();
                return;
            }

            $suggestions.empty();
            matches.forEach(c => {
                const $item = $(`<button type="button" class="list-group-item list-group-item-action">${c.name}</button>`);
                $item.data('id', c.id);
                $item.on('click', function() {
                    setCustomerFromSuggestion(c.id, c.name);
                });
                $suggestions.append($item);
            });

            const $wrapper = $input.closest('.customer-search-wrapper');
            const wrapperWidth = $wrapper.length ? $wrapper.innerWidth() : $input.outerWidth();
            $suggestions.css({
                display: 'block',
                width: wrapperWidth + 'px'
            });
        }

        function setCustomerFromSuggestion(id, name) {
            const $hiddenSelect = $('#customer-select');
            const $input = $('#customer-search');
            
            console.log('Setting customer from suggestion - ID:', id, 'Name:', name);
            
            // Update the visible input field
            $input.val(name);
            console.log('Updated #customer-search to:', name);
            
            // Update or create option in hidden select
            let $option = $hiddenSelect.find(`option[value="${id}"]`);
            if ($option.length === 0) {
                console.log('Creating new option for customer ID:', id);
                $option = $(`<option></option>`).val(id).text(name);
                $hiddenSelect.append($option);
            }
            
            // Set the value and trigger change event
            $hiddenSelect.val(id);
            console.log('Set #customer-select value to:', id);
            console.log('Customer options in select:', $hiddenSelect.find('option').map((i, el) => $(el).val() + ':' + $(el).text()).get());
            
            // Trigger change to load appliances and other details
            $hiddenSelect.trigger('change');
            console.log('Triggered change event on #customer-select');
            
            // Hide suggestions
            $('#customer-suggestions').hide();
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

        function renderServiceReportsRows(reports) {
            const $tbody = $('#serviceReportListModal tbody').empty();
            // store the currently displayed reports in a global variable for search
            window.currentServiceReports = reports;
            if (!reports || !Array.isArray(reports)) return;

            reports.forEach(report => {
                let serviceTypes = 'N/A';
                if (report.service_types && Array.isArray(report.service_types)) {
                    serviceTypes = report.service_types.join(', ');
                } else if (typeof report.service_types === 'string') {
                    serviceTypes = report.service_types;
                }

                const dateIn = report.date_in ? new Date(report.date_in).toLocaleDateString() : 'N/A';
                const dateReleasedOut = report.date_pulled_out ? new Date(report.date_pulled_out).toLocaleDateString() : '';
                
                // Map numeric status codes to text (for legacy data compatibility)
                let statusText = report.status;
                if (typeof statusText === 'number' || /^\d+$/.test(statusText)) {
                    const statusMap = {
                        '0': 'Completed',
                        '1': 'Pending',
                        '2': 'Under Repair',
                        '3': 'Unrepairable',
                        '4': 'Release Out'
                    };
                    statusText = statusMap[String(statusText)] || statusText;
                }
                
                let statusBadge = '';
                switch ((statusText || '').toString()) {
                    case 'Completed':
                        statusBadge = '<span class="badge badge-success">Completed</span>';
                        break;
                    case 'Pending':
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                        break;
                    case 'Under Repair':
                        statusBadge = '<span class="badge badge-primary">Under Repair</span>';
                        break;
                    case 'Unrepairable':
                        statusBadge = '<span class="badge badge-danger">Unrepairable</span>';
                        break;
                    case 'Release Out':
                        statusBadge = '<span class="badge badge-secondary">Release Out</span>';
                        break;
                    default:
                        statusBadge = `<span class="badge badge-light">${statusText || 'N/A'}</span>`;
                }

                const totalAmount = parseFloat(report.total_amount || 0);

                $tbody.append(`
                    <tr>
                        <td>${report.report_id}</td>
                        <td>${report.customer_name}</td>
                        <td>${report.appliance_name}</td>
                        <td>${serviceTypes}</td>
                        <td>${dateIn}</td>
                        <td>${dateReleasedOut}</td>
                        <td>${statusBadge}</td>
                        <td>${totalAmount.toFixed(2)}</td>
                        <td class="actions-col">
                            <a href="#" class="edit-report" data-id="${report.report_id}">
                                <i class="material-icons text-primary">edit</i>
                            </a>
                            <a href="#" class="delete-report" data-id="${report.report_id}">
                                <i class="material-icons text-danger">delete</i>
                            </a>
                            <a href="#" class="print-report" data-id="${report.report_id}" title="Print Report">
                                <i class="material-icons text-secondary">print</i>
                            </a>
                        </td>
                    </tr>
                `);
            });
        }

        async function loadServiceReports() {
            try {
                showLoading(true, '#serviceReportListModal .modal-body');

                const response = await callServiceAPI('getAll');
                if (!response.success || !response.data) {
                    throw new Error(response.message || 'No service reports found');
                }

                // Store the reports data globally for local filtering/search
                window.serviceReportsData = response.data;
                // Render according to the selected status filter (default: All)
                applyStatusAndSearch();
                updateBadgeStatus(window.serviceReportsData);
            } catch (error) {
                console.error("Failed to load service reports: ", error);
                showAlert('danger', 'Failed to load service reports: ' + error.message);
            } finally {
                showLoading(false, '#serviceReportListModal .modal-body');
            }
        }

        // Refresh reports without resetting filters
        async function refreshServiceReports() {
            try {
                const response = await callServiceAPI('getAll');
                if (!response.success || !response.data) {
                    console.error('Failed to refresh service reports');
                    return;
                }

                // Store the reports data globally
                window.serviceReportsData = response.data;
                // Re-apply current filters
                applyStatusAndSearch();
                updateBadgeStatus(window.serviceReportsData);
                console.log('Service reports refreshed successfully');
            } catch (error) {
                console.error("Failed to refresh service reports: ", error);
            }
        }

        // Optimistically update a report in the local cache
        function updateReportInCache(reportId, updatedData) {
            if (!window.serviceReportsData || !Array.isArray(window.serviceReportsData)) return;
            
            const index = window.serviceReportsData.findIndex(r => r.report_id == reportId);
            if (index !== -1) {
                // Merge updated data with existing report
                window.serviceReportsData[index] = { ...window.serviceReportsData[index], ...updatedData };
                console.log('Optimistically updated report in cache:', reportId);
                
                // Re-render with current filters
                applyStatusAndSearch();
                updateBadgeStatus(window.serviceReportsData);
            }
        }

        function filterServiceReports(query) {
            query = (query || '').toString().toLowerCase().trim();
            const baseList = Array.isArray(window.currentServiceReports) ? window.currentServiceReports : (Array.isArray(window.serviceReportsData) ? window.serviceReportsData : []);
            if (!query) {
                renderServiceReportsRows(baseList);
                return;
            }

            const filtered = baseList.filter(report => {
                const q = query;
                return (report.report_id && report.report_id.toString().includes(q)) ||
                    (report.customer_name && report.customer_name.toLowerCase().includes(q)) ||
                    (report.appliance_name && report.appliance_name.toLowerCase().includes(q)) ||
                    (report.service_types && (Array.isArray(report.service_types) ? report.service_types.join(', ').toLowerCase().includes(q) : (report.service_types || '').toLowerCase().includes(q)));
            });

            renderServiceReportsRows(filtered);
        }

        function applyStatusAndSearch() {
            const status = $('#service-report-filter').val() || 'All';
            if (!status || status === 'All') {
                window.currentServiceReports = Array.isArray(window.serviceReportsData) ? window.serviceReportsData.slice() : [];
            } else {
                window.currentServiceReports = Array.isArray(window.serviceReportsData) ? window.serviceReportsData.filter(r => {
                    let reportStatus = r.status;
                    // Map numeric status codes to text
                    if (typeof reportStatus === 'number' || /^\d+$/.test(reportStatus)) {
                        const statusMap = {'0': 'Completed', '1': 'Pending', '2': 'Under Repair', '3': 'Unrepairable', '4': 'Release Out'};
                        reportStatus = statusMap[String(reportStatus)] || reportStatus;
                    }
                    return reportStatus === status;
                }) : [];
            }
            renderServiceReportsRows(window.currentServiceReports);
        }

        function updateSubmitButton(status, reportId = '') {
            const $submitBtn = $('#submit-report-btn');

            if(status === 'Completed' && reportId) {
                $submitBtn.html('Submit Report');
                $submitBtn.removeClass('btn-primary').addClass('btn-success');

                $submitBtn.off('click').on('click', async function(e) {
                    e.preventDefault();
                    try {
                        // Ensure report is updated first (so status=Completed is saved)
                        const formData = gatherFormData();
                        await callServiceAPI('update', formData, reportId);
                        // Now create transaction
                        await createTransactionFromReport(reportId);
                    } catch (err) {
                        console.error('Error updating report and creating transaction: ', err);
                        showAlert('danger', 'Failed to update report or create transaction: ' + (err.message || err));
                    }
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
            // serviceCharge removed from totals (not calculated/displayed)
            const partsTotal = calculatePartsTotal();

            const grandTotal = (
                parseFloat(laborCharge.toFixed(2)) +
                parseFloat(deliveryCharge.toFixed(2)) +
                // service charge is no longer part of grand total
                parseFloat(partsTotal.toFixed(2))
            ).toFixed(2);

            $('input[name="parts_charge"]').val(partsTotal.toFixed(2));
            // service_charge display removed; no UI to update
            $('#total-amount').val(grandTotal);
            $('#total-amount-2').val(grandTotal);
        }

        let servicePrices = {};

        async function loadServicePrices() {
            try {
                // use frontend-friendly list to render checkboxes for dynamic service types
                const response = await $.ajax({
                    url: SERVICE_PRICE_API_URL + '?action=getAllForFrontend',
                    type: 'GET',
                    dataType: 'json'
                });

                if (response && response.success && Array.isArray(response.data)) {
                    // response.data is array of services {service_id, service_name, service_price}
                    servicePrices = {};
                    window.servicePricesList = response.data.map(s => {
                        // normalize and keep a map for price lookup
                        servicePrices[s.service_name] = parseFloat(s.service_price);
                        return s;
                    });
                    renderServiceTypeCheckboxes(window.servicePricesList);
                    console.log('Loaded service prices list: ', window.servicePricesList);
                } else {
                    // fallback: legacy object mapping
                    servicePrices = {
                        installation: 500,
                        repair: 300,
                        cleaning: 200,
                        checkup: 150
                    };
                    window.servicePricesList = Object.keys(servicePrices).map(k => ({ service_name: k, service_price: servicePrices[k] }));
                    renderServiceTypeCheckboxes(window.servicePricesList);
                    console.warn('Using fallback service prices');
                }

            } catch (error) {
                console.error('Failed to load service prices: ', error);

                // fallback to default prices 
                servicePrices = {
                    installation: 500,
                    repair: 300,
                    cleaning: 200,
                    checkup: 150
                };
                console.warn('Using fallback service prices');
            }
        }

        function renderServiceTypeCheckboxes(services) {
            const $container = $('#service-type-checkboxes');
            $container.empty();
            if (!Array.isArray(services) || services.length === 0) {
                $container.html('<div class="text-muted">No service types available</div>');
                return;
            }
            services.forEach(service => {
                const name = service.service_name || '';
                const label = name.charAt(0).toUpperCase() + name.slice(1);
                const price = parseFloat(service.service_price || 0) || 0;
                const id = `service-type-${name.replace(/\s+/g, '-')}`;
                const checkboxHtml = `
                    <div class="form-check mr-3 mb-1">
                        <input class="form-check-input service-type-checkbox" type="checkbox" id="${id}" value="${name}" data-price="${price}">
                        <label class="form-check-label" for="${id}">${label}</label>
                    </div>`;
                $container.append(checkboxHtml);
            });
        }

                // calculateServiceCharge removed - service charges are no longer part of the report totals

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
            const reportId = $('#report_id').val();
            $('#report_id').val('');
            
            // Clear comments for this report
            if (reportId && progressComments[reportId]) {
                delete progressComments[reportId];
            }
            
            // Clear visible customer search input
            $('#customer-search').val('');
        
            updateSubmitButton('', '');
            updateStatusProgress(''); // Reset status progress
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
                    const transactionsList = Array.isArray(checkResponse.data) ? checkResponse.data : (checkResponse.data.transactions || []);
                    const existingTransaction = transactionsList.find(t => t.report_id == reportId || t.report_id == reportId || t.reportId == reportId);
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

                // CHECK: Only create transaction if status is "Completed"
                if (reportData.status !== 'Completed') {
                    console.warn('⚠️ Transaction not created - Service report status is not Completed. Current status:', reportData.status);
                    showAlert('warning', 'Transaction can only be created for Completed service reports. Current status: ' + reportData.status);
                    return;
                }

                const totalAmt = parseFloat(reportData.total_amount || 0);
                if (typeof totalAmt !== 'number' || isNaN(totalAmt)) {
                    throw new Error('Invalid total amount for this report');
                }
                const transactionData = {
                    report_id: reportId,
                    customer_name: reportData.customer_name,
                    appliance_name: reportData.appliance_name,
                    total_amount: totalAmt,
                    service_types: reportData.service_types || [],
                    payment_status: 'Pending' 
                };

                console.log('✅ Creating transaction for COMPLETED report with data:', transactionData);

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
                    // Throw error with details from the API, if any
                    const errMsg = transactionResponse.message || 'Failed to create transaction';
                    throw new Error(errMsg);
                }

                    showAlert('success', 'Transaction created successfully');
                    // Update button after successful creation
                    updateSubmitButton('Completed', reportId);

            } catch (error) {
                console.error('Error creating transaction:', error);
                // Attempt to parse XHR-style error if available (for clarity in debugging)
                if (error && error.responseJSON && error.responseJSON.message) {
                    showAlert('danger', `Failed to create transaction: ${error.responseJSON.message}`);
                } else {
                    showAlert('danger', 'Failed to create transaction: ' + (error.message || error));
                }
                // Also log a friendly message in console for full request payload
                console.error('Transaction creation payload:', JSON.stringify(transactionData));
            } finally {
                showLoading(false, '#serviceReportListModal .modal-body');
            }
        }

        // Render print modal with report details as a screenshot
        async function renderPrintModal(reportId) {
            try {
                showLoading(true, '#printReportModal');
                
                const response = await callServiceAPI('getById', null, reportId);
                
                if (!response.success || !response.data) {
                    throw new Error(response.message || 'Failed to load report');
                }

                const report = response.data;
                
                // Create a temporary container to build the formatted report
                const tempContainer = document.createElement('div');
                tempContainer.id = 'temp-screenshot-container';
                tempContainer.style.position = 'absolute';
                tempContainer.style.left = '-9999px';
                tempContainer.style.top = '-9999px';
                tempContainer.style.width = '900px';
                tempContainer.style.background = '#ffffff';
                tempContainer.style.padding = '25px';
                tempContainer.style.fontSize = '13px';
                tempContainer.style.lineHeight = '1.8';
                tempContainer.style.fontFamily = 'Arial, sans-serif';
                tempContainer.style.color = '#000';
                
                // Format dates
                const dateIn = report.date_in ? new Date(report.date_in).toLocaleDateString() : '';
                const dop = report.dop ? new Date(report.dop).toLocaleDateString() : '';
                const datePulledOut = report.date_pulled_out ? new Date(report.date_pulled_out).toLocaleDateString() : '';
                const dateRepaired = report.date_repaired ? new Date(report.date_repaired).toLocaleDateString() : '';

                // Format service types
                let serviceTypes = '';
                if (report.service_types && Array.isArray(report.service_types)) {
                    serviceTypes = report.service_types.join(', ');
                } else if (typeof report.service_types === 'string') {
                    serviceTypes = report.service_types;
                }

                // Build parts list with table
                let partsHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;"><tr style="border-bottom: 2px solid #000;"><th style="text-align: left; padding: 6px; color: #000; font-weight: bold;">Part Name</th><th style="text-align: center; padding: 6px; color: #000; font-weight: bold;">Qty</th></tr>';
                if (report.parts && Array.isArray(report.parts)) {
                    report.parts.forEach(part => {
                        partsHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">${part.part_name || ''}</td><td style="text-align: center; padding: 6px; color: #000;">${part.quantity || 0}</td></tr>`;
                    });
                } else {
                    partsHtml += '<tr><td colspan="2" style="padding: 6px; color: #000;"></td></tr>';
                }
                partsHtml += '</table>';

                // Build charges breakdown with table
                let chargesHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;"><tr style="border-bottom: 2px solid #000;"><th style="text-align: left; padding: 6px; color: #000; font-weight: bold;">Description</th><th style="text-align: right; padding: 6px; color: #000; font-weight: bold;">Amount</th></tr>';
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Labor</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.labor || 0).toFixed(2)}</td></tr>`;
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Pull-Out/Delivery</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.pullout_delivery || 0).toFixed(2)}</td></tr>`;
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Parts Charge</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.parts_total_charge || 0).toFixed(2)}</td></tr>`;
                chargesHtml += '</table>';

                const printContentHtml = `
                    <div style="padding: 25px; font-size: 13px; line-height: 1.8; font-family: Arial, sans-serif; color: #000;">
                        <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: bold; color: #000;">SERVICE REPAIR REPORT</h2>
                            <p style="margin: 0; font-size: 12px; color: #000; font-weight: 600;">Service Report ID: #${report.report_id}</p>
                        </div>
                        
                        <!-- First Row: Info -->
                        <div style="margin-bottom: 15px; display: table; width: 100%; border-collapse: collapse;">
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Date In:</strong><br><span style="font-size: 12px; color: #000;">${dateIn}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Status:</strong><br><span style="font-size: 12px; color: #000;">${report.status || ''}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Dealer:</strong><br><span style="font-size: 12px; color: #000;">${report.dealer || ''}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Staff:</strong><br><span style="font-size: 12px; color: #000;">${report.staff_name || ''}</span>
                            </div>
                        </div>
                        
                        <!-- Customer & Appliance -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">CUSTOMER INFORMATION</h4>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Name:</strong> <span style="font-size: 12px; color: #000;">${report.customer_name || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Contact:</strong> <span style="font-size: 12px; color: #000;">${report.customer_contact || ''}</span>
                                </div>
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Appliance:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_name || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Model:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_model || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Serial:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_serial || ''}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Service Details -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">SERVICE INFORMATION</h4>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Service Type:</strong> <span style="font-size: 12px; color: #000;">${serviceTypes}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Findings:</strong> <span style="font-size: 12px; color: #000;">${report.findings || ''}</span>
                                </div>
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Date of Problem:</strong> <span style="font-size: 12px; color: #000;">${dop}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Date Pulled Out:</strong> <span style="font-size: 12px; color: #000;">${datePulledOut}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Date Repaired:</strong> <span style="font-size: 12px; color: #000;">${dateRepaired}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Complaint -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">COMPLAINT</h4>
                            <div style="padding: 10px; border: 1px solid #000; min-height: 40px; background: #ffffff; font-size: 12px; color: #000;">
                                ${report.complaint || ''}
                            </div>
                        </div>
                        
                        <!-- Parts Used -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">PARTS USED</h4>
                            <div style="padding: 10px; border: 1px solid #000; background: #ffffff;">
                                ${partsHtml}
                            </div>
                        </div>
                        
                        <!-- Charges -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">CHARGES BREAKDOWN</h4>
                            <div style="padding: 10px; border: 1px solid #000; background: #ffffff;">
                                ${chargesHtml}
                            </div>
                        </div>
                        
                        <!-- Total Amount -->
                        <div style="margin-bottom: 15px; background-color: #ffffff; padding: 15px; border: 3px solid #000; font-weight: bold; font-size: 16px; text-align: right; color: #000;">
                            TOTAL AMOUNT: ₱${parseFloat(report.total_amount || 0).toFixed(2)}
                        </div>
                        
                        <!-- Remarks -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">REMARKS</h4>
                            <div style="padding: 10px; border: 1px solid #000; min-height: 40px; background: #ffffff; font-size: 12px; color: #000;">
                                ${report.remarks || 'N/A'}
                            </div>
                        </div>
                        
                        <!-- Signature Area -->
                        <div style="margin-top: 20px; border-top: 2px solid #000; padding-top: 15px;">
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 33%; padding: 0 8px 0 0; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Technician</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                                <div style="display: table-cell; width: 33%; padding: 0 8px; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Manager</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                                <div style="display: table-cell; width: 33%; padding: 0 0 0 8px; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Released By</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                tempContainer.innerHTML = printContentHtml;
                document.body.appendChild(tempContainer);

                // Wait for images/content to load
                await new Promise(resolve => setTimeout(resolve, 500));

                // Capture the formatted report as a screenshot
                const canvas = await html2canvas(tempContainer, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false
                });
                
                // Convert canvas to image
                const screenshotImage = canvas.toDataURL('image/png');
                
                // Remove temporary container
                document.body.removeChild(tempContainer);
                
                // Inject the screenshot image into the print body
                const screenshotHtml = `
                    <div style="text-align: center; width: 100%; height: auto;">
                        <img src="${screenshotImage}" style="max-width: 100%; height: auto; border: none; display: block;" />
                    </div>
                `;
                
                $('#print-report-body').html(screenshotHtml);
                showLoading(false, '#printReportModal');

            } catch (error) {
                console.error('Error rendering print modal:', error);
                showLoading(false, '#printReportModal');
                showAlert('danger', error.message || 'Failed to load report for printing');
            }
        }
    </script>
</body>

</html> 