document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const modeButtons = [...document.querySelectorAll('[data-mode]')];

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

        if (updatedAtNode instanceof HTMLElement && payload?.updated_at) {
            const updated = new Date(payload.updated_at);
            if (!Number.isNaN(updated.valueOf())) {
                updatedAtNode.textContent = `Updated ${updated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
            }
        }
    };

    const fetchMetrics = async () => {
        try {
            const response = await fetch(metricsUrl, {
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

    if (trendContainer instanceof HTMLElement) {
        try {
            const initialTrend = JSON.parse(trendContainer.dataset.trend ?? '[]');
            renderTrendChart(initialTrend);
        } catch (error) {
            console.warn('Initial trend payload parsing failed', error);
        }
    }

    fetchMetrics();
    window.setInterval(fetchMetrics, 10000);
}
