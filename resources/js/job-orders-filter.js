document.addEventListener('DOMContentLoaded', function () {
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
                updateVisibleCount();
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

        updateVisibleCount();
    }

    // Progress filter logic
    function applyProgressFilter(filterValue) {
        jobOrderRows.forEach(row => {
            const status = row.dataset.status || '';

            switch (filterValue) {
                case 'active':
                    // Show orders with in_progress status
                    if (status === 'in_progress') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                    break;
                case 'no-active':
                    // Show orders that are not in_progress (completed, cancelled, pending)
                    if (status !== 'in_progress') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
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
        const visibleCount = Array.from(jobOrderRows).filter(row => row.style.display !== 'none').length;
        const countElement = document.getElementById('visible-joborders-count');
        if (countElement) {
            countElement.textContent = visibleCount + ' orders';
        }
    }
});
