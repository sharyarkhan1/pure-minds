<?php
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/admin-auth.php';
admin_require_auth();

$message = '';
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!admin_verify_csrf()) {
        $error = 'Your session has expired. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        try {
            $pdo = appointment_database();
            if ($action === 'add_slot') {
                $date = trim((string) ($_POST['slot_date'] ?? ''));
                $time = trim((string) ($_POST['slot_time'] ?? ''));
                $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                if (!$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d') || !preg_match('/^\d{2}:\d{2}$/', $time)) {
                    throw new InvalidArgumentException('Choose a valid future date and time.');
                }
                $statement = $pdo->prepare('INSERT INTO appointment_slots (slot_date, slot_time, status) VALUES (:slot_date, :slot_time, \'open\')');
                $statement->execute(['slot_date' => $date, 'slot_time' => $time . ':00']);
                $message = 'The appointment slot is now open for booking.';
            } elseif (in_array($action, ['block_slot', 'open_slot', 'delete_slot'], true)) {
                $slotId = filter_var($_POST['slot_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$slotId) {
                    throw new InvalidArgumentException('The selected slot is invalid.');
                }
                if ($action === 'block_slot') {
                    $statement = $pdo->prepare("UPDATE appointment_slots SET status = 'blocked' WHERE id = :id AND status = 'open'");
                    $statement->execute(['id' => $slotId]);
                    $message = 'The slot has been blocked.';
                } elseif ($action === 'open_slot') {
                    $statement = $pdo->prepare("UPDATE appointment_slots SET status = 'open', reserved_at = NULL WHERE id = :id AND status = 'blocked'");
                    $statement->execute(['id' => $slotId]);
                    $message = 'The slot is open again.';
                } else {
                    $statement = $pdo->prepare("DELETE FROM appointment_slots WHERE id = :id AND status = 'open'");
                    $statement->execute(['id' => $slotId]);
                    $message = 'The open slot was removed.';
                }
            }
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        } catch (PDOException $exception) {
            $error = ((int) $exception->errorInfo[1] === 1062)
                ? 'That date and time already exists.'
                : 'The slot could not be updated right now.';
            error_log('Admin slot update failed: ' . $exception->getMessage());
        } catch (Throwable $exception) {
            $error = 'The slot could not be updated right now.';
            error_log('Admin portal failed: ' . $exception->getMessage());
        }
    }
}

try {
    $pdo = appointment_database();
    $slots = $pdo->query("SELECT id, slot_date, slot_time, status FROM appointment_slots WHERE slot_date >= CURRENT_DATE ORDER BY slot_date, slot_time LIMIT 250")->fetchAll();
    $appointments = $pdo->query("SELECT service_type, subservice, appointment_date, appointment_time, name, email, status FROM appointments ORDER BY appointment_date DESC, appointment_time DESC LIMIT 100")->fetchAll();
} catch (Throwable $exception) {
    $slots = [];
    $appointments = [];
    $error = 'The portal could not load data right now.';
    error_log('Admin portal load failed: ' . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Admin | Pure Minds</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-portal">
<header class="admin-topbar"><a href="admin" class="admin-brand"><img src="images/logo.png" alt="Pure Minds"> <strong>Pure Minds Admin</strong></a><div><span><?php echo htmlspecialchars((string) $_SESSION['admin_email']); ?></span> &nbsp; <a href="admin-logout">Log out</a></div></header>
<main class="admin-content">
    <p class="admin-eyebrow">APPOINTMENT MANAGEMENT</p>
    <h1>Available appointment slots</h1>
    <p class="admin-muted">Add only the times your team can currently offer. Patients will see open slots automatically.</p>
    <?php if ($message !== ''): ?><div class="admin-success" role="status"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="admin-alert" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="admin-grid">
        <section class="admin-card" aria-labelledby="add-slot-title">
            <h2 id="add-slot-title">Add an open time</h2>
            <form method="post" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(admin_csrf_token()); ?>">
                <input type="hidden" name="action" value="add_slot">
                <label for="slotDate">Date</label>
                <input type="date" id="slotDate" name="slot_date" min="<?php echo date('Y-m-d'); ?>" required>
                <label for="slotTime">Time</label>
                <select id="slotTime" name="slot_time" required>
                    <option value="">Select a time</option>
                    <?php for ($hour = 0; $hour < 24; $hour++): ?>
                        <?php foreach ([0, 30] as $minute): ?>
                            <?php $timeValue = sprintf('%02d:%02d', $hour, $minute); ?>
                            <?php $timeLabel = (new DateTimeImmutable('2000-01-01 ' . $timeValue))->format('g:i A'); ?>
                            <option value="<?php echo $timeValue; ?>"><?php echo $timeLabel; ?></option>
                        <?php endforeach; ?>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-wth-bg">Add available slot</button>
            </form>
        </section>
        <section class="admin-card" aria-labelledby="slots-title">
            <h2 id="slots-title">Upcoming slots</h2>
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead><tbody>
            <?php if (!$slots): ?><tr><td colspan="4">No upcoming slots have been added.</td></tr><?php endif; ?>
            <?php foreach ($slots as $slot): ?><tr><td><?php echo htmlspecialchars((new DateTimeImmutable($slot['slot_date']))->format('M j, Y')); ?></td><td><?php echo htmlspecialchars(DateTimeImmutable::createFromFormat('H:i:s', $slot['slot_time'])->format('g:i A')); ?></td><td><span class="status status-<?php echo htmlspecialchars($slot['status']); ?>"><?php echo htmlspecialchars($slot['status']); ?></span></td><td><?php if ($slot['status'] === 'open'): ?><form method="post" class="admin-inline-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(admin_csrf_token()); ?>"><input type="hidden" name="action" value="block_slot"><input type="hidden" name="slot_id" value="<?php echo (int) $slot['id']; ?>"><button class="admin-link-button" type="submit">Block</button></form> <form method="post" class="admin-inline-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(admin_csrf_token()); ?>"><input type="hidden" name="action" value="delete_slot"><input type="hidden" name="slot_id" value="<?php echo (int) $slot['id']; ?>"><button class="admin-link-button" type="submit">Remove</button></form><?php elseif ($slot['status'] === 'blocked'): ?><form method="post" class="admin-inline-form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(admin_csrf_token()); ?>"><input type="hidden" name="action" value="open_slot"><input type="hidden" name="slot_id" value="<?php echo (int) $slot['id']; ?>"><button class="admin-link-button" type="submit">Open</button></form><?php else: ?>Reserved<?php endif; ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </section>
    </div>
    <section class="admin-card" style="margin-top:24px" aria-labelledby="requests-title"><h2 id="requests-title">Appointment requests</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Date</th><th>Patient</th><th>Service</th><th>Email</th><th>Status</th></tr></thead><tbody><?php if (!$appointments): ?><tr><td colspan="5">No appointment requests yet.</td></tr><?php endif; ?><?php foreach ($appointments as $appointment): ?><tr><td><?php echo htmlspecialchars((new DateTimeImmutable($appointment['appointment_date']))->format('M j, Y') . ' ' . DateTimeImmutable::createFromFormat('H:i:s', $appointment['appointment_time'])->format('g:i A')); ?></td><td><?php echo htmlspecialchars($appointment['name']); ?></td><td><?php echo htmlspecialchars($appointment['subservice']); ?></td><td><?php echo htmlspecialchars($appointment['email']); ?></td><td><?php echo htmlspecialchars($appointment['status']); ?></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
</body>
</html>
