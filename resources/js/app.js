import html2canvas from 'html2canvas';
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import 'intl-tel-input/styles';

document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const modeButtons = [...document.querySelectorAll('[data-mode]')];
    const themeToggleButtons = [...document.querySelectorAll('[data-theme-toggle]')];

    initializeCustomersFilters();
    
    initializeJobOrdersFilters();

    const normalizeTheme = (value) => {
        const mode = String(value ?? '').trim().toLowerCase();
        return mode.includes('dark') ? 'dark' : 'light';
    };

    const applyTheme = (theme) => {
        html.classList.toggle('dark', theme === 'dark');
        html.classList.toggle('light', theme !== 'dark');
        localStorage.setItem('theme', theme);
    };

    const setActiveModeButton = (theme) => {
        modeButtons.forEach((button) => {
            button.classList.toggle('appearance-card-active', button.dataset.mode === theme);
        });

        themeToggleButtons.forEach((button) => {
            button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            button.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            button.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });
    };

    const savedTheme = normalizeTheme(localStorage.getItem('theme'));
    applyTheme(savedTheme);
    setActiveModeButton(savedTheme);

    modeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const theme = normalizeTheme(button.dataset.mode);
            applyTheme(theme);
            setActiveModeButton(theme);
        });
    });

    themeToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = html.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
            setActiveModeButton(nextTheme);
        });
    });

    initializeLanguagePreference();
    initializeModalControls();
    initializeConfirmationModals();
    initializeInventoryFormGuards();
    initializePasswordToggles();
    initializeAuthPasswordValidation();
    initializeAuthPhoneInputs();
    initializeDashboardSearch();
    initializeSidebarDateFilters();
    initializeLiveTables();
    initializeDashboardPolling();
    initializeBillingPolling();
    initializeCustomersPolling();
    initializeJobOrdersPolling();
    initializeReportsRevenueChart();
    initializeLandingMetricsPolling();
    initializeLandingRefreshLoader();
    initializeHeaderMenus();
    initializeNotificationActions();
    initializeLogoutLoading();
    initializeSidebarNavigation();
    initializeLandingBackgroundMotion();
    initializeLandingScrollReveal();
    initializeLandingSectionTracking();
    initializeProfileInitials();
    initializeImageUploadPreviews();
    initializeJobOrderCustomerPhotos();
    initializeRegistrationPopup();
    initializeLogsFilters();
});

function initializeModalControls() {
    const modals = [...document.querySelectorAll('[data-modal]')];

    if (!modals.length) {
        return;
    }

    const modalByName = new Map();
    modals.forEach((modal) => {
        modalByName.set(modal.dataset.modal, modal);
    });

    const closeAllModals = () => {
        modals.forEach((modal) => modal.classList.add('hidden'));
        document.body.classList.remove('overflow-hidden');
    };

    const openModal = (name) => {
        const target = modalByName.get(name);

        if (!target) {
            return;
        }

        closeAllModals();
        target.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = (name) => {
        const target = modalByName.get(name);
        if (!target) {
            return;
        }

        target.classList.add('hidden');

        const hasVisibleModal = modals.some((modal) => !modal.classList.contains('hidden'));
        if (!hasVisibleModal) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    const editForm = document.querySelector('[data-edit-form]');
    const movementForm = document.querySelector('[data-movement-form]');
    const movementLabel = document.querySelector('[data-movement-label]');

    const defaultUnitLabelForMode = (mode) => {
        return 'box';
    };

    const normalizeInventoryStockMode = (mode) => 'box_piece';

    const syncInventoryUnitLabel = (form, preserveExisting = false) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const stockMode = form.querySelector('[data-stock-mode-select]');
        const unitLabel = form.querySelector('[data-unit-label-select]');
        const containerQuantity = form.querySelector('input[name="container_quantity"]');
        const containerLabel = form.querySelector('[data-container-quantity-label]');
        const containerHelp = form.querySelector('[data-container-quantity-help]');

        if (!(stockMode instanceof HTMLSelectElement) || !(unitLabel instanceof HTMLSelectElement)) {
            return;
        }

        if (containerQuantity instanceof HTMLInputElement) {
            containerQuantity.required = true;
            containerQuantity.disabled = false;
            containerQuantity.step = '1';
            containerQuantity.min = '1';
            containerQuantity.placeholder = '10 pieces';
        }

        if (containerLabel instanceof HTMLElement) {
            containerLabel.textContent = 'Pieces per Box';
        }

        if (containerHelp instanceof HTMLElement) {
            containerHelp.textContent = 'Example: 1 box = 10 pieces';
        }

        const allowedValues = [...unitLabel.options].map((option) => option.value);
        const currentValueIsAllowed = allowedValues.includes(unitLabel.value);

        if (preserveExisting && currentValueIsAllowed) {
            return;
        }

        unitLabel.value = defaultUnitLabelForMode(stockMode.value);
    };

    const populateEditForm = (trigger) => {
        if (!(editForm instanceof HTMLFormElement)) {
            return;
        }

        const partId = trigger.dataset.editPartId;
        if (!partId) {
            return;
        }

        const template = editForm.dataset.actionTemplate ?? '';
        editForm.action = template.replace('__PART_ID__', partId);

        const fields = {
            name: trigger.dataset.editPartName ?? '',
            sku: trigger.dataset.editPartSku ?? '',
            category: trigger.dataset.editPartCategory ?? '',
            mode: normalizeInventoryStockMode(trigger.dataset.editPartMode),
            unit: trigger.dataset.editPartUnit ?? 'box',
            container_quantity: trigger.dataset.editPartContainerQuantity ?? '',
            minimum: trigger.dataset.editPartMinimum ?? '0',
            price: trigger.dataset.editPartPrice ?? '0.00',
            price_per_box: trigger.dataset.editPartPricePerBox ?? trigger.dataset.editPartPrice ?? '0.00',
            price_per_piece: trigger.dataset.editPartPricePerPiece ?? '0.00',
            active: trigger.dataset.editPartActive ?? '1',
        };

        Object.entries(fields).forEach(([key, value]) => {
            const input = editForm.querySelector(`[data-edit-field="${key}"]`);
            if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement) {
                input.value = value;
            }
        });

        syncInventoryUnitLabel(editForm, true);

        const imageUrl = trigger.dataset.editPartImageUrl ?? '';
        const previewImage = document.querySelector('[data-image-preview="edit-part-image"]');
        const previewWrapper = document.querySelector('[data-image-preview-wrapper="edit-part-image"]');
        const previewPlaceholder = document.querySelector('[data-image-preview-placeholder="edit-part-image"]');
        const previewInput = document.querySelector('[data-image-preview-input="edit-part-image"]');

        if (previewImage instanceof HTMLImageElement) {
            const existingObjectUrl = previewImage.dataset.objectUrl;
            if (existingObjectUrl) {
                URL.revokeObjectURL(existingObjectUrl);
                delete previewImage.dataset.objectUrl;
            }

            if (imageUrl) {
                previewImage.src = imageUrl;
                previewImage.classList.remove('hidden');
                if (previewWrapper instanceof HTMLElement) {
                    previewWrapper.classList.remove('hidden');
                }
                if (previewPlaceholder instanceof HTMLElement) {
                    previewPlaceholder.classList.add('hidden');
                }
            } else {
                previewImage.removeAttribute('src');
                previewImage.classList.add('hidden');
                if (previewWrapper instanceof HTMLElement) {
                    previewWrapper.classList.add('hidden');
                }
                if (previewPlaceholder instanceof HTMLElement) {
                    previewPlaceholder.classList.remove('hidden');
                }
            }
        }

        if (previewInput instanceof HTMLInputElement) {
            previewInput.value = '';
        }
    };

    const populateMovementForm = (trigger) => {
        if (!(movementForm instanceof HTMLFormElement)) {
            return;
        }

        const partId = trigger.dataset.movementPartId;
        if (!partId) {
            return;
        }

        const template = movementForm.dataset.actionTemplate ?? '';
        movementForm.action = template.replace('__PART_ID__', partId);

        if (movementLabel instanceof HTMLElement) {
            const partName = trigger.dataset.movementPartName ?? 'Part';
            const currentStock = trigger.dataset.movementPartStock ?? '0';
            movementLabel.textContent = `${partName} - Current stock: ${currentStock}`;
        }

        const mode = 'box_piece';
        const unitSelect = movementForm.querySelector('[data-movement-unit-select]');
        const typeSelect = movementForm.querySelector('[data-movement-type-select]');
        const quantityInput = movementForm.querySelector('input[name="quantity"]');
        const syncMovementUnits = () => {
            if (!(unitSelect instanceof HTMLSelectElement)) {
                return;
            }

            const type = typeSelect instanceof HTMLSelectElement ? typeSelect.value : 'in';
            const options = {
                box_piece: type === 'in'
                    ? [{ value: 'box', label: 'Box' }]
                    : [
                        { value: 'box', label: 'Box' },
                        { value: 'piece', label: 'Pieces' },
                    ],
            };

            unitSelect.innerHTML = options[mode]
                .map((option) => `<option value="${option.value}">${option.label}</option>`)
                .join('');
            if (quantityInput instanceof HTMLInputElement) {
                quantityInput.step = '1';
                quantityInput.min = '1';
            }
        };

        if (typeSelect instanceof HTMLSelectElement) {
            typeSelect.onchange = syncMovementUnits;
        }

        syncMovementUnits();
    };

    document.querySelectorAll('[data-stock-mode-select]').forEach((select) => {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        const form = select.closest('form');
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        select.addEventListener('change', () => syncInventoryUnitLabel(form));
        syncInventoryUnitLabel(form, true);
    });

    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            const trigger = button;
            const modalName = trigger.dataset.openModal;

            if (!modalName) {
                return;
            }

            if (modalName === 'edit-part-modal') {
                populateEditForm(trigger);
            }

            if (modalName === 'movement-modal') {
                populateMovementForm(trigger);
            }

            openModal(modalName);
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            const modalName = button.dataset.closeModal;
            if (modalName) {
                closeModal(modalName);
            }
        });
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                const modalName = modal.dataset.modal;
                if (modalName) {
                    closeModal(modalName);
                }
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllModals();
        }
    });
}

function initializeLanguagePreference() {
    const storageKey = 'motox.language';
    const html = document.documentElement;
    const languageSelects = [...document.querySelectorAll('[data-language-preference]')];
    const supportedLanguages = ['en-US', 'tl-PH'];
    const translations = {
        'tl-PH': {
            'Quick Settings': 'Mabilisang Settings',
            'Open settings page': 'Buksan ang settings page',
            'Edit shop profile': 'I-edit ang shop profile',
            'Profile': 'Profile',
            'View profile': 'Tingnan ang profile',
            'Account preferences': 'Mga preference ng account',
            'Notifications': 'Mga Notification',
            'Loading notifications...': 'Naglo-load ng notifications...',
            'Mark all as read': 'Markahan lahat bilang nabasa',
            'Notification settings': 'Settings ng notification',
            'Search anything...': 'Maghanap...',
            'Save Settings': 'I-save ang Settings',
            'Shop Preferences': 'Mga Preference ng Shop',
            'Language Preference': 'Preference sa Wika',
            'US English': 'US English',
            'Philippines - Tagalog': 'Philippines - Tagalog',
            'Appearance': 'Hitsura',
            'Light Mode': 'Light Mode',
            'Dark Mode': 'Dark Mode',
            'Are you sure?': 'Sigurado ka ba?',
            'Are you sure you want to delete this Part?': 'Sigurado ka bang gusto mong i-delete ang Part na ito?',
            'Are you sure you want to delete this Customer?': 'Sigurado ka bang gusto mong i-delete ang Customer na ito?',
            'Are you sure you want to delete this Job Order?': 'Sigurado ka bang gusto mong i-delete ang Job Order na ito?',
            'You are about to delete this log entry.': 'Ide-delete mo ang log entry na ito.',
            'You are about to delete all log history.': 'Ide-delete mo ang buong log history.',
            'Yes, Delete': 'Oo, I-delete',
            'Cancel': 'Kanselahin',
            'Are you sure you want to log out?': 'Sigurado ka bang mag-log out?',
            'You will be logged out of MotoX.': 'Mala-log out ka sa MotoX.',
            'Log Out': 'Mag Log Out',
            'Logging out': 'Nagla-log out',
            'logging out...': 'nagla-log out...',
            'Filter by Date': 'I-filter ayon sa Petsa',
            'Add Part': 'Magdagdag ng Part',
            'Create Customer': 'Gumawa ng Customer',
            'Create Job Order': 'Gumawa ng Job Order',
            'Dashboard': 'Dashboard',
            'Customers': 'Mga Customer',
            'Job Orders': 'Mga Job Order',
            'Inventory': 'Inventory',
            'Billing': 'Billing',
            'Reports': 'Mga Report',
            'Settings': 'Settings',
            'Logs': 'Logs',
            'Support': 'Support',
            'Help Me': 'Tulungan Ako',
            'Receipt': 'Resibo',
            'New Job Order': 'Bagong Job Order',
            'Total Customers': 'Kabuuang Customer',
            'Active Jobs': 'Aktibong Trabaho',
            'New This Month': 'Bago Ngayong Buwan',
            'Customer Directory': 'Direktoryo ng Customer',
            'Full Name': 'Buong Pangalan',
            'Email': 'Email',
            'Phone': 'Telepono',
            'Address': 'Address',
            'Notes': 'Notes',
            'Save Customer': 'I-save ang Customer',
            'Add New Part': 'Magdagdag ng Part',
            'Edit Part': 'I-edit ang Part',
            'Stock Mode': 'Stock Mode',
            'Unit Label': 'Unit Label',
            'Pieces per Box': 'Piraso bawat Kahon',
            'Minimum Stock': 'Minimum Stock',
            'Unit Price (PHP)': 'Presyo (PHP)',
            'Price Basis': 'Basehan ng Presyo',
            'Status': 'Status',
            'Save Part': 'I-save ang Part',
            'Update Part': 'I-update ang Part',
            'Record Stock Movement': 'Mag-record ng Stock Movement',
            'Movement Type': 'Uri ng Movement',
            'Quantity': 'Dami',
            'Quantity Unit': 'Unit ng Dami',
            'Reason': 'Dahilan',
            'Save Movement': 'I-save ang Movement',
            'History Log': 'History Log',
            'Action History': 'Kasaysayan ng Aksyon',
            'User': 'User',
            'Action': 'Aksyon',
            'Description': 'Deskripsyon',
            'Delete All History': 'I-delete ang Buong History',
        },
    };

    const normalizeLanguage = (value) => {
        const language = String(value || '').trim();
        return supportedLanguages.includes(language) ? language : 'en-US';
    };

    const translate = (text, language) => {
        const key = String(text || '').trim();
        return translations[language]?.[key] || key;
    };

    const applyLanguage = (language) => {
        const normalized = normalizeLanguage(language);
        html.lang = normalized === 'tl-PH' ? 'tl-PH' : 'en-US';
        localStorage.setItem(storageKey, normalized);

        languageSelects.forEach((select) => {
            if (select instanceof HTMLSelectElement) {
                select.value = normalized;
            }
        });

        document.querySelectorAll('[data-i18n]').forEach((node) => {
            if (!(node instanceof HTMLElement)) {
                return;
            }

            const key = node.dataset.i18n || node.textContent || '';
            node.textContent = translate(key, normalized);
        });

        document.querySelectorAll('a, button, h1, h2, h3, th, option, span, p').forEach((node) => {
            if (!(node instanceof HTMLElement) || node.children.length > 0 || node.dataset.i18n) {
                return;
            }

            const original = node.dataset.i18nOriginal || node.textContent || '';
            const trimmed = original.trim();
            if (!trimmed || translate(trimmed, 'tl-PH') === trimmed) {
                return;
            }

            node.dataset.i18nOriginal = trimmed;
            node.textContent = translate(trimmed, normalized);
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((node) => {
            if (!(node instanceof HTMLInputElement || node instanceof HTMLTextAreaElement)) {
                return;
            }

            node.placeholder = translate(node.dataset.i18nPlaceholder || node.placeholder, normalized);
        });
    };

    const savedLanguage = normalizeLanguage(localStorage.getItem(storageKey));
    applyLanguage(savedLanguage);

    languageSelects.forEach((select) => {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        select.addEventListener('change', () => applyLanguage(select.value));
    });
}

function initializeConfirmationModals() {
    const modal = document.querySelector('[data-confirm-modal]');
    const title = document.querySelector('[data-confirm-title]');
    const body = document.querySelector('[data-confirm-body]');
    const action = document.querySelector('[data-confirm-action]');
    const cancelButtons = [...document.querySelectorAll('[data-confirm-cancel]')];
    let pendingForm = null;

    if (!(modal instanceof HTMLElement) || !(action instanceof HTMLButtonElement)) {
        return;
    }

    const open = (form) => {
        pendingForm = form;

        if (title instanceof HTMLElement) {
            title.textContent = form.dataset.confirmTitle || 'Are you sure?';
        }

        if (body instanceof HTMLElement) {
            body.textContent = form.dataset.confirmBody || 'You are about to delete this item.';
        }

        action.textContent = form.dataset.confirmAction || 'Yes, Delete';
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        pendingForm = null;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            open(form);
        });
    });

    action.addEventListener('click', () => {
        const form = pendingForm;
        close();

        if (form instanceof HTMLFormElement) {
            HTMLFormElement.prototype.submit.call(form);
        }
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', close);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });
}

function initializeInventoryFormGuards() {
    const forms = [...document.querySelectorAll('[data-inventory-action-form]')];

    if (!forms.length) {
        return;
    }

    forms.forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.addEventListener('submit', (event) => {
            const rawAction = form.getAttribute('action') ?? '';
            if (rawAction === '#' || rawAction.includes('__PART_ID__') || form.action.includes('__PART_ID__')) {
                event.preventDefault();
                window.alert('Please choose a part before submitting this inventory action.');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.textContent ?? '';
                submitButton.textContent = 'Saving...';
            }
        });
    });
}

function initializeLogoutLoading() {
    const logoutForm = document.querySelector('[data-logout-form]');
    const logoutButton = document.querySelector('[data-logout-button]');
    const logoutOverlay = document.querySelector('[data-logout-overlay]');
    const logoutModal = document.querySelector('[data-logout-confirm-modal]');
    const confirmLogoutButton = document.querySelector('[data-confirm-logout]');
    const cancelLogoutButtons = [...document.querySelectorAll('[data-cancel-logout]')];
    let logoutSubmitting = false;

    if (!(logoutForm instanceof HTMLFormElement)) {
        return;
    }

    const openLogoutModal = () => {
        if (!(logoutModal instanceof HTMLElement)) {
            return false;
        }

        logoutModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        return true;
    };

    const closeLogoutModal = () => {
        if (logoutModal instanceof HTMLElement) {
            logoutModal.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');
    };

    const submitLogout = () => {
        logoutSubmitting = true;

        if (logoutButton instanceof HTMLButtonElement) {
            logoutButton.disabled = true;
            logoutButton.setAttribute('aria-busy', 'true');
            logoutButton.classList.add('logout-button-active');
        }

        if (logoutOverlay instanceof HTMLElement) {
            logoutOverlay.classList.remove('hidden');
            logoutOverlay.setAttribute('aria-hidden', 'false');
        }

        window.setTimeout(() => {
            HTMLFormElement.prototype.submit.call(logoutForm);
        }, 3000);
    };

    logoutForm.addEventListener('submit', (event) => {
        if (logoutSubmitting) {
            return;
        }

        event.preventDefault();
        if (!openLogoutModal()) {
            submitLogout();
        }
    });

    if (confirmLogoutButton instanceof HTMLButtonElement) {
        confirmLogoutButton.addEventListener('click', () => {
            closeLogoutModal();
            submitLogout();
        });
    }

    cancelLogoutButtons.forEach((button) => {
        button.addEventListener('click', closeLogoutModal);
    });

    if (logoutModal instanceof HTMLElement) {
        logoutModal.addEventListener('click', (event) => {
            if (event.target === logoutModal) {
                closeLogoutModal();
            }
        });
    }
}

function receiptDataFromButton(button) {
    const source = button instanceof HTMLElement && button.dataset.receiptInvoice
        ? button
        : button.closest?.('[data-billing-row]') ?? button;
    const data = source.dataset ?? {};

    return {
        invoice: data.receiptInvoice || '',
        order: data.receiptOrder || '',
        customer: data.receiptCustomer || '',
        phone: data.receiptPhone || '',
        email: data.receiptEmail || '',
        photo: data.receiptPhoto || '',
        vehicle: data.receiptVehicle || '',
        status: data.receiptStatus || '',
        amount: data.receiptAmount || 'PHP 0.00',
        amountValue: Number(data.receiptAmountValue || 0),
        updated: data.receiptUpdated || '',
        shop: data.receiptShop || 'MotoX',
    };
}

function buildReceiptNode(data) {
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const total = Number.isFinite(data.amountValue) && data.amountValue > 0
        ? data.amountValue
        : Number(String(data.amount).replace(/[^0-9.]/g, '')) || 0;
    const isPaid = String(data.status).toLowerCase() === 'paid';
    const collected = isPaid ? total : 0;
    const remaining = Math.max(0, total - collected);
    const progress = total > 0 ? Math.round((collected / total) * 100) : 0;
    const money = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    });
    const statusLabel = isPaid ? 'PAID' : 'PENDING';
    const progressBoxes = Array.from({ length: 12 }, (_, index) => {
        const filled = ((index + 1) / 12) * 100 <= progress;
        return `<span class="${filled ? 'receipt-box-filled' : ''}"></span>`;
    }).join('');

    const node = document.createElement('section');
    node.className = 'receipt-capture-card';
    node.innerHTML = `
        <h1>${escapeHtml(data.customer || 'Customer')}</h1>
        <div class="receipt-capture-balance">${escapeHtml(money.format(remaining))} REMAINING</div>

        <div class="receipt-capture-rule"></div>
        <div class="receipt-capture-summary">
            <span>TOTAL AMOUNT</span><strong>${escapeHtml(money.format(total))}</strong>
            <span>COLLECTED</span><strong>${escapeHtml(money.format(collected))}</strong>
            <span>STILL OWED</span><strong>${escapeHtml(money.format(remaining))}</strong>
        </div>

        <div class="receipt-capture-progress">
            <div><span>COLLECTION PROGRESS</span><strong>${progress}%</strong></div>
            <div class="receipt-progress-boxes">${progressBoxes}</div>
        </div>

        <div class="receipt-capture-section">
            <div class="receipt-capture-section-head"><span>ITEMS OWED</span><span>AMOUNT</span></div>
            <div class="receipt-capture-item">
                <small>${escapeHtml(data.updated || '-')}</small>
                <div><strong>${escapeHtml(data.order || data.invoice || 'Job order')}</strong><b>${escapeHtml(money.format(total))}</b></div>
                <em>${escapeHtml(data.vehicle || 'Service record')}</em>
            </div>
        </div>

        <div class="receipt-capture-total-row">
            <span>ITEMS TOTAL</span>
            <strong>${escapeHtml(money.format(total))}</strong>
        </div>

        <div class="receipt-capture-section">
            <div class="receipt-capture-section-head"><span>PAYMENT HISTORY</span></div>
            <div class="receipt-capture-payment">
                <span>${isPaid ? 'Marked paid from completed job order' : 'No payments yet'}</span>
                <strong>${escapeHtml(money.format(collected))}</strong>
            </div>
        </div>

        <div class="receipt-capture-total-row">
            <span>TOTAL PAID</span>
            <strong>${escapeHtml(money.format(collected))}</strong>
        </div>

        <div class="receipt-capture-meta">
            <span>INVOICE</span><strong>${escapeHtml(data.invoice || '-')}</strong>
            <span>JOB ORDER</span><strong>${escapeHtml(data.order || '-')}</strong>
            <span>STATUS</span><strong>${escapeHtml(data.status || '-')}</strong>
        </div>

        <div class="receipt-capture-note">
            <span>NOTE</span>
            <strong>${escapeHtml(data.shop || 'MotoX')} receipt for ${escapeHtml(data.customer || 'customer')}</strong>
        </div>

        <div class="receipt-capture-stamp">${statusLabel}</div>
        <div class="receipt-capture-codes">
            <div class="receipt-capture-barcode" aria-label="Receipt barcode for ${escapeHtml(data.invoice || data.customer || 'receipt')}"></div>
        </div>
        <p class="receipt-capture-footer">PRINTED BY ${escapeHtml(data.shop || 'MOTOX').toUpperCase()} ON ${escapeHtml(new Date().toLocaleString('en-US', { timeZone: 'Asia/Manila' }))} PHT</p>
    `;

    return node;
}

async function renderReceiptCanvas(source) {
    if (!(source instanceof HTMLElement)) {
        return null;
    }

    const host = document.createElement('div');
    host.className = 'receipt-capture-host';
    const receiptNode = buildReceiptNode(receiptDataFromButton(source));
    host.appendChild(receiptNode);
    document.body.appendChild(host);

    try {
        return await html2canvas(receiptNode, {
            backgroundColor: '#ffffff',
            scale: Math.max(2, window.devicePixelRatio || 1),
            useCORS: true,
        });
    } finally {
        host.remove();
    }
}

async function downloadReceiptPng(source) {
    if (!(source instanceof HTMLElement)) {
        return;
    }

    if (source instanceof HTMLButtonElement) {
        source.disabled = true;
        source.setAttribute('aria-busy', 'true');
    }

    try {
        const canvas = await renderReceiptCanvas(source);
        if (!canvas) {
            return;
        }

        const link = document.createElement('a');
        const receiptData = receiptDataFromButton(source);
        const filename = (receiptData.invoice || receiptData.customer || 'motox-receipt')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        link.href = canvas.toDataURL('image/png');
        link.download = `${filename || 'motox-receipt'}.png`;
        link.click();
    } finally {
        if (source instanceof HTMLButtonElement) {
            source.disabled = false;
            source.removeAttribute('aria-busy');
        }
    }
}

async function printReceiptPng(source) {
    if (!(source instanceof HTMLElement)) {
        return;
    }

    if (source instanceof HTMLButtonElement) {
        source.disabled = true;
        source.setAttribute('aria-busy', 'true');
    }

    try {
        const canvas = await renderReceiptCanvas(source);
        if (!canvas) {
            return;
        }

        const receiptData = receiptDataFromButton(source);
        const printWindow = window.open('', '_blank', 'width=520,height=760');

        if (!printWindow) {
            downloadReceiptPng(source);
            return;
        }

        printWindow.document.write(`
            <!doctype html>
            <html>
                <head>
                    <title>${String(receiptData.invoice || receiptData.customer || 'MotoX Receipt').replace(/[<>]/g, '')}</title>
                    <style>
                        body { margin: 0; background: #f8fafc; display: grid; min-height: 100vh; place-items: center; }
                        img { width: min(430px, 94vw); height: auto; background: #fff; box-shadow: 0 16px 45px rgba(15, 23, 42, 0.16); }
                        @media print {
                            body { background: #fff; min-height: auto; }
                            img { width: 100%; max-width: 430px; box-shadow: none; }
                        }
                    </style>
                </head>
                <body>
                    <img src="${canvas.toDataURL('image/png')}" alt="MotoX receipt">
                    <script>
                        window.addEventListener('load', () => {
                            window.focus();
                            window.print();
                        });
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    } finally {
        if (source instanceof HTMLButtonElement) {
            source.disabled = false;
            source.removeAttribute('aria-busy');
        }
    }
}

function initializePasswordToggles() {
    const toggleButtons = [...document.querySelectorAll('[data-password-toggle]')];

    if (!toggleButtons.length) {
        return;
    }

    const syncPasswordToggle = (button, input) => {
        const isVisible = input.type === 'text';
        const showIcon = button.querySelector('[data-password-icon="show"]');
        const hideIcon = button.querySelector('[data-password-icon="hide"]');

        button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        button.setAttribute('title', isVisible ? 'Hide password' : 'Show password');
        button.classList.toggle('password-toggle-active', isVisible);

        if (showIcon instanceof Element) {
            showIcon.classList.toggle('hidden', !isVisible);
        }

        if (hideIcon instanceof Element) {
            hideIcon.classList.toggle('hidden', isVisible);
        }
    };

    toggleButtons.forEach((button) => {
        const targetId = button.dataset.target;
        const input = targetId ? document.getElementById(targetId) : null;

        if (input instanceof HTMLInputElement) {
            syncPasswordToggle(button, input);
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();

            const targetId = button.dataset.target;
            if (!targetId) {
                return;
            }

            const input = document.getElementById(targetId);
            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const nextVisibleState = input.type === 'password';
            input.type = nextVisibleState ? 'text' : 'password';
            syncPasswordToggle(button, input);
        });
    });
}

function initializeAuthPasswordValidation() {
    const forms = [...document.querySelectorAll('[data-auth-password-form]')];

    if (!forms.length) {
        return;
    }

    const checks = {
        length: (value) => value.length >= 8 && value.length <= 16,
        lower: (value) => /[a-z]/.test(value),
        upper: (value) => /[A-Z]/.test(value),
        number: (value) => /[0-9]/.test(value),
        special: (value) => /[!@#$%&*]/.test(value),
    };

    forms.forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const password = form.querySelector('[data-auth-password]');
        const confirmation = form.querySelector('[data-auth-password-confirmation]');
        const ruleItems = [...form.querySelectorAll('[data-password-rule]')];
        const matchError = form.querySelector('[data-auth-password-match]');

        if (!(password instanceof HTMLInputElement)) {
            return;
        }

        const validate = (showErrors = true) => {
            const value = password.value;
            const ruleResults = Object.fromEntries(
                Object.entries(checks).map(([key, check]) => [key, check(value)]),
            );
            const passwordIsValid = Object.values(ruleResults).every(Boolean);
            const confirmationIsValid = !(confirmation instanceof HTMLInputElement)
                || (confirmation.value.length > 0 && confirmation.value === value);

            ruleItems.forEach((item) => {
                const rule = item.dataset.passwordRule;
                const isValid = Boolean(rule && ruleResults[rule]);
                item.classList.toggle('auth-validation-pass', isValid);
                item.classList.toggle('auth-validation-fail', showErrors && !isValid);
            });

            if (confirmation instanceof HTMLInputElement) {
                confirmation.setCustomValidity(confirmationIsValid ? '' : 'Password confirmation does not match.');
            }

            if (matchError instanceof HTMLElement) {
                matchError.classList.toggle('hidden', confirmationIsValid || !showErrors);
            }

            password.setCustomValidity(passwordIsValid ? '' : 'Password does not meet the required strength rules.');

            return passwordIsValid && confirmationIsValid;
        };

        password.addEventListener('input', () => validate(true));
        if (confirmation instanceof HTMLInputElement) {
            confirmation.addEventListener('input', () => validate(true));
        }

        form.addEventListener('submit', (event) => {
            if (!validate(true)) {
                event.preventDefault();
                password.reportValidity();
            }
        });

        validate(false);
    });
}

function initializeAuthPhoneInputs() {
    const forms = [...document.querySelectorAll('[data-auth-phone-form]')];

    if (!forms.length) {
        return;
    }

    forms.forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const input = form.querySelector('[data-auth-phone-input]');
        const fullInput = form.querySelector('[data-auth-phone-full]');
        const countryInput = form.querySelector('[data-auth-phone-country]');
        const dialCodeInput = form.querySelector('[data-auth-phone-dial-code]');
        const error = form.querySelector('[data-auth-phone-error]');

        if (!(input instanceof HTMLInputElement)
            || !(fullInput instanceof HTMLInputElement)
            || !(countryInput instanceof HTMLInputElement)
            || !(dialCodeInput instanceof HTMLInputElement)
        ) {
            return;
        }

        const initialCountry = (countryInput.value || 'ph').toLowerCase();
        const phone = intlTelInput(input, {
            initialCountry,
            separateDialCode: true,
            nationalMode: true,
            strictMode: true,
            formatAsYouType: false,
        });

        if (fullInput.value) {
            phone.setNumber(fullInput.value);
        }

        const selectedDialCode = () => {
            const data = phone.getSelectedCountryData();
            return data?.dialCode ? `+${data.dialCode}` : '';
        };

        const syncCountryFields = () => {
            const data = phone.getSelectedCountryData();
            countryInput.value = String(data?.iso2 || '').toLowerCase();
            dialCodeInput.value = selectedDialCode();
        };

        const digitsOnly = () => {
            const digits = input.value.replace(/\D+/g, '');
            if (input.value !== digits) {
                input.value = digits;
            }

            return digits;
        };

        const setPhoneError = (message = '') => {
            input.setCustomValidity(message);

            if (error instanceof HTMLElement) {
                error.textContent = message;
                error.classList.toggle('hidden', message === '');
            }
        };

        const validate = (showErrors = true) => {
            const digits = digitsOnly();
            syncCountryFields();

            if (digits === '') {
                fullInput.value = '';
                setPhoneError('');
                return true;
            }

            if (!/^\d+$/.test(digits)) {
                const message = 'Contact number can contain numbers only.';
                setPhoneError(showErrors ? message : '');
                return false;
            }

            const isValid = phone.isValidNumber();
            if (!isValid) {
                const message = 'Enter a valid phone number for the selected country.';
                setPhoneError(showErrors ? message : '');
                return false;
            }

            fullInput.value = phone.getNumber();
            setPhoneError('');
            return true;
        };

        input.addEventListener('input', () => validate(true));
        input.addEventListener('blur', () => validate(true));
        input.addEventListener('countrychange', () => validate(input.value.trim() !== ''));

        form.addEventListener('submit', (event) => {
            if (!validate(true)) {
                event.preventDefault();
                input.reportValidity();
            }
        });

        validate(false);
    });
}

function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token instanceof HTMLMetaElement ? token.content : '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function chartSvgSize(container) {
    const bounds = container instanceof HTMLElement ? container.getBoundingClientRect() : null;
    const width = Math.max(720, Math.round((bounds?.width || 1020) - 40));
    const height = Math.round(Math.min(320, Math.max(230, window.innerWidth * 0.28)));

    return { width, height };
}

function initializeDashboardPolling() {
    const dashboardRoot = document.querySelector('[data-dashboard-metrics-url]');
    if (!(dashboardRoot instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = dashboardRoot.dataset.dashboardMetricsUrl;
    if (!metricsUrl) {
        return;
    }

    const kpiNodes = {
        total_skus: dashboardRoot.querySelector('[data-kpi="total_skus"]'),
        low_stock: dashboardRoot.querySelector('[data-kpi="low_stock"]'),
        out_of_stock: dashboardRoot.querySelector('[data-kpi="out_of_stock"]'),
        inventory_value: dashboardRoot.querySelector('[data-kpi="inventory_value"]'),
    };

    const trendContainer = dashboardRoot.querySelector('[data-chart="movement"]');
    const updatedAtNode = dashboardRoot.querySelector('[data-updated-at]');
    const rangeButtons = [...dashboardRoot.querySelectorAll('[data-dashboard-range]')];
    const rangeSelect = dashboardRoot.querySelector('[data-dashboard-range-select]');
    const revenueCards = [...dashboardRoot.querySelectorAll('[data-dashboard-revenue-card]')];
    const rangeStorageKey = 'motox.dashboard.trend.range';
    const legacyRangeStorageKey = 'motox.dashboard.trend.months';
    const allowedTrendRanges = ['jan-jun', 'jul-dec'];

    const normalizeTrendRange = (value) => {
        const normalized = String(value ?? '').trim().toLowerCase();
        return allowedTrendRanges.includes(normalized) ? normalized : 'jan-jun';
    };

    let selectedTrendRange = normalizeTrendRange(dashboardRoot.dataset.dashboardRange ?? dashboardRoot.dataset.dashboardMonths);

    const setActiveRangeControl = (range) => {
        rangeButtons.forEach((button) => {
            const buttonRange = normalizeTrendRange(button.dataset.dashboardRange);
            button.classList.toggle('budget-range-pill-active', buttonRange === range);
        });

        if (rangeSelect instanceof HTMLSelectElement) {
            rangeSelect.value = normalizeTrendRange(range);
        }
    };

    try {
        selectedTrendRange = normalizeTrendRange(
            localStorage.getItem(rangeStorageKey)
                ?? localStorage.getItem(legacyRangeStorageKey)
                ?? selectedTrendRange
        );
    } catch (error) {
        selectedTrendRange = normalizeTrendRange(dashboardRoot.dataset.dashboardRange ?? dashboardRoot.dataset.dashboardMonths);
    }

    setActiveRangeControl(selectedTrendRange);

    const renderTrendChart = (trend) => {
        if (!(trendContainer instanceof HTMLElement)) {
            return;
        }

        const rows = Array.isArray(trend) && trend.length
            ? trend
            : [{ label: 'Now', date_label: 'Current stock flow', in: 0, out: 0 }];

        const seriesIn = rows.map((row) => Math.max(0, Number(row.in || 0)));
        const seriesOut = rows.map((row) => Math.max(0, Number(row.out || 0)));
        const observedMaxValue = Math.max(...seriesIn, ...seriesOut);
        const scaleBase = Math.max(1, observedMaxValue);
        const maxPercent = 100;
        const percentAxisValues = Array.from({ length: 6 }, (_, index) => 100 - (index * 20));

        const { width, height } = chartSvgSize(trendContainer);
        const padding = { top: 18, right: 18, bottom: 34, left: 82 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const stepX = rows.length > 1 ? chartWidth / (rows.length - 1) : 0;

        const mapPoint = (value, index) => {
            const x = rows.length > 1 ? padding.left + (stepX * index) : padding.left + (chartWidth / 2);
            const percent = Math.min(maxPercent, Math.max(0, (value / scaleBase) * 100));
            const y = padding.top + ((1 - (percent / maxPercent)) * chartHeight);

            return { x, y };
        };

        const inPoints = seriesIn.map(mapPoint);
        const outPoints = seriesOut.map(mapPoint);
        const toPolyline = (points) => points.map((point) => `${point.x.toFixed(2)},${point.y.toFixed(2)}`).join(' ');
        const baselineY = height - padding.bottom;
        const toAreaPath = (points) => {
            if (!points.length) {
                return '';
            }

            const start = points[0];
            const end = points[points.length - 1];
            const linePath = points.map((point) => `L ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' ');

            return `M ${start.x.toFixed(2)} ${baselineY.toFixed(2)} ${linePath} L ${end.x.toFixed(2)} ${baselineY.toFixed(2)} Z`;
        };
        const numberFormatter = new Intl.NumberFormat('en-US');

        const horizontalGrid = Array.from({ length: 6 }, (_, index) => {
            const y = padding.top + ((chartHeight / 5) * index);
            const labelValue = percentAxisValues[index] ?? 0;

            return `
                <text class="budget-grid-label" x="${(padding.left - 12).toFixed(2)}" y="${(y + 4).toFixed(2)}">${numberFormatter.format(labelValue)}%</text>
                <line class="budget-grid-line" x1="${padding.left}" y1="${y.toFixed(2)}" x2="${(width - padding.right).toFixed(2)}" y2="${y.toFixed(2)}"></line>
            `;
        }).join('');

        const verticalGrid = rows.map((_, index) => {
            const x = padding.left + (stepX * index);
            return `<line class="budget-grid-line-vertical" x1="${x.toFixed(2)}" y1="${padding.top}" x2="${x.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>`;
        }).join('');

        const latestMovementIndex = rows.reduce((latest, row, index) => ((Number(row?.in || 0) > 0 || Number(row?.out || 0) > 0) ? index : latest), -1);
        const focusIndex = Math.max(0, latestMovementIndex >= 0 ? latestMovementIndex : rows.length - 1);
        const focusIn = inPoints[focusIndex] ?? inPoints[inPoints.length - 1];
        const focusOut = outPoints[focusIndex] ?? outPoints[outPoints.length - 1];
        const focusX = focusIn?.x ?? padding.left;
        const tooltipLeft = Math.min(78, Math.max(22, (focusX / width) * 100));

        const currentIn = seriesIn[focusIndex] ?? 0;
        const currentOut = seriesOut[focusIndex] ?? 0;
        const previousIn = seriesIn[Math.max(0, focusIndex - 1)] ?? 0;
        const previousOut = seriesOut[Math.max(0, focusIndex - 1)] ?? 0;

        const percentDelta = (current, previous) => {
            if (previous === 0) {
                return current === 0 ? 0 : 100;
            }

            return ((current - previous) / previous) * 100;
        };

        const deltaIn = percentDelta(currentIn, previousIn);
        const deltaOut = percentDelta(currentOut, previousOut);
        const percentageFormatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        });
        const formatDelta = (value) => `${value >= 0 ? '+' : ''}${percentageFormatter.format(value)}%`;

        const formatFullDate = (row) => {
            if (typeof row?.date_label === 'string' && row.date_label.length) {
                return row.date_label;
            }

            const focusDate = new Date(`${row?.day ?? ''}T00:00:00`);

            if (Number.isNaN(focusDate.valueOf())) {
                return row?.label ?? 'Current';
            }

            const month = focusDate.toLocaleDateString('en-US', { month: 'long' });
            const weekday = focusDate.toLocaleDateString('en-US', { weekday: 'long' });

            return `${month} ${focusDate.getDate()}, ${focusDate.getFullYear()}, ${weekday}`;
        };

        const focusRow = rows[focusIndex] ?? rows[rows.length - 1];
        const dateLabel = formatFullDate(focusRow);

        const axisLabels = rows.map((row, index) => `
            <span class="budget-axis-label ${index === focusIndex ? 'budget-axis-label-active' : ''}">
                ${escapeHtml(row.label ?? '')}
            </span>
        `).join('');
        const dateLabelFor = (row) => formatFullDate(row);

        trendContainer.style.setProperty('--budget-count', String(Math.max(1, rows.length)));
        trendContainer.innerHTML = `
            <div class="budget-chart-shell">
                <svg viewBox="0 0 ${width} ${height}" class="budget-chart-svg" role="img" aria-label="Stock in and stock out trend chart">
                    <defs>
                        <linearGradient id="budgetGradientIn" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(34, 197, 94, 0.32)" />
                            <stop offset="58%" stop-color="rgba(16, 185, 129, 0.14)" />
                            <stop offset="100%" stop-color="rgba(34, 197, 94, 0.035)" />
                        </linearGradient>
                        <linearGradient id="budgetGradientOut" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(253, 186, 116, 0.24)" />
                            <stop offset="100%" stop-color="rgba(234, 88, 12, 0.03)" />
                        </linearGradient>
                    </defs>
                    ${horizontalGrid}
                    ${verticalGrid}
                    <path class="budget-area-in" d="${toAreaPath(inPoints)}"></path>
                    <path class="budget-area-out" d="${toAreaPath(outPoints)}"></path>
                    <polyline class="budget-line budget-line-in" points="${toPolyline(inPoints)}"></polyline>
                    <polyline class="budget-line budget-line-out" points="${toPolyline(outPoints)}"></polyline>
                    <line class="budget-guide-line" data-dashboard-guide x1="${focusX.toFixed(2)}" y1="${padding.top}" x2="${focusX.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>
                    ${inPoints.map((point, index) => `<circle class="budget-point budget-point-in budget-point-hoverable" data-dashboard-point="${index}" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4.25"></circle>`).join('')}
                    ${outPoints.map((point, index) => `<circle class="budget-point budget-point-out budget-point-hoverable" data-dashboard-point="${index}" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4.25"></circle>`).join('')}
                    <circle class="budget-point budget-point-in budget-point-focus" data-dashboard-focus-in cx="${(focusIn?.x ?? focusX).toFixed(2)}" cy="${(focusIn?.y ?? baselineY).toFixed(2)}" r="6"></circle>
                    <circle class="budget-point budget-point-out budget-point-focus" data-dashboard-focus-out cx="${(focusOut?.x ?? focusX).toFixed(2)}" cy="${(focusOut?.y ?? baselineY).toFixed(2)}" r="6"></circle>
                </svg>

                <article class="budget-tooltip-card" data-dashboard-tooltip style="left: ${tooltipLeft}%;">
                    <div class="budget-tooltip-head">
                        <p class="budget-tooltip-date" data-dashboard-tooltip-date>${escapeHtml(dateLabel)}</p>
                        <span class="budget-tooltip-total" data-dashboard-tooltip-total>${escapeHtml(focusRow.label ?? 'Current')}</span>
                    </div>
                    <div class="budget-tooltip-divider"></div>
                    <div class="budget-tooltip-row">
                        <div>
                            <p class="budget-tooltip-amount" data-dashboard-tooltip-in>${numberFormatter.format(currentIn)}</p>
                            <p class="budget-tooltip-caption">Stock In</p>
                        </div>
                        <span class="budget-tooltip-change budget-tooltip-change-in" data-dashboard-tooltip-in-change>${formatDelta(deltaIn)}</span>
                    </div>
                    <div class="mt-3 budget-tooltip-row">
                        <div>
                            <p class="budget-tooltip-amount" data-dashboard-tooltip-out>${numberFormatter.format(currentOut)}</p>
                            <p class="budget-tooltip-caption">Stock Out</p>
                        </div>
                        <span class="budget-tooltip-change budget-tooltip-change-out" data-dashboard-tooltip-out-change>${formatDelta(deltaOut)}</span>
                    </div>
                </article>
            </div>

            <div class="budget-axis-row">${axisLabels}</div>
        `;

        const shell = trendContainer.querySelector('.budget-chart-shell');
        const guide = trendContainer.querySelector('[data-dashboard-guide]');
        const focusInNode = trendContainer.querySelector('[data-dashboard-focus-in]');
        const focusOutNode = trendContainer.querySelector('[data-dashboard-focus-out]');
        const tooltip = trendContainer.querySelector('[data-dashboard-tooltip]');
        const tooltipDate = trendContainer.querySelector('[data-dashboard-tooltip-date]');
        const tooltipTotal = trendContainer.querySelector('[data-dashboard-tooltip-total]');
        const tooltipIn = trendContainer.querySelector('[data-dashboard-tooltip-in]');
        const tooltipOut = trendContainer.querySelector('[data-dashboard-tooltip-out]');
        const tooltipInChange = trendContainer.querySelector('[data-dashboard-tooltip-in-change]');
        const tooltipOutChange = trendContainer.querySelector('[data-dashboard-tooltip-out-change]');
        const pointNodes = [...trendContainer.querySelectorAll('[data-dashboard-point]')];
        const axisNodes = [...trendContainer.querySelectorAll('.budget-axis-label')];

        const setFocusIndex = (index) => {
            const pointIn = inPoints[index];
            const pointOut = outPoints[index];
            const row = rows[index] ?? {};
            const currentStockIn = seriesIn[index] ?? 0;
            const currentStockOut = seriesOut[index] ?? 0;
            const previousStockIn = seriesIn[Math.max(0, index - 1)] ?? 0;
            const previousStockOut = seriesOut[Math.max(0, index - 1)] ?? 0;
            const x = pointIn?.x ?? pointOut?.x ?? padding.left;

            if (guide instanceof SVGLineElement) {
                guide.setAttribute('x1', x.toFixed(2));
                guide.setAttribute('x2', x.toFixed(2));
            }
            if (focusInNode instanceof SVGCircleElement && pointIn) {
                focusInNode.setAttribute('cx', pointIn.x.toFixed(2));
                focusInNode.setAttribute('cy', pointIn.y.toFixed(2));
            }
            if (focusOutNode instanceof SVGCircleElement && pointOut) {
                focusOutNode.setAttribute('cx', pointOut.x.toFixed(2));
                focusOutNode.setAttribute('cy', pointOut.y.toFixed(2));
            }
            pointNodes.forEach((node) => {
                node.classList.toggle('budget-point-active', Number(node.dataset.dashboardPoint) === index);
            });
            axisNodes.forEach((node, nodeIndex) => {
                node.classList.toggle('budget-axis-label-active', nodeIndex === index);
            });
            if (tooltip instanceof HTMLElement) {
                tooltip.style.left = `${Math.min(78, Math.max(22, (x / width) * 100))}%`;
            }
            if (tooltipDate instanceof HTMLElement) {
                tooltipDate.textContent = dateLabelFor(row);
            }
            if (tooltipTotal instanceof HTMLElement) {
                tooltipTotal.textContent = row?.label ?? 'Current';
            }
            if (tooltipIn instanceof HTMLElement) {
                tooltipIn.textContent = numberFormatter.format(currentStockIn);
            }
            if (tooltipOut instanceof HTMLElement) {
                tooltipOut.textContent = numberFormatter.format(currentStockOut);
            }
            if (tooltipInChange instanceof HTMLElement) {
                tooltipInChange.textContent = formatDelta(percentDelta(currentStockIn, previousStockIn));
            }
            if (tooltipOutChange instanceof HTMLElement) {
                tooltipOutChange.textContent = formatDelta(percentDelta(currentStockOut, previousStockOut));
            }
        };

        if (shell instanceof HTMLElement && inPoints.length) {
            shell.addEventListener('pointermove', (event) => {
                const rect = shell.getBoundingClientRect();
                const chartX = ((event.clientX - rect.left) / Math.max(1, rect.width)) * width;
                const nearestIndex = inPoints.reduce((nearest, point, index) => {
                    const currentDistance = Math.abs(point.x - chartX);
                    const nearestDistance = Math.abs(inPoints[nearest].x - chartX);

                    return currentDistance < nearestDistance ? index : nearest;
                }, 0);

                setFocusIndex(nearestIndex);
            });

            shell.addEventListener('pointerleave', () => {
                setFocusIndex(inPoints.length - 1);
            });
        }
    };

    const applyRevenueStats = (rows) => {
        if (!Array.isArray(rows) || !rows.length) {
            return;
        }

        rows.forEach((row) => {
            const period = String(row?.period ?? '');
            const card = revenueCards.find((node) => node instanceof HTMLElement && node.dataset.dashboardRevenueCard === period);

            if (!(card instanceof HTMLElement)) {
                return;
            }

            const valueNode = card.querySelector('[data-dashboard-revenue-value]');
            if (valueNode instanceof HTMLElement) {
                valueNode.textContent = String(row?.value ?? 'PHP 0.00');
            }
        });
    };

    const applyMetrics = (payload) => {
        if (payload?.kpis) {
            Object.entries(payload.kpis).forEach(([key, value]) => {
                const node = kpiNodes[key];
                if (node instanceof HTMLElement) {
                    node.textContent = String(value);
                }
            });
        }

        renderTrendChart(payload?.trend ?? []);
        applyRevenueStats(payload?.revenue_stats ?? []);

        if (typeof payload?.trend_range === 'string' || typeof payload?.trend_range_months === 'number') {
            selectedTrendRange = normalizeTrendRange(payload.trend_range ?? payload.trend_range_months);
            setActiveRangeControl(selectedTrendRange);
        }

        if (updatedAtNode instanceof HTMLElement && payload?.updated_at) {
            const updated = new Date(payload.updated_at);
            if (!Number.isNaN(updated.valueOf())) {
                updatedAtNode.textContent = `Updated ${updated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Manila' })} PHT`;
            }
        }
    };

    const buildMetricsUrl = (range = selectedTrendRange) => {
        const url = new URL(metricsUrl, window.location.origin);
        url.searchParams.set('range', normalizeTrendRange(range));
        return `${url.pathname}${url.search}`;
    };

    const fetchMetrics = async (range = selectedTrendRange) => {
        try {
            const response = await fetch(buildMetricsUrl(range), {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyMetrics(payload);
        } catch (error) {
            console.warn('Dashboard metrics polling failed', error);
        }
    };

    rangeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const range = normalizeTrendRange(button.dataset.dashboardRange);
            selectedTrendRange = range;
            setActiveRangeControl(range);

            try {
                localStorage.setItem(rangeStorageKey, range);
                localStorage.removeItem(legacyRangeStorageKey);
            } catch (error) {
                console.warn('Unable to persist dashboard trend range', error);
            }

            fetchMetrics(range);
        });
    });

    if (rangeSelect instanceof HTMLSelectElement) {
        rangeSelect.addEventListener('change', () => {
            const range = normalizeTrendRange(rangeSelect.value);
            selectedTrendRange = range;
            setActiveRangeControl(range);

            try {
                localStorage.setItem(rangeStorageKey, range);
                localStorage.removeItem(legacyRangeStorageKey);
            } catch (error) {
                console.warn('Unable to persist dashboard trend range', error);
            }

            fetchMetrics(range);
        });
    }

    if (trendContainer instanceof HTMLElement) {
        try {
            const initialTrend = JSON.parse(trendContainer.dataset.trend ?? '[]');
            renderTrendChart(initialTrend);
        } catch (error) {
            console.warn('Initial trend payload parsing failed', error);
        }
    }

    fetchMetrics(selectedTrendRange);
    window.setInterval(() => {
        fetchMetrics(selectedTrendRange);
    }, 5000);
}

function initializeBillingPolling() {
    const root = document.querySelector('[data-billing-metrics-url]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = root.dataset.billingMetricsUrl;
    if (!metricsUrl) {
        return;
    }

    const kpiNodes = {
        total_billed: root.querySelector('[data-billing-kpi="total_billed"]'),
        paid_amount: root.querySelector('[data-billing-kpi="paid_amount"]'),
        pending_amount: root.querySelector('[data-billing-kpi="pending_amount"]'),
        total_invoices: root.querySelector('[data-billing-kpi="total_invoices"]'),
    };
    const periodButtons = [...root.querySelectorAll('[data-billing-period]')];
    const allowedPeriods = ['all', 'daily', 'weekly', 'monthly', 'yearly'];
    const storageKey = 'motox.billing.period';

    const normalizePeriod = (value) => {
        const normalized = normalizePeriodFilter(value);
        return allowedPeriods.includes(normalized) ? normalized : 'all';
    };

    let selectedPeriod = 'all';
    try {
        selectedPeriod = normalizePeriod(localStorage.getItem(storageKey));
    } catch (error) {
        selectedPeriod = 'all';
    }
    if (!periodButtons.length) {
        selectedPeriod = 'all';
    } else {
        const activeButton = periodButtons.find((button) => button.classList.contains('budget-range-pill-active'));
        if (activeButton) {
            selectedPeriod = normalizePeriod(activeButton.dataset.billingPeriod);
        }
    }

    const moneyFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
    const countFormatter = new Intl.NumberFormat('en-US');

    const setActivePeriodButton = (period) => {
        periodButtons.forEach((button) => {
            button.classList.toggle('budget-range-pill-active', normalizePeriod(button.dataset.billingPeriod) === period);
        });
    };

    const applyStats = (stats) => {
        if (!stats || typeof stats !== 'object') {
            return;
        }

        if (kpiNodes.total_billed instanceof HTMLElement) {
            kpiNodes.total_billed.textContent = `PHP ${moneyFormatter.format(Number(stats.total_billed ?? 0))}`;
        }
        if (kpiNodes.paid_amount instanceof HTMLElement) {
            kpiNodes.paid_amount.textContent = `PHP ${moneyFormatter.format(Number(stats.paid_amount ?? 0))}`;
        }
        if (kpiNodes.pending_amount instanceof HTMLElement) {
            kpiNodes.pending_amount.textContent = `PHP ${moneyFormatter.format(Number(stats.pending_amount ?? 0))}`;
        }
        if (kpiNodes.total_invoices instanceof HTMLElement) {
            kpiNodes.total_invoices.textContent = countFormatter.format(Number(stats.total_invoices ?? 0));
        }
    };

    const buildUrl = (period) => {
        const url = new URL(metricsUrl, window.location.origin);
        url.searchParams.set('period', normalizePeriod(period));
        return `${url.pathname}${url.search}`;
    };

    const fetchMetrics = async (period = selectedPeriod) => {
        try {
            const response = await fetch(buildUrl(period), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyStats(payload?.stats);
            if (Array.isArray(payload?.invoices) && window.MotoXLiveTables) {
                window.MotoXLiveTables.replaceRows('billing', payload.invoices);
            }
        } catch (error) {
            console.warn('Billing metrics polling failed', error);
        }
    };

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectedPeriod = normalizePeriod(button.dataset.billingPeriod);
            setActivePeriodButton(selectedPeriod);
            try {
                localStorage.setItem(storageKey, selectedPeriod);
            } catch (error) {
                console.warn('Unable to persist billing period', error);
            }
            fetchMetrics(selectedPeriod);
        });
    });

    window.addEventListener('motox:date-filter-change', (event) => {
        if (event.detail?.page !== 'billing') {
            return;
        }

        selectedPeriod = normalizePeriod(event.detail?.filter);
        setActivePeriodButton(selectedPeriod);
        try {
            localStorage.setItem(storageKey, selectedPeriod);
        } catch (error) {
            console.warn('Unable to persist billing period', error);
        }
        fetchMetrics(selectedPeriod);
    });

    setActivePeriodButton(selectedPeriod);
    fetchMetrics(selectedPeriod);
    window.setInterval(fetchMetrics, 2000);
}

function initializeCustomersPolling() {
    const root = document.querySelector('[data-customers-metrics-url]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = root.dataset.customersMetricsUrl;
    if (!metricsUrl) {
        return;
    }

    const kpiNodes = {
        total: root.querySelector('[data-customer-kpi="total"]'),
        active_jobs: root.querySelector('[data-customer-kpi="active_jobs"]'),
        new_this_month: root.querySelector('[data-customer-kpi="new_this_month"]'),
    };
    const formatter = new Intl.NumberFormat('en-US');

    const applyStats = (stats) => {
        if (!stats || typeof stats !== 'object') {
            return;
        }

        Object.entries(kpiNodes).forEach(([key, node]) => {
            if (node instanceof HTMLElement) {
                node.textContent = formatter.format(Number(stats[key] ?? 0));
            }
        });
    };

    const fetchMetrics = async () => {
        try {
            const response = await fetch(metricsUrl, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyStats(payload?.stats);
        } catch (error) {
            console.warn('Customers metrics polling failed', error);
        }
    };

    fetchMetrics();
    window.setInterval(fetchMetrics, 12000);
}

function initializeJobOrdersPolling() {
    const root = document.querySelector('[data-joborders-metrics-url]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = root.dataset.jobordersMetricsUrl;
    if (!metricsUrl) {
        return;
    }

    const kpiNodes = {
        total_orders: root.querySelector('[data-joborder-kpi="total_orders"]'),
        pending: root.querySelector('[data-joborder-kpi="pending"]'),
        in_progress: root.querySelector('[data-joborder-kpi="in_progress"]'),
        completed: root.querySelector('[data-joborder-kpi="completed"]'),
        estimated_value: root.querySelector('[data-joborder-kpi="estimated_value"]'),
    };

    const countFormatter = new Intl.NumberFormat('en-US');
    const moneyFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const applyStats = (stats) => {
        if (!stats || typeof stats !== 'object') {
            return;
        }

        if (kpiNodes.total_orders instanceof HTMLElement) {
            kpiNodes.total_orders.textContent = countFormatter.format(Number(stats.total_orders ?? 0));
        }
        if (kpiNodes.pending instanceof HTMLElement) {
            kpiNodes.pending.textContent = countFormatter.format(Number(stats.pending ?? 0));
        }
        if (kpiNodes.in_progress instanceof HTMLElement) {
            kpiNodes.in_progress.textContent = countFormatter.format(Number(stats.in_progress ?? 0));
        }
        if (kpiNodes.completed instanceof HTMLElement) {
            kpiNodes.completed.textContent = countFormatter.format(Number(stats.completed ?? 0));
        }
        if (kpiNodes.estimated_value instanceof HTMLElement) {
            kpiNodes.estimated_value.textContent = `PHP ${moneyFormatter.format(Number(stats.estimated_value ?? 0))}`;
        }
    };

    const fetchMetrics = async () => {
        try {
            const response = await fetch(metricsUrl, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyStats(payload?.stats);
        } catch (error) {
            console.warn('Job order metrics polling failed', error);
        }
    };

    fetchMetrics();
    window.setInterval(fetchMetrics, 12000);
}

function initializeReportsRevenueChart() {
    const reportsRoot = document.querySelector('[data-reports-metrics-url]');
    const chartContainer = reportsRoot instanceof HTMLElement
        ? reportsRoot.querySelector('[data-chart="report-revenue"]')
        : document.querySelector('[data-chart="report-revenue"]');

    if (!(chartContainer instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = reportsRoot instanceof HTMLElement ? reportsRoot.dataset.reportsMetricsUrl : '';
    const rangeButtons = reportsRoot instanceof HTMLElement
        ? [...reportsRoot.querySelectorAll('[data-report-range]')]
        : [...document.querySelectorAll('[data-report-range]')];
    const rangeSelect = reportsRoot instanceof HTMLElement
        ? reportsRoot.querySelector('[data-report-range-select]')
        : document.querySelector('[data-report-range-select]');
    const periodButtons = reportsRoot instanceof HTMLElement
        ? [...reportsRoot.querySelectorAll('[data-report-period]')]
        : [...document.querySelectorAll('[data-report-period]')];
    const statusNodes = reportsRoot instanceof HTMLElement
        ? [...reportsRoot.querySelectorAll('[data-report-status]')]
        : [...document.querySelectorAll('[data-report-status]')];

    const kpiNodes = {
        month_revenue: reportsRoot?.querySelector('[data-report-kpi="month_revenue"]'),
        growth_rate: reportsRoot?.querySelector('[data-report-kpi="growth_rate"]'),
        jobs_closed: reportsRoot?.querySelector('[data-report-kpi="jobs_closed"]'),
        inventory_value: reportsRoot?.querySelector('[data-report-kpi="inventory_value"]'),
    };

    const summaryNodes = {
        latest: reportsRoot?.querySelector('[data-report-summary="latest"]'),
        average: reportsRoot?.querySelector('[data-report-summary="average"]'),
        peak: reportsRoot?.querySelector('[data-report-summary="peak"]'),
    };
    const updatedAtNode = reportsRoot?.querySelector('[data-reports-updated-at]');

    const reportRangeStorageKey = 'motox.reports.range.half';
    const legacyReportRangeStorageKey = 'motox.reports.range.months';
    const reportPeriodStorageKey = 'motox.reports.period';
    const allowedRanges = ['jan-jun', 'jul-dec'];
    const allowedPeriods = ['all', 'daily', 'weekly', 'monthly', 'yearly'];
    const normalizeRange = (value) => {
        const normalized = String(value ?? '').trim().toLowerCase();
        return allowedRanges.includes(normalized) ? normalized : 'jan-jun';
    };
    const normalizePeriod = (value) => {
        const normalized = normalizePeriodFilter(value);
        return allowedPeriods.includes(normalized) ? normalized : 'all';
    };

    let series = [];
    let selectedRange = 'jan-jun';
    let selectedPeriod = 'all';

    try {
        const parsed = JSON.parse(chartContainer.dataset.series ?? '[]');
        if (Array.isArray(parsed)) {
            series = parsed;
        }
    } catch (error) {
        console.warn('Report trend payload parsing failed', error);
    }

    try {
        selectedRange = normalizeRange(
            localStorage.getItem(reportRangeStorageKey)
                ?? localStorage.getItem(legacyReportRangeStorageKey)
        );
    } catch (error) {
        selectedRange = 'jan-jun';
    }

    try {
        selectedPeriod = normalizePeriod(localStorage.getItem(reportPeriodStorageKey));
    } catch (error) {
        selectedPeriod = 'all';
    }
    if (!periodButtons.length) {
        selectedPeriod = 'all';
    }

    const moneyFormatter = new Intl.NumberFormat('en-US', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 2,
    });
    const countFormatter = new Intl.NumberFormat('en-US');
    const percentFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    });

    const setActiveRange = (range) => {
        rangeButtons.forEach((button) => {
            button.classList.toggle('budget-range-pill-active', normalizeRange(button.dataset.reportRange) === range);
        });

        if (rangeSelect instanceof HTMLSelectElement) {
            rangeSelect.value = normalizeRange(range);
        }
    };

    const setActivePeriod = (period) => {
        periodButtons.forEach((button) => {
            button.classList.toggle('budget-range-pill-active', normalizePeriod(button.dataset.reportPeriod) === period);
        });
    };

    const applyStatusBreakdown = (rows) => {
        if (!Array.isArray(rows) || !rows.length) {
            return;
        }

        const renderStatusOrders = (orders) => {
            if (!Array.isArray(orders) || !orders.length) {
                return '<p class="report-status-empty">No job orders in this status.</p>';
            }

            return orders.map((order) => {
                const customer = escapeHtml(order?.customer ?? 'Walk-in Customer');
                const image = order?.profile_photo_url
                    ? `<img src="${escapeHtml(order.profile_photo_url)}" alt="${customer} profile" class="report-status-avatar" loading="lazy" decoding="async">`
                    : `<span class="report-status-avatar report-status-avatar-fallback">${escapeHtml(order?.initials ?? 'WI')}</span>`;

                return `
                    <div class="report-status-order">
                        ${image}
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">${customer}</p>
                            <p class="truncate text-xs text-slate-500">${escapeHtml(order?.order_number ?? '-')} - ${escapeHtml(order?.vehicle || 'No vehicle')}</p>
                        </div>
                        <span class="report-status-date">${escapeHtml(order?.date_display ?? '-')}</span>
                    </div>
                `;
            }).join('');
        };

        rows.forEach((row) => {
            const key = String(row?.key ?? row?.status ?? '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '_');

            if (!key) {
                return;
            }

            const target = statusNodes.find((node) => node instanceof HTMLElement && node.dataset.reportStatus === key);
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const countNode = target.querySelector('[data-report-status-count]');
            if (countNode instanceof HTMLElement) {
                countNode.textContent = countFormatter.format(Number(row?.count ?? 0));
            }

            const ordersNode = target.querySelector('[data-report-status-orders]');
            if (ordersNode instanceof HTMLElement) {
                ordersNode.innerHTML = renderStatusOrders(row?.orders);
            }
        });
    };

    const rangeLabel = (range) => normalizeRange(range) === 'jul-dec' ? 'Jul-Dec' : 'Jan-Jun';

    const seriesForRange = (range) => {
        const normalized = normalizeRange(range);
        const start = normalized === 'jul-dec' ? 6 : 0;

        return series.slice(start, start + 6);
    };

    const renderSeries = (range) => {
        if (!series.length) {
            chartContainer.innerHTML = '<p class="text-sm text-slate-500">No monthly revenue data available yet.</p>';
            return;
        }

        const normalizedRange = normalizeRange(range);
        const windowedSeries = seriesForRange(normalizedRange);
        const values = windowedSeries.map((row) => Math.max(0, Number(row?.value || 0)));
        const peakValue = Math.max(0, ...values);
        const scaleBase = Math.max(1, peakValue);
        const maxPercent = 100;
        const axisValues = Array.from({ length: 6 }, (_, index) => 100 - (index * 20));

        const { width, height } = chartSvgSize(chartContainer);
        const padding = { top: 20, right: 18, bottom: 34, left: 82 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const stepX = windowedSeries.length > 1 ? chartWidth / (windowedSeries.length - 1) : 0;
        const baselineY = height - padding.bottom;

        const points = values.map((value, index) => {
            const x = padding.left + (stepX * index);
            const percent = Math.min(maxPercent, Math.max(0, (value / scaleBase) * 100));
            const y = padding.top + ((1 - (percent / maxPercent)) * chartHeight);

            return { x, y };
        });

        const linePoints = points.map((point) => `${point.x.toFixed(2)},${point.y.toFixed(2)}`).join(' ');
        const areaPath = points.length
            ? `M ${points[0].x.toFixed(2)} ${baselineY.toFixed(2)} ${points
                .map((point) => `L ${point.x.toFixed(2)} ${point.y.toFixed(2)}`)
                .join(' ')} L ${points[points.length - 1].x.toFixed(2)} ${baselineY.toFixed(2)} Z`
            : '';

        const horizontalGrid = Array.from({ length: 6 }, (_, index) => {
            const y = padding.top + ((chartHeight / 5) * index);
            const labelValue = axisValues[index] ?? 0;
            return `
                <text class="budget-grid-label" x="${(padding.left - 12).toFixed(2)}" y="${(y + 4).toFixed(2)}">${labelValue}%</text>
                <line class="budget-grid-line" x1="${padding.left}" y1="${y.toFixed(2)}" x2="${(width - padding.right).toFixed(2)}" y2="${y.toFixed(2)}"></line>
            `;
        }).join('');

        const verticalGrid = windowedSeries.map((_, index) => {
            const x = padding.left + (stepX * index);
            return `<line class="budget-grid-line-vertical" x1="${x.toFixed(2)}" y1="${padding.top}" x2="${x.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>`;
        }).join('');

        const axisLabels = windowedSeries.map((row) => `
            <span class="budget-axis-label">${escapeHtml(row?.label ?? '')}</span>
        `).join('');
        const lastRevenueIndex = values.reduce((latest, value, index) => value > 0 ? index : latest, -1);
        const focusIndex = Math.max(0, lastRevenueIndex >= 0 ? lastRevenueIndex : points.length - 1);
        const focusPoint = points[focusIndex] ?? points[points.length - 1];
        const focusRow = windowedSeries[focusIndex] ?? windowedSeries[windowedSeries.length - 1] ?? {};
        const focusValue = values[focusIndex] ?? 0;
        const previousValue = values[Math.max(0, focusIndex - 1)] ?? 0;
        const tooltipLeft = focusPoint ? Math.min(78, Math.max(22, (focusPoint.x / width) * 100)) : 50;
        const deltaForIndex = (index) => {
            const current = values[index] ?? 0;
            const previous = values[Math.max(0, index - 1)] ?? 0;

            return previous > 0
                ? ((current - previous) / previous) * 100
                : (current > 0 ? 100 : 0);
        };
        const formatSignedPercent = (value) => `${value >= 0 ? '+' : ''}${percentFormatter.format(value)}%`;

        chartContainer.style.setProperty('--budget-count', String(Math.max(1, windowedSeries.length)));
        chartContainer.innerHTML = `
            <div class="budget-chart-shell">
                <svg viewBox="0 0 ${width} ${height}" class="budget-chart-svg" role="img" aria-label="Monthly revenue trend chart">
                    <defs>
                        <linearGradient id="reportRevenueGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(251, 191, 36, 0.34)" />
                            <stop offset="58%" stop-color="rgba(249, 115, 22, 0.16)" />
                            <stop offset="100%" stop-color="rgba(249, 115, 22, 0.035)" />
                        </linearGradient>
                    </defs>
                    ${horizontalGrid}
                    ${verticalGrid}
                    <path d="${areaPath}" fill="url(#reportRevenueGradient)"></path>
                    <polyline class="budget-line budget-line-revenue budget-line-pencil" points="${linePoints}"></polyline>
                    ${focusPoint ? `<line class="budget-guide-line" data-report-guide x1="${focusPoint.x.toFixed(2)}" y1="${padding.top}" x2="${focusPoint.x.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>` : ''}
                    ${points.map((point, index) => `<circle class="budget-point budget-point-revenue budget-point-hoverable" data-report-point="${index}" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4.5"></circle>`).join('')}
                    ${focusPoint ? `<circle class="budget-point budget-point-revenue budget-point-focus" data-report-focus cx="${focusPoint.x.toFixed(2)}" cy="${focusPoint.y.toFixed(2)}" r="6"></circle>` : ''}
                </svg>
                <article class="budget-tooltip-card" data-report-tooltip style="left: ${tooltipLeft}%;">
                    <div class="budget-tooltip-head">
                        <p class="budget-tooltip-date" data-report-tooltip-date>${escapeHtml(focusRow?.date_label ?? focusRow?.label ?? 'Current')}</p>
                        <span class="budget-tooltip-total" data-report-tooltip-label>${escapeHtml(focusRow?.label ?? 'Current')}</span>
                    </div>
                    <div class="budget-tooltip-divider"></div>
                    <div class="budget-tooltip-row">
                        <div>
                            <p class="budget-tooltip-amount" data-report-tooltip-amount>PHP ${moneyFormatter.format(focusValue)}</p>
                            <p class="budget-tooltip-caption">Revenue</p>
                        </div>
                        <span class="budget-tooltip-change budget-tooltip-change-in" data-report-tooltip-change>${formatSignedPercent(deltaForIndex(focusIndex))}</span>
                    </div>
                </article>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                <span class="inline-flex items-center gap-2"><i class="budget-legend-dot budget-legend-dot-revenue"></i>Revenue</span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Scale 0-100%</span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Peak PHP ${moneyFormatter.format(peakValue)}</span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">${rangeLabel(normalizedRange)} Window</span>
            </div>
            <div class="budget-axis-row">${axisLabels}</div>
        `;

        const shell = chartContainer.querySelector('.budget-chart-shell');
        const guide = chartContainer.querySelector('[data-report-guide]');
        const focus = chartContainer.querySelector('[data-report-focus]');
        const tooltip = chartContainer.querySelector('[data-report-tooltip]');
        const tooltipDate = chartContainer.querySelector('[data-report-tooltip-date]');
        const tooltipLabel = chartContainer.querySelector('[data-report-tooltip-label]');
        const tooltipAmount = chartContainer.querySelector('[data-report-tooltip-amount]');
        const tooltipChange = chartContainer.querySelector('[data-report-tooltip-change]');
        const pointNodes = [...chartContainer.querySelectorAll('[data-report-point]')];

        const setFocusIndex = (index) => {
            const point = points[index];
            const row = windowedSeries[index] ?? {};

            if (!point) {
                return;
            }

            if (guide instanceof SVGLineElement) {
                guide.setAttribute('x1', point.x.toFixed(2));
                guide.setAttribute('x2', point.x.toFixed(2));
            }
            if (focus instanceof SVGCircleElement) {
                focus.setAttribute('cx', point.x.toFixed(2));
                focus.setAttribute('cy', point.y.toFixed(2));
            }
            pointNodes.forEach((node) => {
                node.classList.toggle('budget-point-active', Number(node.dataset.reportPoint) === index);
            });
            if (tooltip instanceof HTMLElement) {
                tooltip.style.left = `${Math.min(78, Math.max(22, (point.x / width) * 100))}%`;
            }
            if (tooltipDate instanceof HTMLElement) {
                tooltipDate.textContent = row?.date_label ?? row?.label ?? 'Current';
            }
            if (tooltipLabel instanceof HTMLElement) {
                tooltipLabel.textContent = row?.label ?? 'Current';
            }
            if (tooltipAmount instanceof HTMLElement) {
                tooltipAmount.textContent = `PHP ${moneyFormatter.format(values[index] ?? 0)}`;
            }
            if (tooltipChange instanceof HTMLElement) {
                tooltipChange.textContent = formatSignedPercent(deltaForIndex(index));
            }
        };

        if (shell instanceof HTMLElement && points.length) {
            shell.addEventListener('pointermove', (event) => {
                const rect = shell.getBoundingClientRect();
                const chartX = ((event.clientX - rect.left) / Math.max(1, rect.width)) * width;
                const nearestIndex = points.reduce((nearest, point, index) => {
                    const currentDistance = Math.abs(point.x - chartX);
                    const nearestDistance = Math.abs(points[nearest].x - chartX);

                    return currentDistance < nearestDistance ? index : nearest;
                }, 0);

                setFocusIndex(nearestIndex);
            });

            shell.addEventListener('pointerleave', () => {
                setFocusIndex(points.length - 1);
            });
        }
    };

    const applyReportPayload = (payload) => {
        if (payload?.stats && typeof payload.stats === 'object') {
            const stats = payload.stats;

            if (kpiNodes.month_revenue instanceof HTMLElement) {
                kpiNodes.month_revenue.textContent = `PHP ${moneyFormatter.format(Number(stats.month_revenue ?? 0))}`;
            }
            if (kpiNodes.jobs_closed instanceof HTMLElement) {
                kpiNodes.jobs_closed.textContent = countFormatter.format(Number(stats.jobs_closed ?? 0));
            }
            if (kpiNodes.inventory_value instanceof HTMLElement) {
                kpiNodes.inventory_value.textContent = `PHP ${moneyFormatter.format(Number(stats.inventory_value ?? 0))}`;
            }
            if (kpiNodes.growth_rate instanceof HTMLElement) {
                const rate = Number(stats.growth_rate ?? 0);
                kpiNodes.growth_rate.textContent = `${rate >= 0 ? '+' : ''}${percentFormatter.format(rate)}%`;
                kpiNodes.growth_rate.classList.toggle('text-emerald-600', rate >= 0);
                kpiNodes.growth_rate.classList.toggle('text-rose-600', rate < 0);
            }
        }

        if (payload?.monthly_trend_summary && typeof payload.monthly_trend_summary === 'object') {
            if (summaryNodes.latest instanceof HTMLElement) {
                summaryNodes.latest.textContent = String(payload.monthly_trend_summary.latest ?? '-');
            }
            if (summaryNodes.average instanceof HTMLElement) {
                summaryNodes.average.textContent = String(payload.monthly_trend_summary.average ?? '-');
            }
            if (summaryNodes.peak instanceof HTMLElement) {
                summaryNodes.peak.textContent = String(payload.monthly_trend_summary.peak ?? '-');
            }
        }

        if (Array.isArray(payload?.monthly_trend) && payload.monthly_trend.length) {
            series = payload.monthly_trend;
        }

        if (Array.isArray(payload?.status_breakdown)) {
            applyStatusBreakdown(payload.status_breakdown);
        }

        if (Array.isArray(payload?.top_customers) && window.MotoXLiveTables) {
            window.MotoXLiveTables.replaceRows('reports', payload.top_customers);
        }

        renderSeries(selectedRange);
    };

    const buildMetricsUrl = (period) => {
        const url = new URL(metricsUrl, window.location.origin);
        url.searchParams.set('period', normalizePeriod(period));
        return `${url.pathname}${url.search}`;
    };

    const fetchMetrics = async () => {
        if (!metricsUrl) {
            return;
        }

        try {
            const response = await fetch(buildMetricsUrl(selectedPeriod), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyReportPayload(payload);
        } catch (error) {
            console.warn('Reports metrics polling failed', error);
        }
    };

    setActiveRange(selectedRange);
    setActivePeriod(selectedPeriod);
    renderSeries(selectedRange);

    rangeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const range = normalizeRange(button.dataset.reportRange);
            selectedRange = range;
            setActiveRange(range);
            renderSeries(range);

            try {
                localStorage.setItem(reportRangeStorageKey, range);
                localStorage.removeItem(legacyReportRangeStorageKey);
            } catch (error) {
                console.warn('Unable to persist reports trend range', error);
            }
        });
    });

    if (rangeSelect instanceof HTMLSelectElement) {
        rangeSelect.addEventListener('change', () => {
            const range = normalizeRange(rangeSelect.value);
            selectedRange = range;
            setActiveRange(range);
            renderSeries(range);

            try {
                localStorage.setItem(reportRangeStorageKey, range);
                localStorage.removeItem(legacyReportRangeStorageKey);
            } catch (error) {
                console.warn('Unable to persist reports trend range', error);
            }
        });
    }

    const downloadTrigger = document.querySelector('[data-report-download-trigger]');
    const downloadMenu = document.querySelector('[data-report-download-menu]');
    if (downloadTrigger instanceof HTMLButtonElement && downloadMenu instanceof HTMLElement) {
        downloadTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            downloadMenu.classList.toggle('hidden');
        });

        downloadMenu.addEventListener('click', () => {
            downloadMenu.classList.add('hidden');
        });

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Node)) {
                return;
            }

            if (!downloadTrigger.contains(target) && !downloadMenu.contains(target)) {
                downloadMenu.classList.add('hidden');
            }
        });
    }

    const downloadPngButton = document.querySelector('[data-download-report-png]');
    if (downloadPngButton instanceof HTMLButtonElement) {
        downloadPngButton.addEventListener('click', () => {
            const originalText = downloadPngButton.textContent;
            downloadPngButton.disabled = true;
            downloadPngButton.textContent = 'Preparing PNG...';

            window.requestAnimationFrame(() => {
                exportReportRevenueChartToPng({
                    filename: `motox-report-chart-${selectedPeriod}-${selectedRange}.png`,
                    months: 6,
                    rangeLabel: rangeLabel(selectedRange),
                    period: selectedPeriod,
                    series: seriesForRange(selectedRange),
                    summary: {
                        latest: summaryNodes.latest instanceof HTMLElement ? summaryNodes.latest.textContent : '',
                        average: summaryNodes.average instanceof HTMLElement ? summaryNodes.average.textContent : '',
                        peak: summaryNodes.peak instanceof HTMLElement ? summaryNodes.peak.textContent : '',
                    },
                })
                    .catch((error) => {
                        console.warn('Report PNG export failed', error);
                    })
                    .finally(() => {
                        downloadPngButton.disabled = false;
                        downloadPngButton.textContent = originalText || 'PNG Report';
                    });
            });
        });
    }

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => {
            selectedPeriod = normalizePeriod(button.dataset.reportPeriod);
            setActivePeriod(selectedPeriod);

            try {
                localStorage.setItem(reportPeriodStorageKey, selectedPeriod);
            } catch (error) {
                console.warn('Unable to persist reports period', error);
            }

            fetchMetrics();
        });
    });

    window.addEventListener('motox:date-filter-change', (event) => {
        if (event.detail?.page !== 'reports') {
            return;
        }

        selectedPeriod = normalizePeriod(event.detail?.filter);
        setActivePeriod(selectedPeriod);
        try {
            localStorage.setItem(reportPeriodStorageKey, selectedPeriod);
        } catch (error) {
            console.warn('Unable to persist reports period', error);
        }
        fetchMetrics();
    });

    fetchMetrics();
    if (metricsUrl) {
        window.setInterval(fetchMetrics, 7000);
    }
}

async function exportReportRevenueChartToPng({ filename, months, rangeLabel, period, series, summary }) {
    const rows = Array.isArray(series) ? series : [];
    const width = 1400;
    const height = 820;
    const scale = Math.min(2, Math.max(1, window.devicePixelRatio || 1));
    const canvas = document.createElement('canvas');
    canvas.width = Math.ceil(width * scale);
    canvas.height = Math.ceil(height * scale);

    const context = canvas.getContext('2d');
    if (!context) {
        return;
    }

    context.scale(scale, scale);
    context.fillStyle = '#f7f8fb';
    context.fillRect(0, 0, width, height);

    fillRoundedRect(context, 48, 48, width - 96, height - 96, 28, '#ffffff');
    context.strokeStyle = '#e2e8f0';
    context.lineWidth = 1;
    strokeRoundedRect(context, 48, 48, width - 96, height - 96, 28);

    context.fillStyle = '#0f172a';
    context.font = '800 42px Manrope, Arial, sans-serif';
    context.fillText('Monthly Revenue Trend', 92, 124);

    context.fillStyle = '#64748b';
    context.font = '600 20px Manrope, Arial, sans-serif';
    context.fillText(`${periodLabel(period)} - ${rangeLabel || `${months}-month`} window`, 92, 160);

    context.textAlign = 'right';
    context.fillStyle = '#f97316';
    context.font = '800 26px Manrope, Arial, sans-serif';
    context.fillText('MotoX Reports', width - 92, 126);
    context.fillStyle = '#94a3b8';
    context.font = '600 16px Manrope, Arial, sans-serif';
    context.fillText(new Date().toLocaleString('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }), width - 92, 158);
    context.textAlign = 'left';

    const chart = {
        x: 112,
        y: 228,
        width: width - 224,
        height: 360,
    };
    const values = rows.map((row) => Math.max(0, Number(row?.value || 0)));
    const peakValue = Math.max(0, ...values);
    const scaleBase = Math.max(1, peakValue);
    const moneyFormatter = new Intl.NumberFormat('en-US', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 2,
    });

    context.strokeStyle = '#e2e8f0';
    context.lineWidth = 1;
    context.fillStyle = '#64748b';
    context.font = '600 15px Manrope, Arial, sans-serif';

    for (let index = 0; index <= 5; index += 1) {
        const y = chart.y + ((chart.height / 5) * index);
        const value = 100 - (index * 20);
        context.beginPath();
        context.moveTo(chart.x, y);
        context.lineTo(chart.x + chart.width, y);
        context.stroke();
        context.fillText(`${value}%`, chart.x, y - 8);
    }

    if (!rows.length) {
        context.fillStyle = '#64748b';
        context.font = '700 24px Manrope, Arial, sans-serif';
        context.textAlign = 'center';
        context.fillText('No monthly revenue data available yet.', width / 2, chart.y + chart.height / 2);
        context.textAlign = 'left';
    } else {
        const stepX = rows.length > 1 ? chart.width / (rows.length - 1) : 0;
        const points = values.map((value, index) => ({
            x: chart.x + (stepX * index),
            y: chart.y + ((1 - (Math.min(100, Math.max(0, (value / scaleBase) * 100)) / 100)) * chart.height),
        }));

        const areaGradient = context.createLinearGradient(0, chart.y, 0, chart.y + chart.height);
        areaGradient.addColorStop(0, 'rgba(251, 191, 36, 0.34)');
        areaGradient.addColorStop(0.58, 'rgba(249, 115, 22, 0.16)');
        areaGradient.addColorStop(1, 'rgba(249, 115, 22, 0.035)');

        context.beginPath();
        context.moveTo(points[0].x, chart.y + chart.height);
        points.forEach((point) => context.lineTo(point.x, point.y));
        context.lineTo(points[points.length - 1].x, chart.y + chart.height);
        context.closePath();
        context.fillStyle = areaGradient;
        context.fill();

        context.beginPath();
        points.forEach((point, index) => {
            if (index === 0) {
                context.moveTo(point.x, point.y);
                return;
            }

            context.lineTo(point.x, point.y);
        });
        context.strokeStyle = '#f59e0b';
        context.lineWidth = 5;
        context.lineJoin = 'round';
        context.lineCap = 'round';
        context.stroke();

        points.forEach((point) => {
            context.beginPath();
            context.arc(point.x, point.y, 7, 0, Math.PI * 2);
            context.fillStyle = '#ffffff';
            context.fill();
            context.strokeStyle = '#f59e0b';
            context.lineWidth = 4;
            context.stroke();
        });

        const lastRevenueIndex = values.reduce((latest, value, index) => value > 0 ? index : latest, -1);
        const focusIndex = Math.max(0, lastRevenueIndex >= 0 ? lastRevenueIndex : points.length - 1);
        const focusPoint = points[focusIndex];
        const focusRow = rows[focusIndex] ?? {};
        const focusValue = values[focusIndex] ?? 0;
        const tooltipX = Math.min(chart.x + chart.width - 260, Math.max(chart.x + 16, focusPoint.x - 130));
        const tooltipY = Math.max(chart.y + 20, focusPoint.y - 132);

        fillRoundedRect(context, tooltipX, tooltipY, 260, 104, 18, '#0f172a');
        context.fillStyle = '#cbd5e1';
        context.font = '700 14px Manrope, Arial, sans-serif';
        drawClampedText(context, focusRow?.date_label ?? focusRow?.label ?? 'Current', tooltipX + 18, tooltipY + 30, 224);
        context.fillStyle = '#ffffff';
        context.font = '800 25px Manrope, Arial, sans-serif';
        drawClampedText(context, `PHP ${moneyFormatter.format(focusValue)}`, tooltipX + 18, tooltipY + 66, 224);
        context.fillStyle = '#fdba74';
        context.font = '700 14px Manrope, Arial, sans-serif';
        context.fillText('Revenue', tooltipX + 18, tooltipY + 88);

        context.fillStyle = '#475569';
        context.font = '700 16px Manrope, Arial, sans-serif';
        context.textAlign = 'center';
        rows.forEach((row, index) => {
            const point = points[index];
            drawClampedText(context, row?.label ?? '', point.x, chart.y + chart.height + 42, 74);
        });
        context.textAlign = 'left';
    }

    const summaryRows = [
        ['Latest Month', summary?.latest || '-'],
        ['12-Month Average', summary?.average || '-'],
        ['Peak Month', summary?.peak || '-'],
    ];
    const cardWidth = (width - 224 - 32) / 3;
    summaryRows.forEach(([label, value], index) => {
        const x = 112 + (index * (cardWidth + 16));
        const y = 650;
        fillRoundedRect(context, x, y, cardWidth, 96, 18, '#f8fafc');
        context.strokeStyle = '#e2e8f0';
        context.lineWidth = 1;
        strokeRoundedRect(context, x, y, cardWidth, 96, 18);
        context.fillStyle = '#64748b';
        context.font = '800 13px Manrope, Arial, sans-serif';
        context.fillText(label.toUpperCase(), x + 20, y + 34);
        context.fillStyle = '#0f172a';
        context.font = '800 25px Manrope, Arial, sans-serif';
        drawClampedText(context, value, x + 20, y + 68, cardWidth - 40);
    });

    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function periodLabel(value) {
    const labels = {
        all: 'All time',
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        yearly: 'Yearly',
    };

    return labels[normalizePeriodFilter(value)] ?? labels.all;
}

function compactCurrency(value) {
    const amount = Number(value || 0);

    if (amount >= 1000000) {
        return `${(amount / 1000000).toFixed(1)}M`;
    }

    if (amount >= 1000) {
        return `${(amount / 1000).toFixed(1)}K`;
    }

    return amount.toFixed(0);
}

function drawClampedText(context, value, x, y, maxWidth) {
    const text = String(value ?? '');

    if (context.measureText(text).width <= maxWidth) {
        context.fillText(text, x, y);
        return;
    }

    let truncated = text;
    while (truncated.length > 4 && context.measureText(`${truncated}...`).width > maxWidth) {
        truncated = truncated.slice(0, -1);
    }

    context.fillText(`${truncated}...`, x, y);
}

function fillRoundedRect(context, x, y, width, height, radius, fillStyle) {
    roundedRectPath(context, x, y, width, height, radius);
    context.fillStyle = fillStyle;
    context.fill();
}

function strokeRoundedRect(context, x, y, width, height, radius) {
    roundedRectPath(context, x, y, width, height, radius);
    context.stroke();
}

function roundedRectPath(context, x, y, width, height, radius) {
    const normalizedRadius = Math.min(radius, width / 2, height / 2);

    context.beginPath();
    context.moveTo(x + normalizedRadius, y);
    context.lineTo(x + width - normalizedRadius, y);
    context.quadraticCurveTo(x + width, y, x + width, y + normalizedRadius);
    context.lineTo(x + width, y + height - normalizedRadius);
    context.quadraticCurveTo(x + width, y + height, x + width - normalizedRadius, y + height);
    context.lineTo(x + normalizedRadius, y + height);
    context.quadraticCurveTo(x, y + height, x, y + height - normalizedRadius);
    context.lineTo(x, y + normalizedRadius);
    context.quadraticCurveTo(x, y, x + normalizedRadius, y);
    context.closePath();
}

function initializeLandingMetricsPolling() {
    const root = document.querySelector('[data-landing-metrics-url]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const metricsUrl = root.dataset.landingMetricsUrl;
    if (!metricsUrl) {
        return;
    }

    const valueNodes = [...root.querySelectorAll('[data-landing-value]')];
    const updatedNode = root.querySelector('[data-landing-updated]');

    const applyValues = (rows) => {
        if (!Array.isArray(rows)) {
            return;
        }

        rows.forEach((row) => {
            const key = row?.key;
            const value = row?.value;
            if (typeof key !== 'string' || typeof value !== 'string') {
                return;
            }

            valueNodes.forEach((node) => {
                if (node instanceof HTMLElement && node.dataset.landingValue === key) {
                    node.textContent = value;
                }
            });
        });
    };

    const applyPayload = (payload) => {
        applyValues(payload?.projectSnapshot);
        applyValues(payload?.workspacePulse);
        applyValues(payload?.timeWindows);

        if (updatedNode instanceof HTMLElement && typeof payload?.landingUpdatedAt === 'string') {
            updatedNode.textContent = payload.landingUpdatedAt;
        }
    };

    const fetchMetrics = async () => {
        try {
            const response = await fetch(metricsUrl, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyPayload(payload);
        } catch (error) {
            console.warn('Landing metrics polling failed', error);
        }
    };

    fetchMetrics();
    window.setInterval(fetchMetrics, 15000);
}

function initializeLandingRefreshLoader() {
    const loader = document.querySelector('[data-landing-loader]');
    const landingRoot = document.querySelector('.landing-page');

    if (!(loader instanceof HTMLElement) || !(landingRoot instanceof HTMLElement)) {
        return;
    }

    document.body.classList.add('landing-is-loading');

    const hideLoader = () => {
        loader.classList.add('landing-refresh-loader-hidden');
        document.body.classList.remove('landing-is-loading');

        window.setTimeout(() => {
            loader.remove();
        }, 420);
    };

    const minimumDelay = 650;
    const startedAt = performance.now();
    const finish = () => {
        const elapsed = performance.now() - startedAt;
        window.setTimeout(hideLoader, Math.max(0, minimumDelay - elapsed));
    };

    if (document.readyState === 'complete') {
        finish();
        return;
    }

    window.addEventListener('load', finish, { once: true });
}

function initializeLandingBackgroundMotion() {
    const landingRoot = document.querySelector('.landing-page');
    if (!(landingRoot instanceof HTMLElement)) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        landingRoot.style.setProperty('--landing-scroll-progress', '0');
        return;
    }

    let ticking = false;

    const applyScrollProgress = () => {
        const scrollTop = window.scrollY;
        const scrollHeight = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const progress = Math.min(1, Math.max(0, scrollTop / scrollHeight));

        landingRoot.style.setProperty('--landing-scroll-progress', progress.toFixed(4));
        ticking = false;
    };

    const requestTick = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(applyScrollProgress);
    };

    applyScrollProgress();
    window.addEventListener('scroll', requestTick, { passive: true });
    window.addEventListener('resize', requestTick);
}

function initializeLandingScrollReveal() {
    const revealItems = [...document.querySelectorAll('.landing-reveal')];

    if (!revealItems.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.18,
        rootMargin: '0px 0px -8% 0px',
    });

    revealItems.forEach((item) => observer.observe(item));
}

function initializeLandingSectionTracking() {
    const navLinks = [...document.querySelectorAll('.landing-nav a[href^="#"]')];

    if (!navLinks.length) {
        return;
    }

    const sectionItems = navLinks.map((link) => {
        const targetId = link.getAttribute('href');
        if (!targetId) {
            return null;
        }

        const section = document.querySelector(targetId);
        if (!(section instanceof HTMLElement)) {
            return null;
        }

        return { link, section, targetId };
    }).filter(Boolean);

    if (!sectionItems.length) {
        return;
    }

    const setActiveLink = (targetId) => {
        sectionItems.forEach((item) => {
            const isActive = item.targetId === targetId;
            item.link.classList.toggle('landing-nav-link-active', isActive);
            item.link.setAttribute('aria-current', isActive ? 'page' : 'false');
        });
    };

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    sectionItems.forEach((item) => {
        item.link.addEventListener('click', (event) => {
            event.preventDefault();
            setActiveLink(item.targetId);

            item.section.scrollIntoView({
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
                block: 'start',
            });

            history.replaceState(null, '', item.targetId);
        });
    });

    const thresholdSteps = [0.2, 0.35, 0.55, 0.75];
    const observer = new IntersectionObserver((entries) => {
        const visibleEntry = entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (!visibleEntry) {
            return;
        }

        const activeSection = sectionItems.find((item) => item.section === visibleEntry.target);
        if (activeSection) {
            setActiveLink(activeSection.targetId);
        }
    }, {
        rootMargin: '-24% 0px -52% 0px',
        threshold: thresholdSteps,
    });

    sectionItems.forEach((item) => observer.observe(item.section));

    const initialTargetId = sectionItems.some((item) => item.targetId === window.location.hash)
        ? window.location.hash
        : sectionItems[0].targetId;

    setActiveLink(initialTargetId);
}

function initializeHeaderMenus() {
    const triggers = [...document.querySelectorAll('[data-header-menu-trigger]')];

    if (!triggers.length) {
        return;
    }

    const closeAllMenus = () => {
        triggers.forEach((trigger) => {
            const name = trigger.dataset.headerMenuTrigger;
            const panel = name ? document.querySelector(`[data-header-menu-panel="${name}"]`) : null;

            trigger.setAttribute('aria-expanded', 'false');

            if (panel instanceof HTMLElement) {
                panel.classList.add('hidden');
            }
        });
    };

    triggers.forEach((trigger) => {
        let closeTimer = null;
        const openMenu = () => {
            const name = trigger.dataset.headerMenuTrigger;
            const panel = name ? document.querySelector(`[data-header-menu-panel="${name}"]`) : null;

            if (!(panel instanceof HTMLElement)) {
                return;
            }

            window.clearTimeout(closeTimer);
            closeAllMenus();
            panel.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        };

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            const name = trigger.dataset.headerMenuTrigger;
            const panel = name ? document.querySelector(`[data-header-menu-panel="${name}"]`) : null;

            if (!(panel instanceof HTMLElement)) {
                return;
            }

            const willOpen = panel.classList.contains('hidden');
            closeAllMenus();

            if (willOpen) {
                panel.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        const shell = trigger.closest('.header-menu-shell');
        if (shell instanceof HTMLElement) {
            shell.addEventListener('mouseenter', openMenu);
            shell.addEventListener('mouseleave', () => {
                closeTimer = window.setTimeout(closeAllMenus, 180);
            });
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element) || !target.closest('.header-menu-shell')) {
            closeAllMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllMenus();
        }
    });
}

function initializeSidebarNavigation() {
    const layout = document.querySelector('[data-app-layout]');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggles = [...document.querySelectorAll('[data-sidebar-toggle]')];

    if (!(layout instanceof HTMLElement) || !(sidebar instanceof HTMLElement)) {
        return;
    }

    const desktopBreakpoint = 1280;
    const hiddenStorageKey = 'motox.sidebar.hidden';
    const legacyCollapsedStorageKey = 'motox.sidebar.collapsed';
    const mobileOpenClass = 'sidebar-mobile-open';
    const desktopHiddenClass = 'sidebar-desktop-hidden';
    const desktopVisibleLabel = 'Hide sidebar';
    const desktopHiddenLabel = 'Show sidebar';
    const mobileOpenLabel = 'Open sidebar';
    const mobileCloseLabel = 'Close sidebar';

    const isDesktop = () => window.innerWidth >= desktopBreakpoint;

    const setToggleLabels = (isHidden) => {
        const label = isHidden ? desktopHiddenLabel : desktopVisibleLabel;

        toggles.forEach((toggle) => {
            if (toggle instanceof HTMLButtonElement) {
                if (toggle.hasAttribute('data-sidebar-mobile-toggle')) {
                    return;
                }

                toggle.setAttribute('aria-label', label);
                toggle.setAttribute('title', label);
            }
        });
    };

    const setExpandedState = (expanded) => {
        toggles.forEach((toggle) => {
            if (toggle instanceof HTMLButtonElement) {
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                if (toggle.hasAttribute('data-sidebar-mobile-toggle')) {
                    const mobileLabel = expanded ? mobileCloseLabel : mobileOpenLabel;
                    toggle.setAttribute('aria-label', mobileLabel);
                    toggle.setAttribute('title', mobileLabel);
                }
            }
        });
    };

    const setDesktopHidden = (isHidden) => {
        layout.classList.toggle(desktopHiddenClass, isHidden);
        setToggleLabels(isHidden);
        setExpandedState(!isHidden);

        try {
            localStorage.setItem(hiddenStorageKey, isHidden ? '1' : '0');
            localStorage.removeItem(legacyCollapsedStorageKey);
        } catch (error) {
            console.warn('Unable to persist sidebar state', error);
        }
    };

    const setMobileOpen = (isOpen) => {
        sidebar.classList.toggle(mobileOpenClass, isOpen);
        sidebar.classList.toggle('hidden', !isOpen);
        setExpandedState(isOpen);

        if (overlay instanceof HTMLElement) {
            overlay.classList.toggle('hidden', !isOpen);
            overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        document.body.classList.toggle('overflow-hidden', isOpen);
    };

    const closeMobile = () => setMobileOpen(false);

    const resetMobileChrome = () => {
        if (overlay instanceof HTMLElement) {
            overlay.classList.add('hidden');
            overlay.setAttribute('aria-hidden', 'true');
        }

        document.body.classList.remove('overflow-hidden');
        setExpandedState(false);
    };

    const syncViewportState = () => {
        if (isDesktop()) {
            sidebar.classList.remove(mobileOpenClass, 'hidden');
            resetMobileChrome();

            try {
                const storedHiddenState = localStorage.getItem(hiddenStorageKey);
                const shouldHideDesktop = storedHiddenState === null
                    ? localStorage.getItem(legacyCollapsedStorageKey) === '1'
                    : storedHiddenState === '1';
                setDesktopHidden(shouldHideDesktop);
            } catch (error) {
                setDesktopHidden(false);
            }

            return;
        }

        layout.classList.remove(desktopHiddenClass);
        closeMobile();
    };

    syncViewportState();

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();

            if (isDesktop()) {
                const willHide = !layout.classList.contains(desktopHiddenClass);
                setDesktopHidden(willHide);
                return;
            }

            const willOpen = !sidebar.classList.contains(mobileOpenClass);
            setMobileOpen(willOpen);
        });
    });

    if (overlay instanceof HTMLElement) {
        overlay.addEventListener('click', () => {
            closeMobile();
        });
    }

    document.addEventListener('click', (event) => {
        if (isDesktop()) {
            return;
        }

        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        if (!target.closest('#sidebar') && !target.closest('[data-sidebar-toggle]')) {
            closeMobile();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobile();
        }
    });

    window.addEventListener('resize', () => {
        syncViewportState();
    });
}

function initializeNotificationActions() {
    const panel = document.querySelector('[data-header-menu-panel="notifications"]');
    const notificationTrigger = document.querySelector('[data-header-menu-trigger="notifications"]');
    const unreadDot = document.querySelector('[data-notification-dot]');
    const unreadCountBadge = document.querySelector('[data-notification-count-badge]');
    const unreadCountLabel = panel instanceof HTMLElement
        ? panel.querySelector('[data-notification-count-label]')
        : null;
    const markAsReadButton = document.querySelector('[data-mark-notifications-read]');
    const listRoot = panel instanceof HTMLElement
        ? panel.querySelector('[data-notification-list]')
        : null;

    if (!(panel instanceof HTMLElement) || !(unreadDot instanceof HTMLElement) || !(listRoot instanceof HTMLElement)) {
        return;
    }

    const notificationsUrl = panel.dataset.notificationsUrl;
    const readAllUrl = panel.dataset.notificationsReadUrl;
    const deleteTemplate = panel.dataset.notificationsDeleteTemplate || '';
    let lastUnreadCount = 0;
    const latestNotificationStorageKey = 'motox.notifications.latest-sounded-id';
    const notificationSignalsStorageKey = 'motox.notifications.signals';
    const notificationSoundUnlockStorageKey = 'motox.notifications.sound-unlocked';
    let lastNotificationId = null;
    let knownNotificationSignals = new Map();
    let audioContext = null;
    let hasFetchedNotifications = false;
    let pendingNotificationSounds = 0;

    if (!notificationsUrl) {
        return;
    }

    try {
        const storedNotificationId = Number(localStorage.getItem(latestNotificationStorageKey));
        lastNotificationId = Number.isFinite(storedNotificationId) && storedNotificationId > 0
            ? storedNotificationId
            : null;
    } catch (error) {
        lastNotificationId = null;
    }

    try {
        const storedSignals = JSON.parse(localStorage.getItem(notificationSignalsStorageKey) || '{}');
        if (storedSignals && typeof storedSignals === 'object' && !Array.isArray(storedSignals)) {
            knownNotificationSignals = new Map(Object.entries(storedSignals).map(([id, signal]) => [Number(id), String(signal)]));
        }
    } catch (error) {
        knownNotificationSignals = new Map();
    }

    const ensureAudioContext = () => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) {
                return null;
            }

            audioContext = audioContext || new AudioContext();
            if (audioContext.state === 'running') {
                try {
                    sessionStorage.setItem(notificationSoundUnlockStorageKey, '1');
                } catch (error) {
                    console.warn('Unable to persist notification sound state', error);
                }
            }

            return audioContext;
        } catch (error) {
            console.warn('Notification sound unavailable', error);
        }

        return null;
    };

    const playNotificationSound = (repeatCount = 1) => {
        try {
            const context = ensureAudioContext();
            if (!context) {
                return;
            }

            if (context.state === 'suspended') {
                pendingNotificationSounds = Math.min(4, pendingNotificationSounds + Math.max(1, repeatCount));
                context.resume().then(() => {
                    if (pendingNotificationSounds > 0) {
                        const queuedSounds = pendingNotificationSounds;
                        pendingNotificationSounds = 0;
                        playNotificationSound(queuedSounds);
                    }
                }).catch((error) => {
                    console.warn('Notification sound unavailable', error);
                });
                return;
            }

            pendingNotificationSounds = 0;
            try {
                sessionStorage.setItem(notificationSoundUnlockStorageKey, '1');
            } catch (error) {
                console.warn('Unable to persist notification sound state', error);
            }

            const playTone = (frequency, startOffset) => {
                const gain = context.createGain();
                const oscillator = context.createOscillator();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, context.currentTime + startOffset);
                gain.gain.setValueAtTime(0.0001, context.currentTime + startOffset);
                gain.gain.exponentialRampToValueAtTime(0.24, context.currentTime + startOffset + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + startOffset + 0.2);
                oscillator.connect(gain);
                gain.connect(context.destination);
                oscillator.start(context.currentTime + startOffset);
                oscillator.stop(context.currentTime + startOffset + 0.22);
            };

            const boundedRepeatCount = Math.min(5, Math.max(1, repeatCount));
            for (let index = 0; index < boundedRepeatCount; index += 1) {
                const offset = index * 0.46;
                playTone(880, offset);
                playTone(1175, offset + 0.18);
            }
        } catch (error) {
            console.warn('Notification sound unavailable', error);
        }
    };

    const unlockNotificationSound = () => {
        const context = ensureAudioContext();
        if (!context) {
            return;
        }

        if (context.state === 'suspended') {
            context.resume().then(() => {
                if (pendingNotificationSounds > 0) {
                    const queuedSounds = pendingNotificationSounds;
                    pendingNotificationSounds = 0;
                    playNotificationSound(queuedSounds);
                }
            }).catch((error) => {
                console.warn('Notification sound unavailable', error);
            });
            return;
        }

        if (pendingNotificationSounds > 0) {
            const queuedSounds = pendingNotificationSounds;
            pendingNotificationSounds = 0;
            playNotificationSound(queuedSounds);
        }
    };

    window.addEventListener('pointerdown', unlockNotificationSound);
    window.addEventListener('keydown', unlockNotificationSound);
    window.addEventListener('focus', unlockNotificationSound);

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const severityClass = (severity) => {
        switch (String(severity || '').toLowerCase()) {
            case 'danger':
                return 'text-rose-500';
            case 'warning':
                return 'text-amber-500';
            case 'success':
                return 'text-emerald-500';
            default:
                return 'text-sky-500';
        }
    };

    const renderItems = (items) => {
        if (!Array.isArray(items) || !items.length) {
            listRoot.innerHTML = '<p class="text-xs text-slate-500">No notifications right now.</p>';
            return;
        }

        listRoot.innerHTML = items.map((item) => `
            <div class="header-menu-item">
                <div class="mt-0.5 ${severityClass(item.severity)}">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-current"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="header-menu-label">${escapeHtml(item.title)}</p>
                    ${item.body ? `<p class="header-menu-text">${escapeHtml(item.body)}</p>` : ''}
                    <p class="mt-1 text-[11px] text-slate-400">${escapeHtml(item.created_human || '')}</p>
                </div>
                <button type="button" class="notification-delete-button" data-delete-notification="${escapeHtml(item.id)}" aria-label="Delete notification" title="Delete notification">
                    &times;
                </button>
            </div>
        `).join('');
    };

    const latestNotificationId = (items) => {
        if (!Array.isArray(items) || !items.length) {
            return null;
        }

        const ids = items.map((item) => Number(item?.id ?? 0)).filter((id) => Number.isFinite(id) && id > 0);

        return ids.length ? Math.max(...ids) : null;
    };

    const notificationIds = (items) => {
        if (!Array.isArray(items) || !items.length) {
            return [];
        }

        return items
            .map((item) => Number(item?.id ?? 0))
            .filter((id) => Number.isFinite(id) && id > 0);
    };

    const notificationSignals = (items) => {
        const signals = new Map();

        if (!Array.isArray(items) || !items.length) {
            return signals;
        }

        items.forEach((item) => {
            const id = Number(item?.id ?? 0);
            if (!Number.isFinite(id) || id <= 0) {
                return;
            }

            signals.set(id, String(item?.updated_at || item?.created_at || id));
        });

        return signals;
    };

    const persistNotificationSignals = (signals) => {
        try {
            localStorage.setItem(
                notificationSignalsStorageKey,
                JSON.stringify(Object.fromEntries(signals.entries())),
            );
        } catch (error) {
            console.warn('Unable to persist notification signals', error);
        }
    };

    const updateUnreadCountDisplay = (unreadCount) => {
        const normalizedCount = Math.max(0, Number(unreadCount || 0));
        const displayCount = normalizedCount > 99 ? '99+' : String(normalizedCount);
        const label = normalizedCount === 1 ? '1 unread' : `${displayCount} unread`;

        unreadDot.classList.toggle('hidden', normalizedCount <= 0);

        if (unreadCountBadge instanceof HTMLElement) {
            unreadCountBadge.textContent = displayCount;
            unreadCountBadge.classList.toggle('hidden', normalizedCount <= 0);
        }

        if (unreadCountLabel instanceof HTMLElement) {
            unreadCountLabel.textContent = label;
        }
    };

    const applyPayload = (payload) => {
        const unreadCount = Number(payload?.unread_count ?? 0);
        const items = payload?.items ?? [];
        const ids = notificationIds(items);
        const latestId = latestNotificationId(items);
        const currentSignals = notificationSignals(items);
        let newNotificationCount = 0;

        currentSignals.forEach((signal, id) => {
            const isNewId = lastNotificationId !== null && id > lastNotificationId;
            const previousSignal = knownNotificationSignals.get(id);
            const wasUpdated = previousSignal !== undefined && previousSignal !== signal;

            if (isNewId || wasUpdated) {
                newNotificationCount += 1;
            }
        });

        if (newNotificationCount === 0 && hasFetchedNotifications && lastNotificationId === null && unreadCount > lastUnreadCount) {
            newNotificationCount = unreadCount - lastUnreadCount;
        }

        if (newNotificationCount === 0 && !hasFetchedNotifications && unreadCount > 0 && Array.isArray(items) && items.length > 0) {
            newNotificationCount = Math.min(3, unreadCount);
        }

        if (unreadCount > 0 && newNotificationCount > 0) {
            playNotificationSound(newNotificationCount);
        }

        lastUnreadCount = unreadCount;
        if (latestId !== null && (lastNotificationId === null || latestId > lastNotificationId)) {
            lastNotificationId = latestId;
            try {
                localStorage.setItem(latestNotificationStorageKey, String(latestId));
            } catch (error) {
                console.warn('Unable to persist latest notification id', error);
            }
        }
        knownNotificationSignals = currentSignals;
        persistNotificationSignals(currentSignals);
        updateUnreadCountDisplay(unreadCount);
        renderItems(items);
        hasFetchedNotifications = true;
    };

    const fetchNotifications = async () => {
        try {
            const response = await fetch(notificationsUrl, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            applyPayload(payload);
            return payload;
        } catch (error) {
            console.warn('Notification polling failed', error);
        }

        return null;
    };

    if (notificationTrigger instanceof HTMLElement) {
        notificationTrigger.addEventListener('click', () => {
            window.setTimeout(() => {
                if (!panel.classList.contains('hidden')) {
                    fetchNotifications();
                }
            }, 0);
        });
    }

    if (markAsReadButton instanceof HTMLButtonElement) {
        markAsReadButton.addEventListener('click', async () => {
            if (!readAllUrl) {
                return;
            }

            try {
                const response = await fetch(readAllUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                });

                if (!response.ok) {
                    return;
                }

                listRoot.innerHTML = '<p class="text-xs text-slate-500">No notifications right now.</p>';
                lastUnreadCount = 0;
                updateUnreadCountDisplay(0);
                fetchNotifications();
            } catch (error) {
                console.warn('Failed to mark notifications as read', error);
            }
        });
    }

    listRoot.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const button = target.closest('[data-delete-notification]');
        if (!(button instanceof HTMLElement) || !deleteTemplate) {
            return;
        }

        const id = button.dataset.deleteNotification;
        if (!id) {
            return;
        }

        try {
            const response = await fetch(deleteTemplate.replace('__ID__', encodeURIComponent(id)), {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (!response.ok) {
                return;
            }

            fetchNotifications();
        } catch (error) {
            console.warn('Failed to delete notification', error);
        }
    });

    window.MotoXRefreshNotifications = fetchNotifications;
    fetchNotifications();
    window.setInterval(fetchNotifications, 2000);
}

function initializeCustomersFilters() {
    const searchInput = document.getElementById('customer-search-input');
    const filterDateBtn = document.getElementById('filter-date-btn');
    const filterProgressBtn = document.getElementById('filter-progress-btn');
    const dateDropdown = document.getElementById('date-dropdown');
    const progressDropdown = document.getElementById('progress-dropdown');
    const customerRows = [...document.querySelectorAll('.customer-row')];

    if (!(searchInput instanceof HTMLInputElement)) {
        return;
    }

    let searchTerm = '';
    let dateFilter = 'all';
    let progressFilter = 'all';

    const setButtonLabel = (button, label) => {
        const span = button?.querySelector('span');
        if (span) {
            span.textContent = label;
        }
    };

    const applyFilters = () => {
        const now = new Date();

        customerRows.forEach((row) => {
            const name = row.dataset.name?.toLowerCase() || '';
            const email = row.dataset.email?.toLowerCase() || '';
            const phone = row.dataset.phone?.toLowerCase() || '';
            const notes = row.dataset.notes?.toLowerCase() || '';
            const createdAt = row.dataset.createdAt ? new Date(row.dataset.createdAt) : null;
            const activeJobs = Number.parseInt(row.dataset.activeJobs || '0', 10);

            const matchesSearch = !searchTerm
                || name.includes(searchTerm)
                || email.includes(searchTerm)
                || phone.includes(searchTerm)
                || notes.includes(searchTerm);

            let matchesDate = true;
            if (dateFilter !== 'all' && createdAt instanceof Date && !Number.isNaN(createdAt.valueOf())) {
                let from = null;
                switch (dateFilter) {
                    case 'today':
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        break;
                    case 'week':
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                        break;
                    case 'month':
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                        break;
                    case 'year':
                        from = new Date(now.getFullYear(), 0, 1);
                        break;
                    default:
                        from = null;
                }

                if (from) {
                    matchesDate = createdAt >= from;
                }
            }

            let matchesProgress = true;
            if (progressFilter === 'active') {
                matchesProgress = activeJobs > 0;
            } else if (progressFilter === 'no-active') {
                matchesProgress = activeJobs === 0;
            }

            row.style.display = matchesSearch && matchesDate && matchesProgress ? '' : 'none';
        });

        updateVisibleCount();
        syncEmptyState(customerRows, 'customer-empty-filter-row', 6, 'No customers match the current filters.');
    };

    searchInput.addEventListener('input', (event) => {
        searchTerm = String(event.target?.value ?? '').toLowerCase().trim();
        applyFilters();
    });

    if (filterDateBtn instanceof HTMLElement && dateDropdown instanceof HTMLElement) {
        filterDateBtn.addEventListener('click', () => {
            dateDropdown.classList.toggle('hidden');
            if (progressDropdown instanceof HTMLElement) {
                progressDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!filterDateBtn.contains(e.target) && !dateDropdown.contains(e.target)) {
                dateDropdown.classList.add('hidden');
            }
        });

        dateDropdown.querySelectorAll('[data-date-filter]').forEach((option) => {
            option.addEventListener('click', () => {
                dateFilter = String(option.dataset.dateFilter || 'all');
                setButtonLabel(filterDateBtn, option.textContent?.trim() || 'Filter by Date');
                dateDropdown.classList.add('hidden');
                applyFilters();
            });
        });
    }

    if (filterProgressBtn instanceof HTMLElement && progressDropdown instanceof HTMLElement) {
        filterProgressBtn.addEventListener('click', () => {
            progressDropdown.classList.toggle('hidden');
            if (dateDropdown instanceof HTMLElement) {
                dateDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!filterProgressBtn.contains(e.target) && !progressDropdown.contains(e.target)) {
                progressDropdown.classList.add('hidden');
            }
        });

        progressDropdown.querySelectorAll('[data-progress-filter]').forEach((option) => {
            option.addEventListener('click', () => {
                progressFilter = String(option.dataset.progressFilter || 'all');
                setButtonLabel(filterProgressBtn, option.textContent?.trim() || 'Filter by Progress');
                progressDropdown.classList.add('hidden');
                applyFilters();
            });
        });
    }

    function updateVisibleCount() {
        const visibleCount = customerRows.filter((row) => row.style.display !== 'none').length;
        const countElement = document.getElementById('visible-customers-count');
        if (countElement) {
            countElement.textContent = `${visibleCount} profiles`;
        }
    }

    applyFilters();
}

function initializeJobOrdersFilters() {
    const searchInput = document.getElementById('joborder-search-input');
    const filterDateBtn = document.getElementById('filter-date-btn');
    const filterProgressBtn = document.getElementById('filter-progress-btn');
    const dateDropdown = document.getElementById('date-dropdown');
    const progressDropdown = document.getElementById('progress-dropdown');
    const jobOrderRows = [...document.querySelectorAll('.job-order-row')];

    if (!(searchInput instanceof HTMLInputElement)) {
        return;
    }

    let searchTerm = '';
    let dateFilter = 'all';
    let progressFilter = 'all';

    const setButtonLabel = (button, label) => {
        const span = button?.querySelector('span');
        if (span) {
            span.textContent = label;
        }
    };

    const applyFilters = () => {
        const now = new Date();

        jobOrderRows.forEach((row) => {
            const orderNumber = row.dataset.orderNumber?.toLowerCase() || '';
            const customer = row.dataset.customer?.toLowerCase() || '';
            const vehicle = row.dataset.vehicle?.toLowerCase() || '';
            const status = row.dataset.status?.toLowerCase() || '';
            const createdAt = row.dataset.createdAt ? new Date(row.dataset.createdAt) : null;

            const matchesSearch = !searchTerm
                || orderNumber.includes(searchTerm)
                || customer.includes(searchTerm)
                || vehicle.includes(searchTerm)
                || status.includes(searchTerm);

            let matchesDate = true;
            if (dateFilter !== 'all' && createdAt instanceof Date && !Number.isNaN(createdAt.valueOf())) {
                let from = null;
                switch (dateFilter) {
                    case 'today':
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        break;
                    case 'week':
                        from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                        break;
                    case 'month':
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                        break;
                    case 'year':
                        from = new Date(now.getFullYear(), 0, 1);
                        break;
                    default:
                        from = null;
                }

                if (from) {
                    matchesDate = createdAt >= from;
                }
            }

            let matchesProgress = true;
            if (progressFilter !== 'all') {
                matchesProgress = status === progressFilter;
            }

            row.style.display = matchesSearch && matchesDate && matchesProgress ? '' : 'none';
        });

        syncEmptyState(jobOrderRows, 'job-order-empty-filter-row', 7, 'No job orders match the current filters.');
    };

    searchInput.addEventListener('input', (event) => {
        searchTerm = String(event.target?.value ?? '').toLowerCase().trim();
        applyFilters();
    });

    if (filterDateBtn instanceof HTMLElement && dateDropdown instanceof HTMLElement) {
        filterDateBtn.addEventListener('click', () => {
            dateDropdown.classList.toggle('hidden');
            if (progressDropdown instanceof HTMLElement) {
                progressDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!filterDateBtn.contains(e.target) && !dateDropdown.contains(e.target)) {
                dateDropdown.classList.add('hidden');
            }
        });

        dateDropdown.querySelectorAll('[data-date-filter]').forEach((option) => {
            option.addEventListener('click', () => {
                dateFilter = String(option.dataset.dateFilter || 'all');
                setButtonLabel(filterDateBtn, option.textContent?.trim() || 'Filter by Date');
                dateDropdown.classList.add('hidden');
                applyFilters();
            });
        });
    }

    if (filterProgressBtn instanceof HTMLElement && progressDropdown instanceof HTMLElement) {
        filterProgressBtn.addEventListener('click', () => {
            progressDropdown.classList.toggle('hidden');
            if (dateDropdown instanceof HTMLElement) {
                dateDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!filterProgressBtn.contains(e.target) && !progressDropdown.contains(e.target)) {
                progressDropdown.classList.add('hidden');
            }
        });

        progressDropdown.querySelectorAll('[data-progress-filter]').forEach((option) => {
            option.addEventListener('click', () => {
                progressFilter = String(option.dataset.progressFilter || 'all');
                setButtonLabel(filterProgressBtn, option.textContent?.trim() || 'Filter by Progress');
                progressDropdown.classList.add('hidden');
                applyFilters();
            });
        });
    }

    applyFilters();
}

function syncEmptyState(rows, rowId, colspan, message) {
    if (!Array.isArray(rows) || rows.length === 0) {
        return;
    }

    const tbody = rows[0].closest('tbody');
    if (!(tbody instanceof HTMLTableSectionElement)) {
        return;
    }

    let emptyRow = document.getElementById(rowId);
    if (!(emptyRow instanceof HTMLTableRowElement)) {
        tbody.insertAdjacentHTML('beforeend', `
            <tr id="${rowId}" class="hidden">
                <td colspan="${colspan}" class="py-10 text-center text-sm text-slate-500">${message}</td>
            </tr>
        `);
        emptyRow = document.getElementById(rowId);
    }

    if (emptyRow instanceof HTMLTableRowElement) {
        const visibleRows = rows.filter((row) => row.style.display !== 'none').length;
        emptyRow.classList.toggle('hidden', visibleRows > 0);
    }
}

function initializeSidebarDateFilters() {
    const triggers = [...document.querySelectorAll('[data-date-filter-trigger]')];

    if (!triggers.length) {
        return;
    }

    const closeMenus = () => {
        document.querySelectorAll('[data-date-filter-menu]').forEach((menu) => {
            if (menu instanceof HTMLElement) {
                menu.classList.add('hidden');
            }
        });
    };

    triggers.forEach((trigger) => {
        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        const page = trigger.dataset.dateFilterTrigger;
        const menu = document.querySelector(`[data-date-filter-menu="${page}"]`);
        const label = trigger.querySelector('span');

        if (!(menu instanceof HTMLElement) || !page) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const wasHidden = menu.classList.contains('hidden');
            closeMenus();
            menu.classList.toggle('hidden', !wasHidden);
        });

        menu.querySelectorAll('[data-date-filter]').forEach((option) => {
            option.addEventListener('click', () => {
                const filter = option.dataset.dateFilter || 'all';
                if (label instanceof HTMLElement) {
                    label.textContent = option.textContent?.trim() || 'Filter by Date';
                }
                menu.classList.add('hidden');
                window.dispatchEvent(new CustomEvent('motox:date-filter-change', {
                    detail: { page, filter },
                }));
            });
        });
    });

    document.addEventListener('click', closeMenus);
}

function itemMatchesDate(filter, rawDate) {
    const normalized = normalizePeriodFilter(filter);
    if (normalized === 'all') {
        return true;
    }

    if (!rawDate) {
        return false;
    }

    const itemDate = new Date(rawDate);
    if (Number.isNaN(itemDate.valueOf())) {
        return false;
    }

    const now = new Date();
    let from = null;

    switch (normalized) {
        case 'daily':
            from = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            break;
        case 'weekly':
            from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
            break;
        case 'monthly':
            from = new Date(now.getFullYear(), now.getMonth(), 1);
            break;
        case 'yearly':
            from = new Date(now.getFullYear(), 0, 1);
            break;
        default:
            from = null;
    }

    return from ? itemDate >= from : true;
}

function normalizePeriodFilter(value) {
    const normalized = String(value ?? '').trim().toLowerCase();
    switch (normalized) {
        case 'general':
        case 'all':
        case 'all-time':
        case 'all_time':
            return 'all';
        case 'today':
        case 'day':
        case 'daily':
            return 'daily';
        case 'week':
        case 'weekly':
            return 'weekly';
        case 'month':
        case 'monthly':
            return 'monthly';
        case 'year':
        case 'yearly':
            return 'yearly';
        default:
            return 'all';
    }
}

function initializeDashboardSearch() {
    const searchInput = document.getElementById('dashboard-search-input');
    const dashboardItems = [...document.querySelectorAll('[data-dashboard-item]')];

    if (!(searchInput instanceof HTMLInputElement) || !dashboardItems.length) {
        return;
    }

    let searchTerm = '';
    let dateFilter = 'all';

    const applyFilters = () => {
        dashboardItems.forEach((row) => {
            const haystack = [
                row.dataset.partName,
                row.dataset.partSku,
                row.dataset.partCategory,
                row.dataset.movementName,
                row.dataset.movementType,
                row.dataset.movementReason,
            ].join(' ').toLowerCase();

            const matchesSearch = !searchTerm || haystack.includes(searchTerm);
            const matchesDate = itemMatchesDate(dateFilter, row.dataset.itemDate);
            row.style.display = matchesSearch && matchesDate ? '' : 'none';
        });
    };

    searchInput.addEventListener('input', (event) => {
        searchTerm = String(event.target?.value ?? '').toLowerCase().trim();
        applyFilters();
    });

    window.addEventListener('motox:date-filter-change', (event) => {
        if (event.detail?.page !== 'dashboard') {
            return;
        }

        dateFilter = String(event.detail?.filter || 'all');
        applyFilters();
    });

    applyFilters();
}

function initializeLiveTables() {
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const badgeClass = (tone) => {
        switch (String(tone || '').toLowerCase()) {
            case 'success':
                return 'bg-emerald-50 text-emerald-700 border-emerald-100';
            case 'danger':
                return 'bg-rose-50 text-rose-700 border-rose-100';
            case 'warning':
                return 'bg-amber-50 text-amber-700 border-amber-100';
            default:
                return 'bg-slate-50 text-slate-700 border-slate-100';
        }
    };

    const renderBadge = (label, tone) => `
        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${badgeClass(tone)}">
            ${escapeHtml(label)}
        </span>
    `;

    const tableConfigs = {
        billing: {
            tbody: document.querySelector('[data-billing-rows]'),
            input: document.getElementById('billing-search-input'),
            exportUrl: document.querySelector('[data-billing-export-url]')?.dataset.billingExportUrl || '',
            emptyColspan: 8,
            emptyText: 'No billable job orders match the current filters.',
            filename: 'motox-billing.csv',
            title: 'MotoX Billing',
            dateFilter: 'all',
            rowSelector: '[data-billing-row]',
            renderRows(rows) {
                return rows.map((invoice) => {
                    const search = [
                        invoice.invoice_number,
                        invoice.order_number,
                        invoice.customer,
                        invoice.vehicle,
                        invoice.status,
                    ].join(' ').toLowerCase();

                    return `
                        <tr
                            data-billing-row
                            data-item-date="${escapeHtml(invoice.updated_at || '')}"
                            data-search="${escapeHtml(search)}"
                            data-receipt-key="${escapeHtml(invoice.invoice_number)}"
                            data-receipt-invoice="${escapeHtml(invoice.invoice_number)}"
                            data-receipt-order="${escapeHtml(invoice.order_number)}"
                            data-receipt-customer="${escapeHtml(invoice.customer)}"
                            data-receipt-phone="${escapeHtml(invoice.customer_phone || '')}"
                            data-receipt-email="${escapeHtml(invoice.customer_email || '')}"
                            data-receipt-photo="${escapeHtml(invoice.customer_photo_url || '')}"
                            data-receipt-vehicle="${escapeHtml(invoice.vehicle)}"
                            data-receipt-status="${escapeHtml(invoice.status)}"
                            data-receipt-amount="${escapeHtml(invoice.amount_display)}"
                            data-receipt-amount-value="${escapeHtml(invoice.amount)}"
                            data-receipt-updated="${escapeHtml(invoice.receipt_updated_display || invoice.updated_display)}"
                            data-receipt-shop="${escapeHtml(invoice.shop_name || 'MotoX')}"
                        >
                            <td class="font-semibold text-slate-900">${escapeHtml(invoice.invoice_number)}</td>
                            <td>${escapeHtml(invoice.order_number)}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    ${invoice.customer_photo_url
                                        ? `<img src="${escapeHtml(invoice.customer_photo_url)}" alt="${escapeHtml(invoice.customer)} profile" class="h-10 w-10 rounded-full object-cover">`
                                        : `<span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">${escapeHtml(String(invoice.customer || 'CU').split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('') || 'CU')}</span>`}
                                    <span>${escapeHtml(invoice.customer)}</span>
                                </div>
                            </td>
                            <td>${escapeHtml(invoice.vehicle)}</td>
                            <td>${renderBadge(invoice.status, invoice.tone)}</td>
                            <td class="font-semibold text-slate-900">${escapeHtml(invoice.amount_display)}</td>
                            <td>${escapeHtml(invoice.updated_display)}</td>
                            <td data-print-skip>
                                <div class="billing-action-buttons">
                                    <button type="button" class="receipt-action-button receipt-action-button-primary" data-download-receipt aria-label="Download receipt for ${escapeHtml(invoice.customer)}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 3v12" />
                                            <path d="m7 10 5 5 5-5" />
                                            <path d="M5 21h14" />
                                        </svg>
                                        <span>Receipt</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            },
        },
        reports: {
            tbody: document.querySelector('[data-reports-rows]'),
            input: document.getElementById('reports-search-input'),
            exportUrl: document.querySelector('[data-reports-export-url]')?.dataset.reportsExportUrl || '',
            emptyColspan: 4,
            emptyText: 'No customer billing data matches the current filters.',
            filename: 'motox-reports.csv',
            title: 'MotoX Reports',
            dateFilter: 'all',
            rowSelector: '[data-reports-row]',
            renderRows(rows) {
                return rows.map((row) => {
                    const search = [row.name, row.jobs, row.billed].join(' ').toLowerCase();

                    return `
                        <tr data-reports-row data-item-date="${escapeHtml(row.latest_job_at || '')}" data-search="${escapeHtml(search)}">
                            <td class="font-semibold text-slate-900">
                                <div class="flex items-center gap-3">
                                    ${row.profile_photo_url
                                        ? `<img src="${escapeHtml(row.profile_photo_url)}" alt="${escapeHtml(row.name)} profile" class="h-10 w-10 rounded-full object-cover">`
                                        : `<span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">${escapeHtml(String(row.name || 'CU').split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('') || 'CU')}</span>`}
                                    <span>${escapeHtml(row.name)}</span>
                                </div>
                            </td>
                            <td>${escapeHtml(row.jobs)}</td>
                            <td class="font-semibold text-slate-900">${escapeHtml(row.billed)}</td>
                            <td>${escapeHtml(row.latest_display || '-')}</td>
                        </tr>
                    `;
                }).join('');
            },
        },
    };

    const states = {
        billing: { search: '', date: 'all' },
        reports: { search: '', date: 'all' },
    };

    const getRows = (page) => {
        const config = tableConfigs[page];
        return config?.tbody instanceof HTMLElement ? [...config.tbody.querySelectorAll(config.rowSelector)] : [];
    };

    const ensureEmptyRow = (page) => {
        const config = tableConfigs[page];
        if (!(config?.tbody instanceof HTMLElement)) {
            return;
        }

        let emptyRow = config.tbody.querySelector('[data-empty-row]');
        if (!(emptyRow instanceof HTMLTableRowElement)) {
            config.tbody.insertAdjacentHTML('beforeend', `
                <tr data-empty-row class="hidden">
                    <td colspan="${config.emptyColspan}" class="py-10 text-center text-sm text-slate-500">${escapeHtml(config.emptyText)}</td>
                </tr>
            `);
            emptyRow = config.tbody.querySelector('[data-empty-row]');
        }

        const visibleCount = getRows(page).filter((row) => row.style.display !== 'none').length;
        emptyRow.classList.toggle('hidden', visibleCount > 0);
    };

    const applyFilters = (page) => {
        const state = states[page];
        const rows = getRows(page);

        rows.forEach((row) => {
            const matchesSearch = !state.search || String(row.dataset.search || '').includes(state.search);
            const matchesDate = itemMatchesDate(state.date, row.dataset.itemDate);
            row.style.display = matchesSearch && matchesDate ? '' : 'none';
        });

        ensureEmptyRow(page);
    };

    Object.entries(tableConfigs).forEach(([page, config]) => {
        if (!(config.tbody instanceof HTMLElement)) {
            return;
        }

        if (config.input instanceof HTMLInputElement) {
            config.input.addEventListener('input', (event) => {
                states[page].search = String(event.target?.value ?? '').toLowerCase().trim();
                applyFilters(page);
            });
        }

        const exportButton = document.querySelector(`[data-export-csv="${page}"]`);
        if (exportButton instanceof HTMLButtonElement) {
            exportButton.addEventListener('click', () => exportServerCsv(config));
        }

        const printButton = document.querySelector(`[data-print-table="${page}"]`);
        if (printButton instanceof HTMLButtonElement) {
            printButton.addEventListener('click', () => printVisibleTable(config));
        }

        if (page === 'billing') {
            config.tbody?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const button = target.closest('[data-download-receipt]');
                if (!(button instanceof HTMLButtonElement)) {
                    return;
                }

                event.preventDefault();
                downloadReceiptPng(button);
            });

        }

        applyFilters(page);
    });

    window.addEventListener('motox:date-filter-change', (event) => {
        const page = event.detail?.page;
        if (!states[page]) {
            return;
        }

        states[page].date = String(event.detail?.filter || 'all');
        if (tableConfigs[page]) {
            tableConfigs[page].dateFilter = states[page].date;
        }
        applyFilters(page);
    });

    window.MotoXLiveTables = {
        replaceRows(page, rows) {
            const config = tableConfigs[page];
            if (!(config?.tbody instanceof HTMLElement) || !Array.isArray(rows)) {
                return;
            }

            config.tbody.innerHTML = rows.length
                ? config.renderRows(rows)
                : `<tr data-empty-row><td colspan="${config.emptyColspan}" class="py-10 text-center text-sm text-slate-500">${escapeHtml(config.emptyText)}</td></tr>`;

            applyFilters(page);
        },
    };
}

function visibleTableData(config) {
    const table = config.tbody?.closest('table');
    if (!(table instanceof HTMLTableElement)) {
        return { headers: [], rows: [] };
    }

    const headers = [...table.querySelectorAll('thead th')]
        .filter((cell) => !cell.matches('[data-print-skip]'))
        .map((cell) => cell.textContent.trim());
    const rows = [...config.tbody.querySelectorAll('tr')]
        .filter((row) => !row.matches('[data-empty-row]') && row.style.display !== 'none')
        .map((row) => [...row.children]
            .filter((cell) => !cell.matches('[data-print-skip]'))
            .map((cell) => cell.textContent.trim().replace(/\s+/g, ' ')));

    return { headers, rows };
}

function printBillingReceipt(button) {
    printReceiptPng(button);
}

function exportVisibleTable(config) {
    const { headers, rows } = visibleTableData(config);
    const csvEscape = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`;
    const csv = [headers, ...rows].map((row) => row.map(csvEscape).join(',')).join('\r\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const range = String(config.dateFilter || 'all').replace(/[^a-z0-9-]/gi, '-');
    link.href = url;
    link.download = config.filename.replace('.csv', `-${range}.csv`);
    link.click();
    URL.revokeObjectURL(url);
}

function exportServerCsv(config) {
    if (config.exportUrl) {
        const url = new URL(config.exportUrl, window.location.origin);
        url.searchParams.set('period', normalizePeriodFilter(config.dateFilter || 'all'));
        window.location.href = `${url.pathname}${url.search}`;
        return;
    }

    exportVisibleTable(config);
}

function printVisibleTable(config) {
    const { headers, rows } = visibleTableData(config);
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    const printWindow = window.open('', '_blank', 'width=960,height=720');

    if (!printWindow) {
        window.print();
        return;
    }

    printWindow.document.write(`
        <!doctype html>
        <html>
            <head>
                <title>${escapeHtml(config.title)}</title>
                <style>
                    body { font-family: Arial, sans-serif; color: #111827; padding: 24px; }
                    h1 { font-size: 22px; margin: 0 0 16px; }
                    table { width: 100%; border-collapse: collapse; font-size: 12px; }
                    th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                    th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: .08em; }
                </style>
            </head>
            <body>
                <h1>${escapeHtml(config.title)}</h1>
                <table>
                    <thead><tr>${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}</tr></thead>
                    <tbody>${rows.map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`).join('')}</tbody>
                </table>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function initializeRegistrationPopup() {
    const successNode = document.querySelector('[data-registration-success]');
    if (!(successNode instanceof HTMLElement)) {
        return;
    }

    const message = successNode.dataset.registrationSuccess;
    if (!message) {
        return;
    }

    const normalized = message.toLowerCase();
    if (!normalized.includes('successfully registered')) {
        return;
    }

    window.setTimeout(() => {
        window.alert(message);
    }, 80);
}

function initializeProfileInitials() {
    const input = document.querySelector('[data-profile-name-input]');
    const initialsNode = document.querySelector('[data-profile-avatar-initials]');

    if (!(input instanceof HTMLInputElement) || !(initialsNode instanceof HTMLElement)) {
        return;
    }

    const updateInitials = () => {
        const initials = input.value
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('');

        initialsNode.textContent = initials || 'MX';
    };

    input.addEventListener('input', updateInitials);
    updateInitials();
}

function initializeImageUploadPreviews() {
    const inputs = [...document.querySelectorAll('[data-image-preview-input]')];

    if (!inputs.length) {
        return;
    }

    inputs.forEach((input) => {
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const key = input.dataset.imagePreviewInput;
        if (!key) {
            return;
        }

        const previewImage = document.querySelector(`[data-image-preview="${key}"]`);
        const previewWrapper = document.querySelector(`[data-image-preview-wrapper="${key}"]`);
        const previewPlaceholder = document.querySelector(`[data-image-preview-placeholder="${key}"]`);
        const profileFallback = key === 'profile-avatar'
            ? document.querySelector('[data-profile-avatar-initials]')
            : null;

        if (!(previewImage instanceof HTMLImageElement)) {
            return;
        }

        const hidePreview = () => {
            const existingObjectUrl = previewImage.dataset.objectUrl;
            if (existingObjectUrl) {
                URL.revokeObjectURL(existingObjectUrl);
                delete previewImage.dataset.objectUrl;
            }

            previewImage.removeAttribute('src');
            previewImage.classList.add('hidden');
            if (previewWrapper instanceof HTMLElement) {
                previewWrapper.classList.add('hidden');
            }
            if (previewPlaceholder instanceof HTMLElement) {
                previewPlaceholder.classList.remove('hidden');
            }
            if (profileFallback instanceof HTMLElement) {
                profileFallback.classList.remove('hidden');
            }
        };

        const showPreview = (file) => {
            const existingObjectUrl = previewImage.dataset.objectUrl;
            if (existingObjectUrl) {
                URL.revokeObjectURL(existingObjectUrl);
            }

            const objectUrl = URL.createObjectURL(file);
            previewImage.src = objectUrl;
            previewImage.dataset.objectUrl = objectUrl;
            previewImage.classList.remove('hidden');

            if (previewWrapper instanceof HTMLElement) {
                previewWrapper.classList.remove('hidden');
            }
            if (previewPlaceholder instanceof HTMLElement) {
                previewPlaceholder.classList.add('hidden');
            }
            if (profileFallback instanceof HTMLElement) {
                profileFallback.classList.add('hidden');
            }
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) {
                hidePreview();
                return;
            }

            showPreview(file);
        });
    });
}

function initializeLogsFilters() {
    const bind = () => {
        const form = document.querySelector('[data-logs-filter-form]');
        const searchInput = document.querySelector('[data-logs-live-search]');
        const filterSelect = document.querySelector('[data-logs-filter-select]');
        const dateTrigger = document.querySelector('[data-logs-date-trigger]');
        const dateMenu = document.querySelector('[data-logs-date-menu]');
        let pendingRequest = null;

        const fetchLogs = async (url, focusSearch = false) => {
            const main = document.querySelector('main');
            if (!(main instanceof HTMLElement)) {
                window.location.href = url;
                return;
            }

            if (pendingRequest instanceof AbortController) {
                pendingRequest.abort();
            }
            pendingRequest = new AbortController();

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: pendingRequest.signal,
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextMain = doc.querySelector('main');
                if (!(nextMain instanceof HTMLElement)) {
                    window.location.href = url;
                    return;
                }

                main.innerHTML = nextMain.innerHTML;
                window.history.replaceState(null, '', url);
                initializeModalControls();
                bind();

                if (focusSearch) {
                    const nextSearchInput = document.querySelector('[data-logs-live-search]');
                    if (nextSearchInput instanceof HTMLInputElement) {
                        nextSearchInput.focus();
                        nextSearchInput.setSelectionRange(nextSearchInput.value.length, nextSearchInput.value.length);
                    }
                }
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.warn('Logs filtering failed', error);
                }
            }
        };

        if (form instanceof HTMLFormElement && searchInput instanceof HTMLInputElement) {
        let searchTimer = null;
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            fetchLogs(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`, true);
        });

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => {
                fetchLogs(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`, true);
            }, 650);
        });
        }

        if (form instanceof HTMLFormElement && filterSelect instanceof HTMLSelectElement) {
            filterSelect.addEventListener('change', () => {
                fetchLogs(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`, false);
            });
        }

        if (dateTrigger instanceof HTMLButtonElement && dateMenu instanceof HTMLElement) {
        dateTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = dateMenu.classList.contains('hidden');
            dateMenu.classList.toggle('hidden', !willOpen);
            dateTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        if (!window.MotoXLogsDateOutsideClickBound) {
            window.MotoXLogsDateOutsideClickBound = true;
            document.addEventListener('click', (event) => {
                document.querySelectorAll('[data-logs-date-menu]').forEach((menu) => {
                    const trigger = document.querySelector('[data-logs-date-trigger]');
                    if (!(menu instanceof HTMLElement) || !(trigger instanceof HTMLElement)) {
                        return;
                    }

                    if (menu.contains(event.target) || trigger.contains(event.target)) {
                        return;
                    }

                    menu.classList.add('hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                });
            });
        }

            dateMenu.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const link = target.closest('a');
                if (!(link instanceof HTMLAnchorElement)) {
                    return;
                }

                event.preventDefault();
                fetchLogs(link.href, false);
            });
        }
    };

    bind();
}

function initializeJobOrderCustomerPhotos() {
    const selects = [...document.querySelectorAll('[data-customer-photo-select]')];

    selects.forEach((select) => {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        const key = select.dataset.customerPhotoSelect;
        if (!key) {
            return;
        }

        const previewImage = document.querySelector(`[data-image-preview="${key}"]`);
        const placeholder = document.querySelector(`[data-image-preview-placeholder="${key}"]`);
        const uploadCard = document.querySelector(`[data-walk-in-upload="${key}"]`);
        const fileInput = document.querySelector(`[data-image-preview-input="${key}"]`);

        if (!(previewImage instanceof HTMLImageElement)) {
            return;
        }

        const sync = () => {
            const option = select.selectedOptions[0];
            const photoUrl = option?.dataset.photoUrl || '';
            const isWalkIn = !select.value;

            if (uploadCard instanceof HTMLElement) {
                uploadCard.classList.toggle('joborder-upload-existing-customer', !isWalkIn);
            }

            if (fileInput instanceof HTMLInputElement) {
                fileInput.disabled = !isWalkIn;
            }

            if (photoUrl) {
                previewImage.src = photoUrl;
                previewImage.classList.remove('hidden');
                if (placeholder instanceof HTMLElement) {
                    placeholder.classList.add('hidden');
                }
                return;
            }

            if (!isWalkIn) {
                previewImage.removeAttribute('src');
                previewImage.classList.add('hidden');
                if (placeholder instanceof HTMLElement) {
                    placeholder.classList.remove('hidden');
                }
            }
        };

        select.addEventListener('change', sync);
        sync();
    });
}
