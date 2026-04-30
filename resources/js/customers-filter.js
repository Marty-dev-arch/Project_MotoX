document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('customer-search-input');
    const filterDateBtn = document.getElementById('filter-date-btn');
    const filterProgressBtn = document.getElementById('filter-progress-btn');
    const dateDropdown = document.getElementById('date-dropdown');
    const progressDropdown = document.getElementById('progress-dropdown');
    const customerRows = document.querySelectorAll('.customer-row');

    // Live search functionality
    if (searchInput) {
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
    }

    // Filter by Date dropdown toggle
    if (filterDateBtn && dateDropdown) {
        filterDateBtn.addEventListener('click', function () {
            dateDropdown.classList.toggle('hidden');
            progressDropdown.classList.add('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!filterDateBtn.contains(e.target) && !dateDropdown.contains(e.target)) {
                dateDropdown.classList.add('hidden');
            }
        });

        // Date filter options
        dateDropdown.querySelectorAll('[data-date-filter]').forEach(option => {
            option.addEventListener('click', function () {
                const filter = this.dataset.dateFilter;
                applyDateFilter(filter);
                dateDropdown.classList.add('hidden');
                filterDateBtn.querySelector('span').textContent = this.textContent.trim();
            });
        });
    }

    // Filter by Progress dropdown toggle
    if (filterProgressBtn && progressDropdown) {
        filterProgressBtn.addEventListener('click', function () {
            progressDropdown.classList.toggle('hidden');
            dateDropdown.classList.add('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!filterProgressBtn.contains(e.target) && !progressDropdown.contains(e.target)) {
                progressDropdown.classList.add('hidden');
            }
        });

        // Progress filter options
        progressDropdown.querySelectorAll('[data-progress-filter]').forEach(option => {
            option.addEventListener('click', function () {
                const filter = this.dataset.progressFilter;
                applyProgressFilter(filter);
                progressDropdown.classList.add('hidden');
                filterProgressBtn.querySelector('span').textContent = this.textContent.trim();
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
                // Show all
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
                    if (activeJobs > 0) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                    break;
                case 'no-active':
                    if (activeJobs === 0) {
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
        const visibleCount = Array.from(customerRows).filter(row => row.style.display !== 'none').length;
        const countElement = document.getElementById('visible-customers-count');
        if (countElement) {
            countElement.textContent = visibleCount + ' profiles';
        }
    }
});
