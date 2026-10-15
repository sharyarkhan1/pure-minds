<?php
include_once __DIR__ . '/includes/config.php';
$page_title = 'Book an Appointment | Pure Minds';
$page_description = 'Request an appointment with Pure Minds for psychiatric or medical health care in Pickerington, Ohio.';
$page_canonical = $site_url . '/book-an-appointment';
$appointment_page = true;
include_once __DIR__ . '/includes/appointment-handler.php';
$submission = appointment_handle_submission($bookingServices);
$initialStep = (int) ($submission['step'] ?? 1);
$selectedService = (string) ($_POST['service_type'] ?? '');
$selectedSubservice = (string) ($_POST['subservice'] ?? '');
$selectedDate = (string) ($_POST['appointment_date'] ?? ($submission['date'] ?? ''));
$selectedTime = (string) ($_POST['appointment_time'] ?? '');
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>
<main class="page-content appointment-page">
    <div class="container">
        <div class="appointment-intro">
            <p class="appointment-eyebrow">PURE MINDS CARE</p>
            <h1>Book an Appointment</h1>
            <p>Choose the care that fits your needs, then request a time that works for you.</p>
        </div>
        <div class="booking-shell">
            <nav class="booking-progress" aria-label="Appointment booking progress">
                <?php foreach (['Service', 'Sub-service', 'Date & Time', 'Contact', 'Review'] as $index => $label): ?>
                    <div class="progress-step" data-progress="<?php echo $index + 1; ?>">
                        <span class="progress-number"><?php echo $index + 1; ?></span>
                        <span class="progress-label" data-mobile-label="<?php echo htmlspecialchars($index === 1 ? 'Sub' : ($index === 2 ? 'Date' : $label)); ?>"><?php echo htmlspecialchars($label); ?></span>
                    </div>
                <?php endforeach; ?>
            </nav>
            <?php if (!empty($submission['success'])): ?>
                <div class="booking-success" role="status">
                    <h2>Appointment Request Received</h2>
                    <p>Thank you for requesting an appointment with Pure Minds. Your appointment request has been received. A member of our team will contact you to confirm your appointment details.</p>
                </div>
            <?php else: ?>
                <form method="post" class="booking-form" id="appointmentForm" data-initial-step="<?php echo $initialStep; ?>" novalidate>
                    <?php if (!empty($submission['error'])): ?>
                        <div class="booking-alert booking-alert--error" id="bookingError" role="alert"><?php echo htmlspecialchars($submission['error']); ?></div>
                    <?php else: ?>
                        <div class="booking-alert booking-alert--error" id="bookingError" role="alert" hidden></div>
                    <?php endif; ?>
                    <?php if (!empty($submission['slot_unavailable'])): ?>
                        <div class="booking-alert booking-alert--error" role="alert"><strong>This Time Slot Is No Longer Available</strong><br>The selected appointment time was just taken. Please select another available time.</div>
                    <?php endif; ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(appointment_csrf_token()); ?>">
                    <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($selectedService); ?>">
                    <input type="hidden" name="subservice" value="<?php echo htmlspecialchars($selectedSubservice); ?>">
                    <input type="hidden" name="appointment_time" value="<?php echo htmlspecialchars($selectedTime); ?>">
                    <label class="visually-hidden-field" for="website">Website</label>
                    <input class="visually-hidden-field" id="website" name="website" tabindex="-1" autocomplete="off">

                    <section class="booking-step" data-step="1" aria-labelledby="step-one-title">
                        <h2 id="step-one-title">What kind of care do you need?</h2>
                        <p class="step-copy">Start by choosing the service type that best matches your needs.</p>
                        <div class="selection-grid selection-grid--services">
                            <?php foreach ($bookingServices as $key => $service): ?>
                                <button type="button" class="booking-option booking-option--service js-service-option" value="<?php echo htmlspecialchars($key); ?>" aria-pressed="<?php echo $selectedService === $key ? 'true' : 'false'; ?>">
                                    <span class="booking-option__check" aria-hidden="true">✓</span>
                                    <h3><?php echo htmlspecialchars($service['label']); ?></h3>
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="booking-actions"><span></span><button type="button" class="btn-wth-bg" data-next>Continue</button></div>
                    </section>

                    <section class="booking-step" data-step="2" aria-labelledby="step-two-title" hidden>
                        <h2 id="step-two-title">Choose a sub-service</h2>
                        <p class="step-copy">Select the option that best describes the appointment you are requesting.</p>
                        <div class="options-list" id="subserviceOptions"></div>
                        <div class="booking-actions"><button type="button" class="btn-back" data-back>Back</button><button type="button" class="btn-wth-bg" data-next>Continue</button></div>
                    </section>

                    <section class="booking-step" data-step="3" aria-labelledby="step-three-title" hidden>
                        <h2 id="step-three-title">Find a time that works</h2>
                        <p class="step-copy">Select a date to load only currently open appointment times.</p>
                        <div class="date-time-grid">
                            <div class="form-field"><label for="appointmentDate">Date <span class="required" aria-hidden="true">*</span></label><input type="date" id="appointmentDate" name="appointment_date" value="<?php echo htmlspecialchars($selectedDate); ?>" required></div>
                            <div class="form-field"><label for="appointmentTimeDisplay">Time <span class="required" aria-hidden="true">*</span></label><div class="time-status" id="timeStatus" aria-live="polite">Select a date to see open times.</div><div class="time-options" id="timeOptions" role="group" aria-label="Available appointment times"></div><input type="hidden" id="appointmentTimeDisplay" aria-hidden="true"></div>
                        </div>
                        <div class="booking-actions"><button type="button" class="btn-back" data-back>Back</button><button type="button" class="btn-wth-bg" data-next>Continue</button></div>
                    </section>

                    <section class="booking-step" data-step="4" aria-labelledby="step-four-title" hidden>
                        <h2 id="step-four-title">Tell us how to reach you</h2>
                        <p class="step-copy">We will use these details to follow up about your appointment request.</p>
                        <div class="form-grid">
                            <div class="form-field"><label for="name">Name <span class="required" aria-hidden="true">*</span></label><input type="text" id="name" name="name" value="<?php echo htmlspecialchars((string) ($_POST['name'] ?? '')); ?>" autocomplete="name" required minlength="2"></div>
                            <div class="form-field"><label for="phone">Phone <span class="required" aria-hidden="true">*</span></label><input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars((string) ($_POST['phone'] ?? '')); ?>" autocomplete="tel" required pattern="[0-9+().\-\s]{7,40}"></div>
                            <div class="form-field form-field--full"><label for="email">Email <span class="required" aria-hidden="true">*</span></label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars((string) ($_POST['email'] ?? '')); ?>" autocomplete="email" required></div>
                            <div class="form-field form-field--full"><label for="notes">Notes <span class="field-note" style="display:inline;">(optional)</span></label><textarea id="notes" name="notes" maxlength="2000"><?php echo htmlspecialchars((string) ($_POST['notes'] ?? '')); ?></textarea><p class="field-note">Please do not include sensitive medical or confidential health information in this field.</p></div>
                        </div>
                        <div class="booking-actions"><button type="button" class="btn-back" data-back>Back</button><button type="button" class="btn-wth-bg" data-next>Review Request</button></div>
                    </section>

                    <section class="booking-step" data-step="5" aria-labelledby="step-five-title" hidden>
                        <h2 id="step-five-title">Review Your Appointment</h2>
                        <p class="step-copy">Please check your details before sending your appointment request.</p>
                        <dl class="review-list">
                            <?php foreach (['service' => 'Service', 'subservice' => 'Sub-service', 'date' => 'Date', 'time' => 'Time', 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'notes' => 'Notes'] as $key => $label): ?><div class="review-row"><dt><?php echo $label; ?></dt><dd data-review="<?php echo $key; ?>"></dd></div><?php endforeach; ?>
                        </dl>
                        <div class="booking-actions"><button type="button" class="btn-back" data-back>Back</button><button type="submit" class="btn-wth-bg">Request Appointment</button></div>
                    </section>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
<script type="application/json" id="bookingServices"><?php echo json_encode($bookingServices, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script src="js/appointment.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
