<!-- scripts start-->
@livewireScripts
<!-- latest jquery-->
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>

<!-- Bootstrap js-->
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

<!-- Simple bar js-->
<script src="{{ asset('assets/vendor/simplebar/simplebar.js') }}"></script>

<!-- phosphor js -->
<script src="{{ asset('assets/vendor/phosphor/phosphor.js') }}"></script>

<!-- Customizer js-->
<script src="{{ asset('assets/js/customizer.js') }}"></script>

<!-- prism js-->
<script src="{{ asset('assets/vendor/prism/prism.min.js') }}"></script>

<!-- App js-->
<script src="{{ asset('assets/js/script.js') }}"></script>

<script>
    /**
     * Manages sidebar menu active state handling differently main links and create routes
     */
    const initializeSidebarActiveState = () => {
        const cleanMenuState = () => {
            document.querySelectorAll('.main-nav li, .main-nav a').forEach(el => {
                el.classList.remove('active', 'show');
                if (el.hasAttribute('aria-expanded')) {
                    el.setAttribute('aria-expanded', 'false');
                }
            });

            document.querySelectorAll('.collapse').forEach(collapse => {
                collapse.classList.remove('show');
                collapse.setAttribute('aria-expanded', 'false');
            });
        };

        const activateMenuElement = (exactMatch) => {
            const directParentLi = exactMatch.closest('li');
            if (directParentLi) {
                directParentLi.classList.add('active');
                exactMatch.classList.add('active');
            }

            const parentCollapse = exactMatch.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                parentCollapse.setAttribute('aria-expanded', 'true');

                const parentButton = document.querySelector(`[href="#${parentCollapse.id}"]`);
                if (parentButton) {
                    parentButton.classList.add('active', 'show');
                    parentButton.setAttribute('aria-expanded', 'true');
                }
            }
        };

        const handleSidebarState = () => {
            const currentPath = window.location.pathname;

            if (currentPath.endsWith('/create')) {
                const createLink = Array.from(document.querySelectorAll('.collapse a')).find(link => {
                    const href = link.getAttribute('href').replace(window.location.origin, '');
                    return href === currentPath;
                });

                if (createLink) {
                    cleanMenuState();
                    activateMenuElement(createLink);
                }
                return;
            }

            const exactMatch = Array.from(document.querySelectorAll('.main-nav a')).find(link => {
                const href = link.getAttribute('href').replace(window.location.origin, '');
                return href === currentPath;
            });

            if (exactMatch) {
                activateMenuElement(exactMatch);
            }
        };

        setTimeout(handleSidebarState, 0);
    };

    document.addEventListener('DOMContentLoaded', initializeSidebarActiveState);
</script>

<!-- Toatify js-->
<script src="{{ asset('assets/vendor/notifications/toastify-js.js') }}"></script>

<!-- sweetalert js-->
<script src="{{ asset('assets/vendor/sweetalert/sweetalert.js') }}"></script>

<!-- js -->
<script src="{{ asset('assets/js/sweet_alert.js') }}"></script>
