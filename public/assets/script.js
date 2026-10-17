document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.menu_compress_btn');
    const appShell = document.querySelector('.app-shell');
    const rightMenu = document.querySelector('#rightMenu');

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
    }

    // collapsed by default on small screens
    setCollapsed(smallScreen.matches);

    // follow the screen size when it crosses the breakpoint (resize, rotate)
    smallScreen.addEventListener('change', function (event) {
        setCollapsed(event.matches);
    });

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (appShell) {
                appShell.classList.toggle('collapsed');
            }

            if (rightMenu) {
                rightMenu.classList.toggle('collapsed');
            }
        });
    });
});
