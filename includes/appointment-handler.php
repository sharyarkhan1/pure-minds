<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$bookingServices = [
    'psychiatric' => [
        'label' => 'Psychiatric Health',
        'website_label' => 'Behavioral & Mental Health',
        'description' => 'Support for mood, anxiety, focus, sleep, and trauma — delivered with privacy and compassion.',
        'subservices' => [
            'Medication Management',
            'Depression & Anxiety',
            'ADHD Evaluation',
            'PTSD Treatment',
            'Bipolar (Disorder) Treatment',
            'Detox Services',
        ],
    ],
    'medical' => [
        'label' => 'Medical Health',
        'website_label' => 'Primary & Medical Care',
        'description' => 'A regular doctor for the whole family — checkups, same-day urgent care, and chronic condition management.',
        'subservices' => [
            'Primary Care',
            'Urgent Care',
            'Family Medicine',
            'Chronic Care',
        ],
    ],
];

function appointment_load_env(): array
{
    static $values;
    if ($values !== null) {
        return $values;
    }

    $values = [];
    $envPath = dirname(__DIR__) . '/.env';
    if (!is_readable($envPath)) {
        return $values;
    }

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && $value[0] === $value[strlen($value) - 1] && in_array($value[0], ['"', "'"], true)) {
            $value = substr($value, 1, -1);
        }
        $values[trim($key)] = $value;
    }

    return $values;
}

function appointment_env(string $key, string $default = ''): string
{
    $env = appointment_load_env();
    $serverValue = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    return (string) ($serverValue !== null && $serverValue !== '' ? $serverValue : ($env[$key] ?? $default));
}

function appointment_database(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = appointment_env('DB_DSN');
    if ($dsn === '') {
        $host = appointment_env('DB_HOST', '127.0.0.1');
        $port = appointment_env('DB_PORT', '3306');
        $name = appointment_env('DB_NAME', 'pure_minds');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    }

    $pdo = new PDO($dsn, appointment_env('DB_USERNAME', 'root'), appointment_env('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function appointment_csrf_token(): string
{
    if (empty($_SESSION['appointment_csrf'])) {
        $_SESSION['appointment_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['appointment_csrf'];
}

function appointment_clean(string $value, int $maxLength): string
{
    $value = trim($value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
}

function appointment_slots_for_date(string $date): array
{
    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $today = new DateTimeImmutable('today');
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date || $dateObject < $today) {
        return [];
    }

    $statement = appointment_database()->prepare(
        "SELECT id, TIME_FORMAT(slot_time, '%H:%i') AS slot_time
         FROM appointment_slots
                 WHERE slot_date = :slot_date AND status = 'open'
                     AND (slot_date > CURRENT_DATE OR (slot_date = CURRENT_DATE AND slot_time > CURRENT_TIME))
         ORDER BY slot_time"
    );
    $statement->execute(['slot_date' => $date]);
    return $statement->fetchAll();
}

function appointment_send_emails(array $appointment): void
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (is_readable($autoload)) {
        require_once $autoload;
    } else {
        $manualPath = dirname(__DIR__) . '/PHPMailer/src/';
        foreach (['Exception.php', 'PHPMailer.php', 'SMTP.php'] as $file) {
            $manualFile = $manualPath . $file;
            if (!is_readable($manualFile)) {
                throw new RuntimeException('PHPMailer is not installed. Add it with Composer or place its src folder in /PHPMailer.');
            }
            require_once $manualFile;
        }
    }

    if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
        throw new RuntimeException('Email service is not available.');
    }

    $mailConfig = [
        'host' => appointment_env('SMTP_HOST'),
        'port' => (int) appointment_env('SMTP_PORT', '587'),
        'encryption' => appointment_env('SMTP_ENCRYPTION', 'tls'),
        'username' => appointment_env('SMTP_USERNAME'),
        'password' => appointment_env('SMTP_PASSWORD'),
        'from_email' => appointment_env('SMTP_FROM_EMAIL'),
        'from_name' => appointment_env('SMTP_FROM_NAME', 'Pure Minds'),
        'notification_email' => appointment_env('APPOINTMENT_NOTIFICATION_EMAIL'),
    ];
    if ($mailConfig['host'] === '' || $mailConfig['username'] === '' || $mailConfig['password'] === '' || $mailConfig['from_email'] === '' || $mailConfig['notification_email'] === '') {
        throw new RuntimeException('Email service is not configured.');
    }

    $safe = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $details = '<p><strong>Service Type:</strong> ' . $safe($appointment['service_label']) . '</p>'
        . '<p><strong>Sub-service:</strong> ' . $safe($appointment['subservice']) . '</p>'
        . '<p><strong>Date:</strong> ' . $safe($appointment['date_display']) . '</p>'
        . '<p><strong>Time:</strong> ' . $safe($appointment['time_display']) . '</p>'
        . '<p><strong>Name:</strong> ' . $safe($appointment['name']) . '</p>'
        . '<p><strong>Phone:</strong> ' . $safe($appointment['phone']) . '</p>'
        . '<p><strong>Email:</strong> ' . $safe($appointment['email']) . '</p>'
        . '<p><strong>Notes:</strong> ' . nl2br($safe($appointment['notes'] ?: 'None provided')) . '</p>'
        . '<p><strong>Submitted:</strong> ' . $safe($appointment['submitted']) . '</p>';

    $send = static function (string $to, string $subject, string $body, ?string $replyTo = null) use ($mailConfig): void {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $mailConfig['host'];
        $mail->Port = $mailConfig['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'] === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($to);
        if ($replyTo !== null) {
            $mail->addReplyTo($replyTo);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['</p>', '<br>'], ["\n", "\n"], $body));
        $mail->send();
    };

    $send(
        $mailConfig['notification_email'],
        'New Appointment Booking — Pure Minds',
        '<h2>New Appointment Booking</h2>' . $details,
        $appointment['email']
    );
    $send(
        $appointment['email'],
        'Appointment Request Received — Pure Minds',
        '<h2>Appointment Request Received</h2>'
            . '<p>Hello ' . $safe($appointment['name']) . ',</p>'
            . '<p>Thank you for requesting an appointment with Pure Minds. Your request has been received and a member of our team will contact you to confirm the appointment details.</p>'
            . '<p><strong>Service:</strong> ' . $safe($appointment['service_label']) . '<br>'
            . '<strong>Sub-service:</strong> ' . $safe($appointment['subservice']) . '<br>'
            . '<strong>Requested date:</strong> ' . $safe($appointment['date_display']) . '<br>'
            . '<strong>Requested time:</strong> ' . $safe($appointment['time_display']) . '</p>'
    );
}

function appointment_handle_submission(array $services): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return [];
    }

    $failure = static fn(string $message, int $step = 1): array => ['error' => $message, 'step' => $step];
    if (!hash_equals((string) ($_SESSION['appointment_csrf'] ?? ''), (string) ($_POST['csrf_token'] ?? ''))) {
        return $failure('Your session has expired. Please refresh the page and try again.');
    }
    if (appointment_clean((string) ($_POST['website'] ?? ''), 100) !== '') {
        return $failure('We could not process this request.');
    }
    if (!empty($_SESSION['appointment_submitted_at']) && time() - (int) $_SESSION['appointment_submitted_at'] < 10) {
        return $failure('Please wait a few seconds before submitting another request.', 5);
    }

    $service = appointment_clean((string) ($_POST['service_type'] ?? ''), 30);
    $subservice = appointment_clean((string) ($_POST['subservice'] ?? ''), 100);
    $date = appointment_clean((string) ($_POST['appointment_date'] ?? ''), 10);
    $time = appointment_clean((string) ($_POST['appointment_time'] ?? ''), 5);
    $name = appointment_clean((string) ($_POST['name'] ?? ''), 120);
    $phone = appointment_clean((string) ($_POST['phone'] ?? ''), 40);
    $email = appointment_clean((string) ($_POST['email'] ?? ''), 254);
    $notes = appointment_clean((string) ($_POST['notes'] ?? ''), 2000);

    if (!isset($services[$service]) || !in_array($subservice, $services[$service]['subservices'], true)) {
        return $failure('Please select a valid service and sub-service.', 2);
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) {
        return $failure('Please select a valid appointment date.', 3);
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        return $failure('Please select an available appointment time.', 3);
    }
    if ($name === '' || strlen($name) < 2) {
        return $failure('Please enter your name.', 4);
    }
    if ($phone === '' || !preg_match('/^[0-9+().\-\s]{7,40}$/', $phone)) {
        return $failure('Please enter a valid phone number.', 4);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $failure('Please enter a valid email address.', 4);
    }

    try {
        $pdo = appointment_database();
        $pdo->beginTransaction();
        $slot = $pdo->prepare(
            "UPDATE appointment_slots SET status = 'reserved', reserved_at = NOW()
                         WHERE slot_date = :slot_date AND slot_time = :slot_time AND status = 'open'
                             AND (slot_date > CURRENT_DATE OR (slot_date = CURRENT_DATE AND slot_time > CURRENT_TIME))"
        );
        $slot->execute(['slot_date' => $date, 'slot_time' => $time . ':00']);
        if ($slot->rowCount() !== 1) {
            $pdo->rollBack();
            return ['slot_unavailable' => true, 'step' => 3, 'date' => $date];
        }

        $insert = $pdo->prepare(
            'INSERT INTO appointments (service_type, subservice, appointment_date, appointment_time, name, phone, email, notes, status)
             VALUES (:service_type, :subservice, :appointment_date, :appointment_time, :name, :phone, :email, :notes, :status)'
        );
        $insert->execute([
            'service_type' => $service,
            'subservice' => $subservice,
            'appointment_date' => $date,
            'appointment_time' => $time . ':00',
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'notes' => $notes,
            'status' => 'requested',
        ]);
        $appointment = [
            'service_label' => $services[$service]['label'],
            'subservice' => $subservice,
            'date_display' => (new DateTimeImmutable($date))->format('F j, Y'),
            'time_display' => DateTimeImmutable::createFromFormat('H:i', $time)->format('g:i A'),
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'notes' => $notes,
            'submitted' => (new DateTimeImmutable())->format('F j, Y g:i A T'),
        ];
        $pdo->commit();
        $_SESSION['appointment_submitted_at'] = time();
        try {
            appointment_send_emails($appointment);
        } catch (Throwable $emailException) {
            error_log('Appointment notification failed: ' . $emailException->getMessage());
        }
        return ['success' => true];
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Appointment submission failed: ' . $exception->getMessage());
        return $failure('We could not submit your request right now. Please try again later.', 5);
    }
}

if (isset($_GET['availability'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $date = appointment_clean((string) ($_GET['date'] ?? ''), 10);
        echo json_encode(['slots' => appointment_slots_for_date($date)]);
    } catch (Throwable $exception) {
        error_log('Availability request failed: ' . $exception->getMessage());
        http_response_code(503);
        echo json_encode(['error' => 'Availability is temporarily unavailable.']);
    }
    exit;
}
