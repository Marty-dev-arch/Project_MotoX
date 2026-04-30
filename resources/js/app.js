document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const modeButtons = [...document.querySelectorAll('[data-mode]')];

    // Initialize customers filter functionality
    initializeCustomersFilters();
    
    // Initialize job orders filter functionality
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

    initializeModalControls();
    initializePasswordToggles();
    initializeDashboardPolling();
    initializeBillingPolling();
    initializeCustomersPolling();
    initializeJobOrdersPolling();
    initializeReportsRevenueChart();
    initializeLandingMetricsPolling();
    initializeHeaderMenus();
    initializeNotificationActions();
    initializeSidebarNavigation();
    initializeLandingBackgroundMotion();
    initializeLandingScrollReveal();
    initializeLandingSectionTracking();
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
            minimum: trigger.dataset.editPartMinimum ?? '0',
            price: trigger.dataset.editPartPrice ?? '0.00',
            active: trigger.dataset.editPartActive ?? '1',
        };

        Object.entries(fields).forEach(([key, value]) => {
            const input = editForm.querySelector(`[data-edit-field="${key}"]`);
            if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement) {
                input.value = value;
            }
        });
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
    };

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

function initializePasswordToggles() {
    const toggleButtons = [...document.querySelectorAll('[data-password-toggle]')];

    if (!toggleButtons.length) {
        return;
    }

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
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

            button.setAttribute('aria-label', nextVisibleState ? 'Hide password' : 'Show password');

            const showIcon = button.querySelector('[data-password-icon="show"]');
            const hideIcon = button.querySelector('[data-password-icon="hide"]');

            if (showIcon instanceof HTMLElement) {
                showIcon.classList.toggle('hidden', nextVisibleState);
            }

            if (hideIcon instanceof HTMLElement) {
                hideIcon.classList.toggle('hidden', !nextVisibleState);
            }
        });
    });
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
    const lowStockContainer = dashboardRoot.querySelector('[data-chart="low-stock"]');
    const updatedAtNode = dashboardRoot.querySelector('[data-updated-at]');
    const rangeButtons = [...dashboardRoot.querySelectorAll('[data-dashboard-range]')];
    const rangeStorageKey = 'motox.dashboard.range.days';
    const allowedTrendRanges = [2, 7, 30, 90, 180, 365];

    const normalizeTrendRange = (value) => {
        const parsed = Number.parseInt(String(value ?? ''), 10);
        return allowedTrendRanges.includes(parsed) ? parsed : 7;
    };

    let selectedTrendRange = 7;

    const setActiveRangeButton = (days) => {
        rangeButtons.forEach((button) => {
            const buttonDays = normalizeTrendRange(button.dataset.dashboardRange);
            button.classList.toggle('budget-range-pill-active', buttonDays === days);
        });
    };

    try {
        selectedTrendRange = normalizeTrendRange(localStorage.getItem(rangeStorageKey));
    } catch (error) {
        selectedTrendRange = 7;
    }

    setActiveRangeButton(selectedTrendRange);

    const renderTrendChart = (trend) => {
        if (!(trendContainer instanceof HTMLElement)) {
            return;
        }

        if (!Array.isArray(trend) || !trend.length) {
            trendContainer.innerHTML = '<p class="text-sm text-slate-500">No movement data available.</p>';
            return;
        }

        const seriesIn = trend.map((row) => Math.max(0, Number(row.in || 0)));
        const seriesOut = trend.map((row) => Math.max(0, Number(row.out || 0)));
        const maxValue = Math.max(1, ...seriesIn, ...seriesOut);

        const width = 980;
        const height = 280;
        const padding = { top: 18, right: 18, bottom: 34, left: 18 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const stepX = trend.length > 1 ? chartWidth / (trend.length - 1) : 0;

        const mapPoint = (value, index) => {
            const x = padding.left + (stepX * index);
            const y = padding.top + ((1 - (value / maxValue)) * chartHeight);

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

        const horizontalGrid = Array.from({ length: 6 }, (_, index) => {
            const y = padding.top + ((chartHeight / 5) * index);
            return `<line class="budget-grid-line" x1="${padding.left}" y1="${y.toFixed(2)}" x2="${(width - padding.right).toFixed(2)}" y2="${y.toFixed(2)}"></line>`;
        }).join('');

        const verticalGrid = trend.map((_, index) => {
            const x = padding.left + (stepX * index);
            return `<line class="budget-grid-line-vertical" x1="${x.toFixed(2)}" y1="${padding.top}" x2="${x.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>`;
        }).join('');

        const focusIndex = Math.max(0, trend.length - 2);
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
        const numberFormatter = new Intl.NumberFormat('en-US');
        const formatDelta = (value) => `${value >= 0 ? '+' : ''}${percentageFormatter.format(value)}%`;

        const focusRow = trend[focusIndex] ?? trend[trend.length - 1];
        const focusDate = new Date(`${focusRow.day ?? ''}T00:00:00`);
        const dateLabel = Number.isNaN(focusDate.valueOf())
            ? (focusRow.label ?? 'Current')
            : focusDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });

        const axisLabels = trend.map((row, index) => `
            <span class="budget-axis-label ${index === focusIndex ? 'budget-axis-label-active' : ''}">
                ${row.label ?? ''}
            </span>
        `).join('');

        trendContainer.style.setProperty('--budget-count', String(Math.max(1, trend.length)));
        trendContainer.innerHTML = `
            <div class="budget-chart-shell">
                <svg viewBox="0 0 ${width} ${height}" class="budget-chart-svg" role="img" aria-label="Stock in and stock out trend chart">
                    <defs>
                        <linearGradient id="budgetGradientIn" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(14, 116, 144, 0.24)" />
                            <stop offset="100%" stop-color="rgba(14, 116, 144, 0.02)" />
                        </linearGradient>
                        <linearGradient id="budgetGradientOut" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(239, 68, 68, 0.22)" />
                            <stop offset="100%" stop-color="rgba(239, 68, 68, 0.02)" />
                        </linearGradient>
                    </defs>
                    ${horizontalGrid}
                    ${verticalGrid}
                    <path class="budget-area-in" d="${toAreaPath(inPoints)}"></path>
                    <path class="budget-area-out" d="${toAreaPath(outPoints)}"></path>
                    <polyline class="budget-line budget-line-in" points="${toPolyline(inPoints)}"></polyline>
                    <polyline class="budget-line budget-line-out" points="${toPolyline(outPoints)}"></polyline>
                    <line class="budget-guide-line" x1="${focusX.toFixed(2)}" y1="${padding.top}" x2="${focusX.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>
                    <circle class="budget-point budget-point-in" cx="${(focusIn?.x ?? focusX).toFixed(2)}" cy="${(focusIn?.y ?? baselineY).toFixed(2)}" r="5"></circle>
                    <circle class="budget-point budget-point-out" cx="${(focusOut?.x ?? focusX).toFixed(2)}" cy="${(focusOut?.y ?? baselineY).toFixed(2)}" r="5"></circle>
                </svg>

                <article class="budget-tooltip-card" style="left: ${tooltipLeft}%;">
                    <div class="budget-tooltip-head">
                        <p class="budget-tooltip-date">${dateLabel}</p>
                        <span class="budget-tooltip-total">${numberFormatter.format(currentIn + currentOut)}</span>
                    </div>
                    <div class="budget-tooltip-divider"></div>
                    <div class="budget-tooltip-row">
                        <div>
                            <p class="budget-tooltip-amount">${numberFormatter.format(currentIn)}</p>
                            <p class="budget-tooltip-caption">Stock In</p>
                        </div>
                        <span class="budget-tooltip-change budget-tooltip-change-in">${formatDelta(deltaIn)}</span>
                    </div>
                    <div class="mt-3 budget-tooltip-row">
                        <div>
                            <p class="budget-tooltip-amount">${numberFormatter.format(currentOut)}</p>
                            <p class="budget-tooltip-caption">Stock Out</p>
                        </div>
                        <span class="budget-tooltip-change budget-tooltip-change-out">${formatDelta(deltaOut)}</span>
                    </div>
                </article>
            </div>

            <div class="budget-axis-row">${axisLabels}</div>
        `;
    };

    const renderLowStockChart = (rows) => {
        if (!(lowStockContainer instanceof HTMLElement)) {
            return;
        }

        if (!Array.isArray(rows) || !rows.length) {
            lowStockContainer.innerHTML = '<p class="text-sm text-slate-500">All categories are currently above minimum stock.</p>';
            return;
        }

        const maxCount = Math.max(1, ...rows.map((row) => Number(row.count || 0)));

        lowStockContainer.innerHTML = rows.map((row) => {
            const count = Number(row.count || 0);
            const width = Math.max(12, (count / maxCount) * 100);

            return `
                <article>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-800">${row.category ?? 'Unknown'}</p>
                        <p class="text-sm font-bold text-brand-700">${count}</p>
                    </div>
                    <div class="h-2.5 w-full rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-brand-500" style="width: ${width}%"></div>
                    </div>
                </article>
            `;
        }).join('');
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
        renderLowStockChart(payload?.low_stock_by_category ?? []);

        if (typeof payload?.trend_range_days === 'number') {
            selectedTrendRange = normalizeTrendRange(payload.trend_range_days);
            setActiveRangeButton(selectedTrendRange);
        }

        if (updatedAtNode instanceof HTMLElement && payload?.updated_at) {
            const updated = new Date(payload.updated_at);
            if (!Number.isNaN(updated.valueOf())) {
                updatedAtNode.textContent = `Updated ${updated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Manila' })} PHT`;
            }
        }
    };

    const buildMetricsUrl = (days) => {
        const url = new URL(metricsUrl, window.location.origin);
        url.searchParams.set('days', String(normalizeTrendRange(days)));
        return `${url.pathname}${url.search}`;
    };

    const fetchMetrics = async (days = selectedTrendRange) => {
        try {
            const response = await fetch(buildMetricsUrl(days), {
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
            const days = normalizeTrendRange(button.dataset.dashboardRange);
            selectedTrendRange = days;
            setActiveRangeButton(days);

            try {
                localStorage.setItem(rangeStorageKey, String(days));
            } catch (error) {
                console.warn('Unable to persist dashboard trend range', error);
            }

            fetchMetrics(days);
        });
    });

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
    }, 10000);
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

    const moneyFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
    const countFormatter = new Intl.NumberFormat('en-US');

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
            console.warn('Billing metrics polling failed', error);
        }
    };

    fetchMetrics();
    window.setInterval(fetchMetrics, 12000);
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

    const reportRangeStorageKey = 'motox.reports.range.months';
    const allowedRanges = [3, 6, 12];
    const normalizeRange = (value) => {
        const parsed = Number.parseInt(String(value ?? ''), 10);
        return allowedRanges.includes(parsed) ? parsed : 6;
    };

    let series = [];
    let selectedMonths = 6;

    try {
        const parsed = JSON.parse(chartContainer.dataset.series ?? '[]');
        if (Array.isArray(parsed)) {
            series = parsed;
        }
    } catch (error) {
        console.warn('Report trend payload parsing failed', error);
    }

    try {
        selectedMonths = normalizeRange(localStorage.getItem(reportRangeStorageKey));
    } catch (error) {
        selectedMonths = 6;
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

    const setActiveRange = (months) => {
        rangeButtons.forEach((button) => {
            button.classList.toggle('budget-range-pill-active', normalizeRange(button.dataset.reportRange) === months);
        });
    };

    const applyStatusBreakdown = (rows) => {
        if (!Array.isArray(rows) || !rows.length) {
            return;
        }

        rows.forEach((row) => {
            const key = String(row?.status ?? '')
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
        });
    };

    const renderSeries = (months) => {
        if (!series.length) {
            chartContainer.innerHTML = '<p class="text-sm text-slate-500">No monthly revenue data available yet.</p>';
            return;
        }

        const windowedSeries = series.slice(-months);
        const values = windowedSeries.map((row) => Math.max(0, Number(row?.value || 0)));
        const maxValue = Math.max(1, ...values);

        const width = 980;
        const height = 280;
        const padding = { top: 20, right: 18, bottom: 34, left: 18 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const stepX = windowedSeries.length > 1 ? chartWidth / (windowedSeries.length - 1) : 0;
        const baselineY = height - padding.bottom;

        const points = values.map((value, index) => {
            const x = padding.left + (stepX * index);
            const y = padding.top + ((1 - (value / maxValue)) * chartHeight);

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
            return `<line class="budget-grid-line" x1="${padding.left}" y1="${y.toFixed(2)}" x2="${(width - padding.right).toFixed(2)}" y2="${y.toFixed(2)}"></line>`;
        }).join('');

        const verticalGrid = windowedSeries.map((_, index) => {
            const x = padding.left + (stepX * index);
            return `<line class="budget-grid-line-vertical" x1="${x.toFixed(2)}" y1="${padding.top}" x2="${x.toFixed(2)}" y2="${baselineY.toFixed(2)}"></line>`;
        }).join('');

        const axisLabels = windowedSeries.map((row) => `
            <span class="budget-axis-label">${row?.label ?? ''}</span>
        `).join('');

        chartContainer.style.setProperty('--budget-count', String(Math.max(1, windowedSeries.length)));
        chartContainer.innerHTML = `
            <div class="budget-chart-shell">
                <svg viewBox="0 0 ${width} ${height}" class="budget-chart-svg" role="img" aria-label="Monthly revenue trend chart">
                    <defs>
                        <linearGradient id="reportRevenueGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgba(14, 116, 144, 0.26)" />
                            <stop offset="100%" stop-color="rgba(14, 116, 144, 0.03)" />
                        </linearGradient>
                    </defs>
                    ${horizontalGrid}
                    ${verticalGrid}
                    <path d="${areaPath}" fill="url(#reportRevenueGradient)"></path>
                    <polyline class="budget-line budget-line-in" points="${linePoints}"></polyline>
                    ${points.map((point) => `<circle class="budget-point budget-point-in" cx="${point.x.toFixed(2)}" cy="${point.y.toFixed(2)}" r="4.5"></circle>`).join('')}
                </svg>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                <span class="inline-flex items-center gap-2"><i class="budget-legend-dot budget-legend-dot-in"></i>Revenue</span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Peak PHP ${moneyFormatter.format(maxValue)}</span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">${months}-Month Window</span>
            </div>
            <div class="budget-axis-row">${axisLabels}</div>
        `;
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

        renderSeries(selectedMonths);
    };

    const fetchMetrics = async () => {
        if (!metricsUrl) {
            return;
        }

        try {
            const response = await fetch(metricsUrl, {
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

    setActiveRange(selectedMonths);
    renderSeries(selectedMonths);

    rangeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const months = normalizeRange(button.dataset.reportRange);
            selectedMonths = months;
            setActiveRange(months);
            renderSeries(months);

            try {
                localStorage.setItem(reportRangeStorageKey, String(months));
            } catch (error) {
                console.warn('Unable to persist reports trend range', error);
            }
        });
    });

    fetchMetrics();
    if (metricsUrl) {
        window.setInterval(fetchMetrics, 15000);
    }
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
    const markAsReadButton = document.querySelector('[data-mark-notifications-read]');
    const unreadDot = document.querySelector('[data-notification-dot]');
    const storageKey = 'motox.notifications.read';

    if (!(unreadDot instanceof HTMLElement)) {
        return;
    }

    const applyReadState = (isRead) => {
        unreadDot.classList.toggle('hidden', isRead);
    };

    try {
        const isRead = localStorage.getItem(storageKey) === '1';
        applyReadState(isRead);
    } catch (error) {
        applyReadState(false);
    }

    if (markAsReadButton instanceof HTMLButtonElement) {
        markAsReadButton.addEventListener('click', () => {
            applyReadState(true);
            try {
                localStorage.setItem(storageKey, '1');
            } catch (error) {
                console.warn('Unable to persist notification state', error);
            }
        });
    }
}

function initializeCustomersFilters() {
    const searchInput = document.getElementById('customer-search-input');
    const filterDateBtn = document.getElementById('filter-date-btn');
    const filterProgressBtn = document.getElementById('filter-progress-btn');
    const dateDropdown = document.getElementById('date-dropdown');
    const progressDropdown = document.getElementById('progress-dropdown');
    const customerRows = document.querySelectorAll('.customer-row');

    // Return early if no search input found (not on customers page)
    if (!(searchInput instanceof HTMLInputElement)) {
        return;
    }

    // Live search functionality
    searchInput.addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();

        customerRows.forEach(row => {
            const name = row.dataset.name?.toLowerCase() || '';
            const email = row.dataset.email?.toLowerCase() || '';
            const phone = row.dataset.phone?.toLowerCase() || '';

            if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateVisibleCount();
    });

    // Filter by Date dropdown toggle
    if (filterDateBtn instanceof HTMLElement && dateDropdown instanceof HTMLElement) {
        filterDateBtn.addEventListener('click', function () {
            dateDropdown.classList.toggle('hidden');
            if (progressDropdown instanceof HTMLElement) {
                progressDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!filterDateBtn.contains(e.target) && !dateDropdown.contains(e.target)) {
                dateDropdown.classList.add('hidden');
            }
        });

        // Date filter options
        const dateOptions = dateDropdown.querySelectorAll('[data-date-filter]');
        dateOptions.forEach(option => {
            option.addEventListener('click', function () {
                const filter = this.dataset.dateFilter;
                applyDateFilter(filter);
                dateDropdown.classList.add('hidden');
                const span = filterDateBtn.querySelector('span');
                if (span) {
                    span.textContent = this.textContent.trim();
                }
            });
        });
    }

    // Filter by Progress dropdown toggle
    if (filterProgressBtn instanceof HTMLElement && progressDropdown instanceof HTMLElement) {
        filterProgressBtn.addEventListener('click', function () {
            progressDropdown.classList.toggle('hidden');
            if (dateDropdown instanceof HTMLElement) {
                dateDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!filterProgressBtn.contains(e.target) && !progressDropdown.contains(e.target)) {
                progressDropdown.classList.add('hidden');
            }
        });

        // Progress filter options
        const progressOptions = progressDropdown.querySelectorAll('[data-progress-filter]');
        progressOptions.forEach(option => {
            option.addEventListener('click', function () {
                const filter = this.dataset.progressFilter;
                applyProgressFilter(filter);
                progressDropdown.classList.add('hidden');
                const span = filterProgressBtn.querySelector('span');
                if (span) {
                    span.textContent = this.textContent.trim();
                }
            });
        });
    }

    // Date filter logic
    function applyDateFilter(filter) {
        const now = new Date();
        let startDate = null;

        switch (filter) {
            case 'today':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case 'week':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                break;
            case 'month':
                startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                break;
            case 'year':
                startDate = new Date(now.getFullYear(), 0, 1);
                break;
            case 'all':
            default:
                customerRows.forEach(row => row.style.display = '');
                updateVisibleCount();
                return;
        }

        customerRows.forEach(row => {
            const createdAt = row.dataset.createdAt;
            if (createdAt && startDate) {
                const customerDate = new Date(createdAt);
                if (customerDate >= startDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        updateVisibleCount();
    }

    // Progress filter logic
    function applyProgressFilter(filter) {
        customerRows.forEach(row => {
            const activeJobs = parseInt(row.dataset.activeJobs) || 0;

            switch (filter) {
                case 'active':
                    row.style.display = activeJobs > 0 ? '' : 'none';
                    break;
                case 'no-active':
                    row.style.display = activeJobs === 0 ? '' : 'none';
                    break;
                case 'all':
                default:
                    row.style.display = '';
            }
        });

        updateVisibleCount();
    }

    // Update visible count
    function updateVisibleCount() {
        const visibleCount = Array.from(customerRows).filter(row => row.style.display !== 'none').length;
        const countElement = document.getElementById('visible-customers-count');
        if (countElement) {
            countElement.textContent = visibleCount + ' profiles';
        }
    }
}

function initializeJobOrdersFilters() {
    const searchInput = document.getElementById('joborder-search-input');
    const filterDateBtn = document.getElementById('filter-date-btn');
    const filterProgressBtn = document.getElementById('filter-progress-btn');
    const dateDropdown = document.getElementById('date-dropdown');
    const progressDropdown = document.getElementById('progress-dropdown');
    const jobOrderRows = document.querySelectorAll('.job-order-row');

    // Return early if no search input found (not on job orders page)
    if (!(searchInput instanceof HTMLInputElement)) {
        return;
    }

    // Live search functionality
    searchInput.addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();

        jobOrderRows.forEach(row => {
            const orderNumber = row.dataset.orderNumber?.toLowerCase() || '';
            const customer = row.dataset.customer?.toLowerCase() || '';
            const vehicle = row.dataset.vehicle?.toLowerCase() || '';
            const status = row.dataset.status?.toLowerCase() || '';

            if (orderNumber.includes(searchTerm) || customer.includes(searchTerm) || vehicle.includes(searchTerm) || status.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Filter by Date dropdown toggle
    if (filterDateBtn instanceof HTMLElement && dateDropdown instanceof HTMLElement) {
        filterDateBtn.addEventListener('click', function () {
            dateDropdown.classList.toggle('hidden');
            if (progressDropdown instanceof HTMLElement) {
                progressDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!filterDateBtn.contains(e.target) && !dateDropdown.contains(e.target)) {
                dateDropdown.classList.add('hidden');
            }
        });

        // Date filter options
        const dateOptions = dateDropdown.querySelectorAll('[data-date-filter]');
        dateOptions.forEach(option => {
            option.addEventListener('click', function () {
                const filterValue = this.dataset.dateFilter;
                applyDateFilter(filterValue);
                dateDropdown.classList.add('hidden');
                const span = filterDateBtn.querySelector('span');
                if (span) {
                    span.textContent = this.textContent.trim();
                }
            });
        });
    }

    // Filter by Progress dropdown toggle
    if (filterProgressBtn instanceof HTMLElement && progressDropdown instanceof HTMLElement) {
        filterProgressBtn.addEventListener('click', function () {
            progressDropdown.classList.toggle('hidden');
            if (dateDropdown instanceof HTMLElement) {
                dateDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!filterProgressBtn.contains(e.target) && !progressDropdown.contains(e.target)) {
                progressDropdown.classList.add('hidden');
            }
        });

        // Progress filter options
        const progressOptions = progressDropdown.querySelectorAll('[data-progress-filter]');
        progressOptions.forEach(option => {
            option.addEventListener('click', function () {
                const filterValue = this.dataset.progressFilter;
                applyProgressFilter(filterValue);
                progressDropdown.classList.add('hidden');
                const span = filterProgressBtn.querySelector('span');
                if (span) {
                    span.textContent = this.textContent.trim();
                }
            });
        });
    }

    // Date filter logic
    function applyDateFilter(filterValue) {
        const now = new Date();
        let startDate = null;

        switch (filterValue) {
            case 'today':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case 'week':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                break;
            case 'month':
                startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                break;
            case 'year':
                startDate = new Date(now.getFullYear(), 0, 1);
                break;
            case 'all':
            default:
                jobOrderRows.forEach(row => row.style.display = '');
                return;
        }

        jobOrderRows.forEach(row => {
            const createdAt = row.dataset.createdAt;
            if (createdAt && startDate) {
                const orderDate = new Date(createdAt);
                if (orderDate >= startDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

// Progress filter logic
    function applyProgressFilter(filterValue) {
        jobOrderRows.forEach(row => {
            const status = row.dataset.status || '';

            switch (filterValue) {
                case 'pending':
                    row.style.display = status === 'pending' ? '' : 'none';
                    break;
                case 'in_progress':
                    row.style.display = status === 'in_progress' ? '' : 'none';
                    break;
                case 'completed':
                    row.style.display = status === 'completed' ? '' : 'none';
                    break;
                case 'all':
                default:
                    row.style.display = '';
            }
        });
    }
}
