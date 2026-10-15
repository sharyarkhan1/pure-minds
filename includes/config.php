<?php
$site_name = 'Pure Minds';
$site_legal_name = 'Pure Minds Medical and Psychiatric Health LLC';
$site_phone = '(614) 555-0123';
$site_fax = '(614) 555-0191';
$site_street = "12203 Taylor's Way";
$site_city = 'Pickerington';
$site_region = 'OH';
$site_postal = '43147';
$site_country = 'US';

$site_description = 'Pure Minds brings psychiatric care and primary medical care together in Pickerington, Ohio — in person or by telehealth. Most major insurance accepted.';
$site_keywords = 'psychiatrist Pickerington, psychiatric care Ohio, primary care Pickerington, telehealth Ohio, mental health, medication management, family medicine';

// Change to "index, follow" when the site is ready to appear in search results.
$robots_meta = 'index, follow';

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$script_dir = rtrim($script_dir, '/');
if ($script_dir === '/' || $script_dir === '\\' || $script_dir === '.') {
    $script_dir = '';
}
$site_url = ($https ? 'https' : 'http') . '://' . $host . $script_dir;
$site_url = rtrim($site_url, '/');

$default_og_image = $site_url . '/images/slide-1.png';

if (!headers_sent()) {
    header('X-Robots-Tag: ' . $robots_meta, true);
}

if (!isset($page_title)) {
    $page_title = 'Psychiatric & Primary Care in Pickerington, OH | ' . $site_name;
}
if (!isset($page_description)) {
    $page_description = $site_description;
}
if (!isset($page_canonical)) {
    $page_canonical = $site_url . '/';
}
if (!isset($page_og_image)) {
    $page_og_image = $default_og_image;
}
if (!isset($page_og_type)) {
    $page_og_type = 'website';
}

$team_members = [
    [
        'name' => 'Hannibal Aswungnjia',
        'image' => 'images/hannibal-awungnjia.png',
        'role' => 'Board-Certified Family Nurse Practitioner & Psychiatric Mental Health Nurse Practitioner',
        'description' => 'Hannibal provides compassionate psychiatric and primary medical care with a focus on treating the whole person.',
    ],
    [
        'name' => 'Team Member 2',
        'image' => null,
        'role' => 'Role to be added',
        'description' => 'Team member information will be added here.',
    ],
    [
        'name' => 'Team Member 3',
        'image' => null,
        'role' => 'Role to be added',
        'description' => 'Team member information will be added here.',
    ],
    [
        'name' => 'Team Member 4',
        'image' => null,
        'role' => 'Role to be added',
        'description' => 'Team member information will be added here.',
    ],
];