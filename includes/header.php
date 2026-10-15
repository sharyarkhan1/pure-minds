<?php
    if (!isset($site_name)) {
        include_once __DIR__ . '/config.php';
    }
?>
<header class="main-header">
    <div class="main-header-wrapper sticky-header">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-md-2 col-8">
                    <div class="header-logo">
                        <a href="<?php echo htmlspecialchars($site_url . '/'); ?>">
                            <img src="images/logo.png?v=4"
                                 alt="<?php echo htmlspecialchars($site_name); ?>">
                        </a>
                    </div>
                </div>
                <!-- Desktop Navigation -->
                <div class="col-md-6 desktop-header-nav">
                    <nav class="header-nav" aria-label="Primary">
                        <div class="ul nav-menu">
                            <li><a href="<?php echo htmlspecialchars($site_url . '/'); ?>">home</a></li>
                            <li><a href="<?php echo htmlspecialchars($site_url . '/#about-us'); ?>">about us</a></li>
                            <li><a href="<?php echo htmlspecialchars($site_url . '/#medical-care'); ?>">medical care</a></li>
                            <li><a href="<?php echo htmlspecialchars($site_url . '/#services'); ?>">our services</a></li>
                            <li><a href="<?php echo htmlspecialchars($site_url . '/#contact-us'); ?>">contact us</a></li>
                        </div>
                    </nav>
                </div>
                <!-- Desktop Buttons -->
                <div class="col-md-4 desktop-header-btns">
                    <div class="inline-header-btns">
                        <div class="button-wrapper">
                            <a href="tel:(614) 555-0123" class="btn-without-bg">
                                <i class="fa-solid fa-phone"></i>
                                (614) 555-0123
                            </a>
                        </div>
                        <div class="button-wrapper">
                            <a href="<?php echo htmlspecialchars($site_url . '/book-an-appointment'); ?>" class="btn-wth-bg">
                                Book an Appointment
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Mobile / Tablet Menu Toggle -->
                <div class="col-md-10 col-4 mobile-menu-toggle-wrapper">
                    <button type="button"
                            class="mobile-menu-toggle"
                            id="mobileMenuToggle"
                            aria-label="Open Menu"
                            aria-controls="mobileOffcanvas"
                            aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Off Canvas Overlay -->
    <div class="offcanvas-overlay" id="offcanvasOverlay"></div>

    <!-- Mobile / Tablet Off Canvas -->
    <div class="mobile-offcanvas"
         id="mobileOffcanvas"
         aria-hidden="true">
        <!-- Off Canvas Header -->
        <div class="mobile-offcanvas-header">
            <div class="mobile-offcanvas-logo">
                <a href="<?php echo htmlspecialchars($site_url . '/'); ?>">
                    <img src="images/logo.png?v=4"
                         alt="<?php echo htmlspecialchars($site_name); ?>">
                </a>
            </div>
            <button type="button"
                    class="mobile-menu-close"
                    id="mobileMenuClose"
                    aria-label="Close Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Off Canvas Navigation -->
        <nav class="mobile-offcanvas-nav" aria-label="Mobile">
            <ul class="mobile-nav-menu">
                <li>
                    <a href="<?php echo htmlspecialchars($site_url . '/'); ?>">home</a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($site_url . '/#about-us'); ?>">about us</a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($site_url . '/#medical-care'); ?>">medical care</a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($site_url . '/#services'); ?>">our services</a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($site_url . '/#contact-us'); ?>">contact us</a>
                </li>
            </ul>
        </nav>

        <!-- Off Canvas Buttons -->
        <div class="mobile-offcanvas-buttons">
            <div class="mobile-button-wrapper">
                <a href="tel:(614) 555-0123" class="btn-without-bg">
                    <i class="fa-solid fa-phone"></i>
                    (614) 555-0123
                </a>
            </div>
            <div class="mobile-button-wrapper">
                <a href="<?php echo htmlspecialchars($site_url . '/book-an-appointment'); ?>" class="btn-wth-bg">
                    Book an Appointment
                </a>
            </div>
        </div>
    </div>
</header>