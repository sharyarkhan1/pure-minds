<?php
if (!isset($site_name)) {
    include_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($site_keywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($site_legal_name); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($robots_meta); ?>">
    <meta name="googlebot" content="<?php echo htmlspecialchars($robots_meta); ?>">
    <meta name="bingbot" content="<?php echo htmlspecialchars($robots_meta); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($page_canonical); ?>">

    <meta property="og:type" content="<?php echo htmlspecialchars($page_og_type); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($page_canonical); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($page_og_image); ?>">
    <meta property="og:locale" content="en_US">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($page_og_image); ?>">

    <meta name="geo.region" content="US-OH">
    <meta name="geo.placename" content="Pickerington">
    <meta name="theme-color" content="#001C5D">
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="images/logo.png">
    <!-- Stylesheet -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <?php if (!empty($appointment_page)): ?>
    <link rel="stylesheet" href="css/appointment.css">
    <?php endif; ?>

    <!-- Fontawesome library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css" integrity="sha512-x9WwyMYBnlXMNQ6kQ/Lyzu1NqIhLQKL5Oq6xByfXuRj7s9CskyCbLv/1IjqzJmXwFXWr0ov6jBV7Qbc0hh9nHg==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap" rel="stylesheet">    
    
    <!-- Owl Carousel Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script>
        (function () {
            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            document.documentElement.classList.add(reduced ? 'pm-reduced-motion' : 'pm-motion');
            if (!reduced) {
                document.documentElement.classList.add('pm-loading');
            }
            window.setTimeout(function () {
                document.documentElement.classList.remove('pm-loading');
                var loader = document.getElementById('pmPageLoader');
                if (loader && !loader.classList.contains('is-hidden')) {
                    loader.classList.add('is-leaving');
                    loader.classList.add('is-hidden');
                }
            }, 15000);
        })();
    </script>
    <style>
        html.pm-loading {
            overflow: hidden;
        }
        .pm-page-loader {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #F8F8F8;
        }
        html.pm-reduced-motion .pm-page-loader {
            display: none;
        }
    </style>
    <noscript>
        <style>
            html.pm-loading { overflow: auto; }
            .pm-page-loader { display: none !important; }
        </style>
    </noscript>
    <script type="application/ld+json">
<?php
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'MedicalClinic',
    'name' => $site_legal_name,
    'alternateName' => $site_name,
    'url' => $site_url . '/',
    'image' => $page_og_image,
    'description' => $page_description,
    'telephone' => $site_phone,
    'faxNumber' => $site_fax,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $site_street,
        'addressLocality' => $site_city,
        'addressRegion' => $site_region,
        'postalCode' => $site_postal,
        'addressCountry' => $site_country,
    ],
    'areaServed' => [
        '@type' => 'AdministrativeArea',
        'name' => 'Ohio',
    ],
    'medicalSpecialty' => [
        'Psychiatry',
        'Primary Care',
        'Family Medicine',
    ],
    'openingHoursSpecification' => [
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '08:00',
            'closes' => '17:00',
        ],
    ],
];
echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
    </script>
</head>
<body>
<div class="pm-page-loader" id="pmPageLoader" role="status" aria-live="polite" aria-label="Loading">
    <div class="pm-page-loader__inner">
        <div class="pm-page-loader__mark">
            <svg class="pm-page-loader__ring" viewBox="0 0 120 120" aria-hidden="true" focusable="false">
                <circle class="pm-page-loader__track" cx="60" cy="60" r="52"></circle>
                <circle class="pm-page-loader__progress" cx="60" cy="60" r="52"></circle>
            </svg>
            <img src="images/logo.png?v=4"
                 alt="<?php echo htmlspecialchars($site_name); ?>"
                 class="pm-page-loader__logo">
        </div>
        <div class="pm-page-loader__bar" aria-hidden="true">
            <span></span>
        </div>
    </div>
</div>
<div class="page-wrapper">