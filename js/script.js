// Header Off canvas
$(document).ready(function () {

    const $menuToggle = $('#mobileMenuToggle');
    const $menuClose = $('#mobileMenuClose');
    const $offcanvas = $('#mobileOffcanvas');
    const $overlay = $('#offcanvasOverlay');

    function openMenu() {
        $offcanvas.addClass('active');
        $overlay.addClass('active');

        $menuToggle.attr('aria-expanded', 'true');
        $offcanvas.attr('aria-hidden', 'false');

        $('body').addClass('offcanvas-open');
    }

    function closeMenu() {
        $offcanvas.removeClass('active');
        $overlay.removeClass('active');

        $menuToggle.attr('aria-expanded', 'false');
        $offcanvas.attr('aria-hidden', 'true');

        $('body').removeClass('offcanvas-open');
    }

    // Open menu
    $menuToggle.on('click', function () {
        openMenu();
    });

    // Close menu
    $menuClose.on('click', function () {
        closeMenu();
    });

    // Close when clicking overlay
    $overlay.on('click', function () {
        closeMenu();
    });

    // Close after clicking navigation link
    $('.mobile-nav-menu a').on('click', function () {
        closeMenu();
    });

    // Close with Escape key
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    // Owl Carousel Hero Sec
    $('.owl-carousel-wrapper').owlCarousel({
        loop: true,
        margin: 0,
        responsiveClass: true,
        nav: false,
        dots: false,
        autoplay: true,
        responsive: {
            0: {
                items: 1,
            },
            600: {
                items: 1,
            },
            1000: {
                items: 1,
            }
        }   
    });

    // owl getting started steps
    
    $('.starting-steps').owlCarousel({
        loop: false,
        margin: 30,
        responsiveClass: true,
        nav: false,
        dots: true,
        autoplay: false,
        responsive: {
            0: {
                items: 1,
            },
            768: {
                items: 2,
            },
            1000: {
                items: 3,
            }
        }   
    });
});

(function () {
    var header = document.querySelector('header.main-header');
    var navigationLinks = Array.prototype.slice.call(document.querySelectorAll(
        '.header-nav .nav-menu a, .mobile-nav-menu a'
    ));
    var homeSection = document.querySelector('.main-hero-sec');
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var activeTarget;
    var ticking = false;

    if (!header || !navigationLinks.length || !homeSection) {
        return;
    }

    function updateHeaderHeight() {
        document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
    }

    function getTarget(link) {
        var href = link.getAttribute('href') || '';
        var hash = href.indexOf('#') === 0 ? href.slice(1) : '';

        if (!hash && (href === '' || href === '#' || href.split('#')[0].split('/').pop() === 'index.php')) {
            return homeSection;
        }

        return hash ? document.getElementById(hash) : null;
    }

    function setActive(target) {
        if (!target || target === activeTarget) {
            return;
        }

        activeTarget = target;
        navigationLinks.forEach(function (link) {
            var isActive = getTarget(link) === target;
            link.classList.toggle('active', isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function updateActiveFromScroll() {
        ticking = false;
        var threshold = window.scrollY + header.offsetHeight + 16;
        var targets = [homeSection].concat(navigationLinks.map(getTarget).filter(function (target) {
            return target && target !== homeSection;
        }));
        var currentTarget = homeSection;

        targets.forEach(function (target) {
            if (target.offsetTop <= threshold) {
                currentTarget = target;
            }
        });

        setActive(currentTarget);
    }

    function requestActiveUpdate() {
        if (ticking) {
            return;
        }
        ticking = true;
        window.requestAnimationFrame(updateActiveFromScroll);
    }

    navigationLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = getTarget(link);
            if (!target) {
                return;
            }

            event.preventDefault();
            setActive(target);
            target.scrollIntoView({
                behavior: reduced ? 'auto' : 'smooth',
                block: 'start'
            });

            if (target.id) {
                history.pushState(null, '', '#' + target.id);
            } else {
                history.pushState(null, '', window.location.pathname + window.location.search);
            }
        });
    });

    updateHeaderHeight();
    setActive(homeSection);
    updateActiveFromScroll();
    window.addEventListener('resize', function () {
        updateHeaderHeight();
        requestActiveUpdate();
    });
    window.addEventListener('scroll', requestActiveUpdate, { passive: true });
    window.addEventListener('popstate', requestActiveUpdate);
    window.addEventListener('hashchange', requestActiveUpdate);
})();

(function () {
    var html = document.documentElement;
    var reduced = html.classList.contains('pm-reduced-motion') ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var loaderHidden = false;

    function hideLoader() {
        if (loaderHidden) {
            return;
        }
        loaderHidden = true;

        var loader = document.getElementById('pmPageLoader');
        html.classList.remove('pm-loading');

        if (!loader) {
            return;
        }

        if (reduced) {
            loader.classList.add('is-hidden');
            return;
        }

        loader.classList.add('is-leaving');
        window.setTimeout(function () {
            loader.classList.add('is-hidden');
        }, 750);
    }

    if (reduced) {
        hideLoader();
    } else if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader, { once: true });
    }

    function applyReveal(nodes, staggerMs) {
        Array.prototype.forEach.call(nodes, function (el, index) {
            if (!el || el.classList.contains('pm-reveal') || el.classList.contains('pm-heading')) {
                return;
            }
            el.classList.add('pm-reveal');
            if (staggerMs) {
                el.style.setProperty('--pm-delay', (index * staggerMs) + 'ms');
            }
        });
    }

    function initReveals() {
        var page = document.querySelector('.page-content');
        if (!page) {
            return;
        }

        applyReveal(page.querySelectorAll('.hero-text-wrapper'), 0);
        applyReveal(page.querySelectorAll('section > .container > .row > [class*="col-"] > .text-wrapper'), 0);

        page.querySelectorAll('.inner-row').forEach(function (row) {
            applyReveal(row.children, 90);
        });

        page.querySelectorAll('.practice-points').forEach(function (group) {
            applyReveal(group.children, 80);
        });

        page.querySelectorAll('.booking-steps').forEach(function (group) {
            applyReveal(group.children, 80);
        });

        page.querySelectorAll('.provider-details').forEach(function (group) {
            applyReveal(group.children, 70);
        });

        applyReveal(page.querySelectorAll(
            '.why-one-practice .bg-wrapper, ' +
            '.see-us-from-home .text-wrapper, .see-us-from-home .inline-btn-wrapper, ' +
            '.meet-the-provider .bg-wrapper, .meet-the-provider .text-wrapper, .meet-the-provider .inline-btn-wrapper, ' +
            '.welcoming-new-patients .column-wrapper, .welcoming-new-patients .map-wrapper, ' +
            '.getting-started .starting-steps, .cta-sec .inline-btn-wrapper, .cta-sec .text-wrapper'
        ), 0);

        var headings = page.querySelectorAll('h1, h2, h3');
        headings.forEach(function (heading) {
            heading.classList.add('pm-heading');
            var siblings = heading.parentElement ? heading.parentElement.querySelectorAll('h1.pm-heading, h2.pm-heading, h3.pm-heading') : [];
            var siblingIndex = Array.prototype.indexOf.call(siblings, heading);
            heading.style.setProperty('--pm-delay', Math.max(siblingIndex, 0) * 60 + 'ms');
        });

        if (reduced) {
            page.querySelectorAll('.pm-reveal, .pm-heading').forEach(function (el) {
                el.classList.add('is-inview');
            });
            return;
        }

        page.querySelectorAll('.owl-carousel-wrapper .pm-reveal, .owl-carousel-wrapper .pm-heading').forEach(function (el) {
            el.classList.add('is-inview');
        });

        if (!('IntersectionObserver' in window)) {
            page.querySelectorAll('.pm-reveal, .pm-heading').forEach(function (el) {
                el.classList.add('is-inview');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-inview');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.16,
            rootMargin: '0px 0px -10% 0px'
        });

        page.querySelectorAll('.pm-reveal, .pm-heading').forEach(function (el) {
            observer.observe(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReveals, { once: true });
    } else {
        initReveals();
    }
})();


