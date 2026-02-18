/**
 * Navigation State Manager
 * Handles persistence of the sidebar navigation state (collapsed or expanded)
 */
const NavStateManager = {
    storageKey: 'navState',
    mobileBreakpoint: 767,
    classes: {
        collapsed: 'semi-nav'
    },

    init: function() {
        this.$nav = $('nav');
        this.restoreState();
        this.setupEventListeners();
    },

    isMobile: function() {
        return window.innerWidth <= this.mobileBreakpoint;
    },

    setupEventListeners: function() {
        var self = this;

        $(document).on('click', '.header-toggle', function() {
            self.toggleState();
        });

        $('.toggle-semi-nav').on('click', function() {
            self.setExpanded();
        });

        // Close sidebar when clicking the overlay on mobile/tablet
        $(document).on('click', '.app-content', function(e) {
            if (!self.isMobile()) return;
            if (!self.$nav.hasClass(self.classes.collapsed)) return;
            if ($(e.target).closest('header').length) return;
            self.closeMobileSidebar();
        });

        // Close sidebar when clicking a navigation link on mobile
        $(document).on('click', 'nav .main-nav a[href]:not([data-bs-toggle])', function() {
            if (self.isMobile() && self.$nav.hasClass(self.classes.collapsed)) {
                self.closeMobileSidebar();
            }
        });
    },

    toggleState: function() {
        this.$nav.toggleClass(this.classes.collapsed);
        if (!this.isMobile()) {
            this.saveState();
        }
    },

    closeMobileSidebar: function() {
        this.$nav.removeClass(this.classes.collapsed);
    },

    setExpanded: function() {
        this.$nav.removeClass(this.classes.collapsed);
        this.saveState();
    },

    setCollapsed: function() {
        this.$nav.addClass(this.classes.collapsed);
        this.saveState();
    },

    saveState: function() {
        var state = this.$nav.hasClass(this.classes.collapsed) ? 'semi-nav' : 'full-nav';
        localStorage.setItem(this.storageKey, state);
    },

    restoreState: function() {
        if (this.isMobile()) {
            this.$nav.removeClass(this.classes.collapsed);
            return;
        }

        var savedState = localStorage.getItem(this.storageKey);
        if (savedState === 'semi-nav') {
            this.$nav.addClass(this.classes.collapsed);
        } else {
            this.$nav.removeClass(this.classes.collapsed);
        }
    }
};

$(document).ready(function() {
    NavStateManager.init();
});
