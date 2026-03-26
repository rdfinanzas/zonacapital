/**
 * CustomPagination - A JavaScript pagination plugin
 * 
 * This class creates a pagination interface with:
 * - Page number display as boxes with current page highlighted
 * - Record count information
 * - Page size selector
 * - Page selector
 * - Navigation arrows
 */
class CustomPagination {
    /**
     * Initialize the pagination plugin
     * @param {HTMLElement|string} container - The container element or selector
     * @param {Object} options - Optional configuration
     * @param {Function} options.onPageChange - Callback when page changes
     * @param {Function} options.onPageSizeChange - Callback when page size changes
     * @param {number} options.initialPageSize - Initial page size
     * @param {Array} options.pageSizeOptions - Available page size options
     */
    constructor(container, options = {}) {
        // Store the container
        this.container = typeof container === 'string' ? document.querySelector(container) : container;

        if (!this.container) {
            console.error('CustomPagination: Container not found');
            return;
        }

        // Default options
        this.options = {
            onPageChange: () => { },
            onPageSizeChange: () => { },
            initialPageSize: 10,
            pageSizeOptions: [5, 10, 25, 50, 100],
            ...options
        };

        // Initialize properties
        this.totalRecords = 0;
        this.pageSize = this.options.initialPageSize;
        this.currentPage = 1;
        this.totalPages = 0;

        // Create DOM elements
        this.createElements();

        // Initial render
        this.render();
    }

    /**
     * Create the DOM elements for the pagination
     */
    createElements() {

        console.log(this.container)
        // Main container
        this.container.classList.add('custom-pagination');
        this.container.innerHTML = '';

        // Create info section (showing X of Y records)
        this.infoElement = document.createElement('div');
        this.infoElement.classList.add('pagination-info');

        // Create page size selector
        this.pageSizeContainer = document.createElement('div');

        this.pageSizeContainer.classList.add('page-size-container');

        const pageSizeLabel = document.createElement('label');
        pageSizeLabel.textContent = 'Mostrar: ';

        this.pageSizeSelect = document.createElement('select');

        this.pageSizeSelect.classList.add('page-size-select');


        this.options.pageSizeOptions.forEach(size => {
            const option = document.createElement('option');
            option.value = size;
            option.textContent = size;
            if (size === this.pageSize) {
                option.selected = true;
            }
            this.pageSizeSelect.appendChild(option);
        });

        this.pageSizeSelect.addEventListener('change', () => {
            const newPageSize = parseInt(this.pageSizeSelect.value);
            this.pageSize = newPageSize;
            this.currentPage = 1; // Reset to first page
            this.render();
            this.options.onPageSizeChange();
        });

        this.pageSizeContainer.appendChild(pageSizeLabel);
        this.pageSizeContainer.appendChild(this.pageSizeSelect);

        // Create pagination controls
        this.paginationContainer = document.createElement('div');
        this.paginationContainer.classList.add('pagination-controls');

        // Create page selector
        this.pageSelectContainer = document.createElement('div');
        this.pageSelectContainer.classList.add('page-select-container');

        const pageLabel = document.createElement('label');
        pageLabel.textContent = 'Página: ';

        this.pageSelect = document.createElement('select');
        this.pageSelect.classList.add('page-select');

        this.pageSelect.addEventListener('change', () => {
            const newPage = parseInt(this.pageSelect.value);
            if (newPage !== this.currentPage) {
                this.currentPage = Number(newPage);
                this.render();
                this.options.onPageChange(this.currentPage);
            }
        });

        this.pageSelectContainer.appendChild(pageLabel);
        this.pageSelectContainer.appendChild(this.pageSelect);

        // Append all elements
        this.container.appendChild(this.infoElement);
        this.container.appendChild(this.pageSizeContainer);
        this.container.appendChild(this.paginationContainer);
        this.container.appendChild(this.pageSelectContainer);
    }

    /**
     * Set pagination data and re-render
     * @param {number} totalRecords - Total number of records
     * @param {number} pageSize - Number of records per page
     * @param {number} currentPage - Current page number
     */
    setPaginationData(totalRecords, pageSize, currentPage) {
        //console log de los parametros
        console.log('setPaginationData called with:', { totalRecords, pageSize, currentPage });
        this.totalRecords = totalRecords;
        this.pageSize = pageSize;
        this.currentPage = Number(currentPage);

        // Calculate total pages
        this.totalPages = Math.max(1, Math.ceil(this.totalRecords / this.pageSize));

        // Ensure current page is within range
        if (this.currentPage > this.totalPages) {
            this.currentPage = this.totalPages;
        }
        if (this.currentPage < 1) {
            this.currentPage = 1;
        }

        // Update page size selector
        this.updatePageSizeSelector();

        // Re-render the pagination
        this.render();
    }

    /**
     * Update page size selector to match current page size
     */
    updatePageSizeSelector() {
        const options = this.pageSizeSelect.options;
        for (let i = 0; i < options.length; i++) {
            if (parseInt(options[i].value) === this.pageSize) {
                this.pageSizeSelect.selectedIndex = i;
                break;
            }
        }
    }

    /**
     * Render the pagination controls
     */
    render() {
        // Update info text
        const start = (this.currentPage - 1) * this.pageSize + 1;
        const end = Math.min(start + this.pageSize - 1, this.totalRecords);
        this.infoElement.textContent = `Mostrando ${start}-${end} de ${this.totalRecords} registros`;

        // Update page select
        this.pageSelect.innerHTML = '';
        for (let i = 1; i <= this.totalPages; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `${i}`;
            if (i === this.currentPage) {
                option.selected = true;
            }
            this.pageSelect.appendChild(option);
        }

        // Update pagination controls
        this.paginationContainer.innerHTML = '';

        // First page & previous buttons
        const firstBtn = this.createButton('<<', 1, this.currentPage > 1);
        const prevBtn = this.createButton('<', this.currentPage - 1, this.currentPage > 1);

        this.paginationContainer.appendChild(firstBtn);
        this.paginationContainer.appendChild(prevBtn);

        // Page number boxes
        const pageNumbers = this.getVisiblePageNumbers();
        pageNumbers.forEach(pageNum => {
            const pageBtn = this.createPageButton(pageNum);
            this.paginationContainer.appendChild(pageBtn);
        });

        // Next page & last buttons
        const nextBtn = this.createButton('>', this.currentPage + 1, this.currentPage < this.totalPages);
        const lastBtn = this.createButton('>>', this.totalPages, this.currentPage < this.totalPages);

        this.paginationContainer.appendChild(nextBtn);
        this.paginationContainer.appendChild(lastBtn);
    }

    /**
     * Get visible page numbers based on current page
     * Shows 5 page numbers at a time with current page in the middle when possible
     */
    getVisiblePageNumbers() {
        if (this.totalPages <= 5) {
            // If total pages are 5 or less, show all page numbers
            return Array.from({ length: this.totalPages }, (_, i) => i + 1);
        }

        // Otherwise, show 5 pages with current page in middle when possible
        let start = Math.max(1, this.currentPage - 2);
        let end = start + 4;

        // Adjust if end is greater than total pages
        if (end > this.totalPages) {
            end = this.totalPages;
            start = Math.max(1, end - 4);
        }

        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    }

    /**
     * Create a navigation button
     */
    createButton(text, pageNum, isEnabled) {
        const button = document.createElement('button');
        button.textContent = text;
        button.classList.add('pagination-btn');

        if (!isEnabled) {
            button.disabled = true;
            button.classList.add('disabled');
        } else {
            button.addEventListener('click', () => {
                this.currentPage = pageNum;
                this.render();
                this.options.onPageChange(this.currentPage);
            });
        }

        return button;
    }

    /**
     * Create a page number button
     */
    createPageButton(pageNum) {
        pageNum = Number(pageNum);
        const button = document.createElement('button');
        button.textContent = pageNum;
        button.classList.add('pagination-btn', 'page-number');
        //alert(pageNum + " " + this.currentPage)

        if (pageNum === this.currentPage) {
            button.classList.add('active');
        }

        button.addEventListener('click', () => {
            if (pageNum !== this.currentPage) {
                this.currentPage = pageNum;
                this.render();
                this.options.onPageChange(this.currentPage);
            }
        });

        return button;
    }
}

// Add to global scope if window exists (browser environment)
if (typeof window !== 'undefined') {
    window.CustomPagination = CustomPagination;
}
