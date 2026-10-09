/* Public portal behaviour: navigation drawer, nested menu toggles, copyable
   page addresses and progress bar reveal. Written against plain DOM APIs so no
   build step or framework is required. */
(function () {
    'use strict';

    var body = document.body;

    /* ---- navigation drawer ------------------------------------------- */

    var toggle = document.querySelector('[data-nav-toggle]');
    var closer = document.querySelector('[data-nav-close]');
    var overlay = document.querySelector('[data-nav-overlay]');

    function closeNav() {
        body.classList.remove('nav-open');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openNav() {
        body.classList.add('nav-open');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (body.classList.contains('nav-open')) {
                closeNav();
            } else {
                openNav();
            }
        });
    }

    if (closer) {
        closer.addEventListener('click', closeNav);
    }

    if (overlay) {
        overlay.addEventListener('click', closeNav);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNav();
            document.querySelectorAll('.has-children.is-open').forEach(function (parent) {
                parent.classList.remove('is-open');
            });
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) {
            closeNav();
        }
    });

    /* ---- nested menu carets (touch and small screens) ---------------- */

    document.querySelectorAll('[data-nav-caret]').forEach(function (caret) {
        caret.addEventListener('click', function (event) {
            event.preventDefault();
            var parent = caret.closest('.has-children');
            if (!parent) {
                return;
            }
            var open = parent.classList.toggle('is-open');
            caret.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    /* ---- reveal progress bars when they scroll into view -------------- */

    var meters = document.querySelectorAll('.meter .fill');

    if (meters.length) {
        var paint = function (fill) {
            fill.style.width = (fill.getAttribute('data-value') || 0) + '%';
        };

        if ('IntersectionObserver' in window) {
            var watcher = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        paint(entry.target);
                        watcher.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            meters.forEach(function (fill) {
                fill.style.width = '0%';
                fill.style.transition = 'width .9s cubic-bezier(.22,.7,.3,1)';
                watcher.observe(fill);
            });
        } else {
            meters.forEach(paint);
        }
    }

    /* ---- mark the active sidebar entry on long pages ------------------ */

    var anchors = document.querySelectorAll('[data-anchor-link]');

    if (anchors.length && 'IntersectionObserver' in window) {
        var targets = [];

        anchors.forEach(function (link) {
            var id = link.getAttribute('href');
            if (id && id.charAt(0) === '#') {
                var section = document.querySelector(id);
                if (section) {
                    targets.push({ link: link, section: section });
                }
            }
        });

        if (targets.length) {
            var spy = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }
                    targets.forEach(function (pair) {
                        pair.link.classList.toggle('is-reading', pair.section === entry.target);
                    });
                });
            }, { rootMargin: '-25% 0px -65% 0px' });

            targets.forEach(function (pair) {
                spy.observe(pair.section);
            });
        }
    }
})();
