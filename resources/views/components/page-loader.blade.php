{{--
    Page Loader Component
    Shows a loading spinner when navigating between pages

    Usage in layout (add once):
    <x-page-loader />

    Options:
    <x-page-loader text="Cargando..." />
    <x-page-loader :showText="false" />
--}}

@props([
    'text' => 'Cargando',
    'showText' => true,
])

<div class="page-loader" id="pageLoader">
    <div class="page-loader-content">
        <div class="page-loader-spinner">
            <div class="page-loader-circle"></div>
        </div>
        @if($showText)
            <span class="page-loader-text">{{ $text }}</span>
        @endif
    </div>
    <div class="page-loader-progress" id="pageLoaderProgress"></div>
</div>

<style>
.page-loader {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(10, 10, 15, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 99999;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.page-loader.active {
    display: flex;
    opacity: 1;
}

.page-loader.fade-out {
    opacity: 0;
}

.page-loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    animation: pageLoaderPulse 0.3s ease-out;
}

@keyframes pageLoaderPulse {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.page-loader-spinner {
    position: relative;
    width: 56px;
    height: 56px;
}

.page-loader-circle {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: var(--devil-red, #e63946);
    border-right-color: var(--fire-orange, #f77f00);
    animation: pageLoaderSpin 0.8s linear infinite;
}

@keyframes pageLoaderSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.page-loader-text {
    font-size: 14px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.5px;
}

/* Progress bar at top */
.page-loader-progress {
    position: absolute;
    top: 0;
    left: 0;
    height: 3px;
    width: 0;
    background: linear-gradient(90deg, var(--devil-red, #e63946), var(--fire-orange, #f77f00));
    box-shadow: 0 0 10px rgba(230, 57, 70, 0.5);
    transition: width 0.3s ease;
}

.page-loader.active .page-loader-progress {
    animation: pageLoaderProgressAnim 2s ease-out forwards;
}

@keyframes pageLoaderProgressAnim {
    0% { width: 0; }
    20% { width: 30%; }
    50% { width: 60%; }
    80% { width: 85%; }
    100% { width: 95%; }
}
</style>

<script>
(function() {
    const loader = document.getElementById('pageLoader');
    if (!loader) return;

    // Elements that should NOT trigger the loader
    const excludeSelectors = [
        '[data-no-loader]',
        '[target="_blank"]',
        '[href^="#"]',
        '[href^="javascript:"]',
        '[href^="mailto:"]',
        '[href^="tel:"]',
        '[onclick]'
    ].join(',');

    // Show loader function
    function showLoader() {
        loader.classList.add('active');
    }

    // Hide loader function (for back navigation)
    function hideLoader() {
        loader.classList.add('fade-out');
        setTimeout(() => {
            loader.classList.remove('active', 'fade-out');
        }, 200);
    }

    // Handle link clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href]');

        if (!link) return;
        if (link.matches(excludeSelectors)) return;

        const href = link.getAttribute('href');

        // Skip empty, hash, or javascript links
        if (!href || href === '#' || href.startsWith('javascript:')) return;

        // Skip external links
        if (link.hostname && link.hostname !== window.location.hostname) return;

        // Skip if modifier key is pressed (new tab)
        if (e.ctrlKey || e.metaKey || e.shiftKey) return;

        // Skip if navigating to current page
        const currentPath = window.location.pathname + window.location.search;
        const linkPath = new URL(link.href).pathname + new URL(link.href).search;
        if (currentPath === linkPath) return;

        // Show loader
        showLoader();
    });

    // Handle form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;

        // Skip forms with data-no-loader attribute
        if (form.hasAttribute('data-no-loader')) return;

        // Skip forms that open in new tab
        if (form.target === '_blank') return;

        // Don't show loader for AJAX forms (if they prevent default)
        // The loader will show but be hidden if page doesn't navigate

        // Small delay to allow form validation to cancel submission
        setTimeout(() => {
            if (!e.defaultPrevented) {
                showLoader();
            }
        }, 10);
    });

    // Hide loader when navigating back (bfcache)
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            hideLoader();
        }
    });

    // Hide loader on page load (safety net)
    window.addEventListener('load', function() {
        hideLoader();
    });

    // Expose global functions
    window.PageLoader = {
        show: showLoader,
        hide: hideLoader
    };
})();
</script>
