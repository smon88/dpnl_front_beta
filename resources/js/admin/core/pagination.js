/**
 * Pagination Module
 * Handles interactive pagination with AJAX support
 */

export class Pagination {
    constructor(container, options = {}) {
        this.container = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!this.container) {
            console.warn('Pagination: Container not found');
            return;
        }

        this.options = {
            contentSelector: options.contentSelector || '[data-pagination-content]',
            paginationSelector: options.paginationSelector || '[data-pagination]',
            onBeforeLoad: options.onBeforeLoad || null,
            onAfterLoad: options.onAfterLoad || null,
            onError: options.onError || null,
            scrollToTop: options.scrollToTop !== false,
            pushState: options.pushState !== false,
            ...options
        };

        this.contentEl = this.container.querySelector(this.options.contentSelector);
        this.paginationEl = this.container.querySelector(this.options.paginationSelector);
        this.loading = false;
        this.currentUrl = new URL(window.location.href);

        this.init();
    }

    init() {
        this.bindEvents();
        window.addEventListener('popstate', () => this.handlePopState());
    }

    bindEvents() {
        if (!this.paginationEl) return;

        // Handle page link clicks
        this.paginationEl.addEventListener('click', (e) => {
            const pageLink = e.target.closest('[data-page]');
            if (pageLink && !pageLink.classList.contains('active') && !pageLink.classList.contains('disabled')) {
                e.preventDefault();
                const page = pageLink.dataset.page;
                const pageName = this.paginationEl.dataset.pageName || 'page';
                this.goToPage(page, pageName);
            }
        });

        // Handle per-page select change
        const perPageSelect = this.paginationEl.querySelector('[data-per-page-select]');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', (e) => {
                const pageName = this.paginationEl.dataset.pageName || 'page';
                this.changePerPage(e.target.value, pageName);
            });
        }
    }

    async goToPage(page, pageName = 'page') {
        if (this.loading) return;

        const url = new URL(window.location.href);
        url.searchParams.set(pageName, page);

        await this.loadPage(url.toString());
    }

    async changePerPage(perPage, pageName = 'page') {
        if (this.loading) return;

        const url = new URL(window.location.href);

        // Determine the per_page param name based on pageName
        const perPageParam = pageName === 'page' ? 'per_page' : `per_page_${pageName}`;
        url.searchParams.set(perPageParam, perPage);

        // Reset to page 1 when changing per_page
        url.searchParams.set(pageName, '1');

        await this.loadPage(url.toString());
    }

    async loadPage(url) {
        if (this.loading) return;

        this.loading = true;
        this.setLoadingState(true);

        if (this.options.onBeforeLoad) {
            this.options.onBeforeLoad();
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Update content
            if (this.contentEl && data.html) {
                this.contentEl.innerHTML = data.html;
            }

            // Update pagination
            if (this.paginationEl && data.pagination) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.pagination;
                const newPagination = tempDiv.firstElementChild;

                if (newPagination) {
                    this.paginationEl.replaceWith(newPagination);
                    this.paginationEl = newPagination;
                    this.bindEvents();
                }
            }

            // Update URL
            if (this.options.pushState) {
                window.history.pushState({ url }, '', url);
            }

            // Scroll to top of container
            if (this.options.scrollToTop) {
                this.container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (this.options.onAfterLoad) {
                this.options.onAfterLoad(data);
            }

        } catch (error) {
            console.error('Pagination error:', error);
            if (this.options.onError) {
                this.options.onError(error);
            } else {
                // Fallback: navigate normally
                window.location.href = url;
            }
        } finally {
            this.loading = false;
            this.setLoadingState(false);
        }
    }

    setLoadingState(loading) {
        if (this.paginationEl) {
            this.paginationEl.classList.toggle('loading', loading);
        }
        if (this.contentEl) {
            this.contentEl.classList.toggle('loading', loading);
        }
    }

    handlePopState() {
        this.loadPage(window.location.href);
    }

    destroy() {
        window.removeEventListener('popstate', () => this.handlePopState());
    }
}

/**
 * Simple pagination that uses standard page navigation (no AJAX)
 * Just handles the per-page selector
 */
export class SimplePagination {
    constructor(container) {
        this.container = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!this.container) return;

        this.init();
    }

    init() {
        // Find all pagination wrappers in the container
        const paginationWrappers = this.container.querySelectorAll('[data-pagination]');

        paginationWrappers.forEach(wrapper => {
            const perPageSelect = wrapper.querySelector('[data-per-page-select]');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', (e) => {
                    this.handlePerPageChange(e.target, wrapper);
                });
            }
        });
    }

    handlePerPageChange(select, wrapper) {
        const pageName = wrapper.dataset.pageName || 'page';
        const perPageParam = pageName === 'page' ? 'per_page' : `per_page_${pageName}`;

        const url = new URL(window.location.href);
        url.searchParams.set(perPageParam, select.value);
        url.searchParams.set(pageName, '1'); // Reset to page 1

        // Show loading state
        wrapper.classList.add('loading');

        window.location.href = url.toString();
    }
}

/**
 * Auto-initialize pagination on DOMContentLoaded
 */
export function initPagination(selector = '[data-pagination-container]', useAjax = false) {
    document.querySelectorAll(selector).forEach(container => {
        if (useAjax) {
            new Pagination(container);
        } else {
            new SimplePagination(container);
        }
    });
}

// Auto-init simple pagination for all pages
document.addEventListener('DOMContentLoaded', () => {
    // Initialize simple pagination (non-AJAX) by default
    initPagination('body', false);
});
