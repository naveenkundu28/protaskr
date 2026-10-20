document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.menu_compress_btn');
    const appShell = document.querySelector('.app-shell');
    const rightMenu = document.querySelector('#rightMenu');
    const submenuItems = Array.from(document.querySelectorAll('.sidebar-nav-item.has-submenu'));
    let flyoutCloseTimer = null;

    // below this width the sidebar starts collapsed (the user can still expand it)
    const collapseBelow = 1200;
    const smallScreen = window.matchMedia('(max-width: ' + (collapseBelow - 0.02) + 'px)');

    function setCollapsed(collapsed) {
        if (appShell) {
            appShell.classList.toggle('collapsed', collapsed);
        }

        if (rightMenu) {
            rightMenu.classList.toggle('collapsed', collapsed);
        }

        closeAllSubmenus();
        if (!collapsed) {
            const activeItem = submenuItems.find(function (item) {
                return item.classList.contains('is-active-route');
            });
            if (activeItem) {
                setAccordionOpen(activeItem, true);
            }
        }
    }

    function setAccordionOpen(item, open) {
        item.classList.toggle('is-open', open);
        const toggle = item.querySelector('.sidebar-submenu-toggle');
        const icon = toggle && toggle.querySelector('i');
        const submenu = item.querySelector('.sidebar-submenu');
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (submenu) {
            submenu.classList.toggle('show', open);
        }
        if (icon) {
            icon.classList.toggle('bi-plus-lg', !open);
            icon.classList.toggle('bi-dash-lg', open);
        }
    }

    function closeFlyout(item) {
        const submenu = item.querySelector('.sidebar-submenu');
        const toggle = item.querySelector('.sidebar-submenu-toggle');
        if (submenu) {
            submenu.classList.remove('show', 'is-flyout');
            submenu.style.removeProperty('--flyout-left');
            submenu.style.removeProperty('--flyout-top');
            submenu.style.removeProperty('--flyout-width');
        }
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
        item.classList.remove('is-flyout-open');
    }

    function closeAllSubmenus() {
        window.clearTimeout(flyoutCloseTimer);
        submenuItems.forEach(function (item) {
            setAccordionOpen(item, false);
            closeFlyout(item);
        });
    }

    function openFlyout(item) {
        if (!appShell || !appShell.classList.contains('collapsed')) return;
        window.clearTimeout(flyoutCloseTimer);
        submenuItems.forEach(function (otherItem) {
            if (otherItem !== item) closeFlyout(otherItem);
        });

        const submenu = item.querySelector('.sidebar-submenu');
        const toggle = item.querySelector('.sidebar-submenu-toggle');
        if (!submenu) return;

        item.classList.add('is-flyout-open');
        submenu.classList.add('show', 'is-flyout');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');

        const parentRect = item.getBoundingClientRect();
        const left = Math.max(8, Math.min(parentRect.right + 4, window.innerWidth - 168));
        const width = Math.min(250, Math.max(1, window.innerWidth - left - 8));
        const panelHeight = Math.min(submenu.scrollHeight, window.innerHeight - 16);
        const top = Math.max(8, Math.min(parentRect.top, window.innerHeight - panelHeight - 8));
        submenu.style.setProperty('--flyout-left', left + 'px');
        submenu.style.setProperty('--flyout-top', top + 'px');
        submenu.style.setProperty('--flyout-width', width + 'px');
    }

    submenuItems.forEach(function (item) {
        const toggle = item.querySelector('.sidebar-submenu-toggle');
        const submenu = item.querySelector('.sidebar-submenu');

        if (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (appShell && appShell.classList.contains('collapsed')) {
                    if (item.classList.contains('is-flyout-open')) {
                        closeFlyout(item);
                    } else {
                        openFlyout(item);
                    }
                    return;
                }

                const shouldOpen = !item.classList.contains('is-open');
                submenuItems.forEach(function (otherItem) {
                    setAccordionOpen(otherItem, false);
                });
                setAccordionOpen(item, shouldOpen);
            });
        }

        item.addEventListener('pointerenter', function () {
            if (appShell && appShell.classList.contains('collapsed')) openFlyout(item);
        });
        item.addEventListener('pointerleave', function () {
            if (appShell && appShell.classList.contains('collapsed')) {
                flyoutCloseTimer = window.setTimeout(function () {
                    closeFlyout(item);
                }, 180);
            }
        });
        if (submenu) {
            submenu.addEventListener('pointerenter', function () {
                window.clearTimeout(flyoutCloseTimer);
            });
            submenu.addEventListener('pointerleave', function () {
                if (appShell && appShell.classList.contains('collapsed')) closeFlyout(item);
            });
        }
    });

    document.addEventListener('pointerdown', function (event) {
        if (!appShell || !appShell.classList.contains('collapsed')) return;
        if (!event.target.closest('.sidebar-nav-item.has-submenu')) {
            submenuItems.forEach(closeFlyout);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') submenuItems.forEach(closeFlyout);
    });

    // collapsed by default on small screens
    setCollapsed(smallScreen.matches);

    // follow the screen size when it crosses the breakpoint (resize, rotate)
    smallScreen.addEventListener('change', function (event) {
        setCollapsed(event.matches);
    });

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (appShell) setCollapsed(!appShell.classList.contains('collapsed'));
        });
    });
});
