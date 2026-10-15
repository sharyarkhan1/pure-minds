(function () {
    var form = document.getElementById('appointmentForm');
    if (!form) return;

    var steps = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
    var progress = Array.prototype.slice.call(document.querySelectorAll('.progress-step'));
    var currentStep = Number(form.dataset.initialStep || 1);
    var services = JSON.parse(document.getElementById('bookingServices').textContent);
    var selectedService = form.querySelector('[name="service_type"]');
    var selectedSubservice = form.querySelector('[name="subservice"]');
    var dateInput = form.querySelector('[name="appointment_date"]');
    var timeInput = form.querySelector('[name="appointment_time"]');
    var subserviceOptions = document.getElementById('subserviceOptions');
    var timeOptions = document.getElementById('timeOptions');
    var timeStatus = document.getElementById('timeStatus');
    var dateNow = new Date();
    dateNow.setMinutes(dateNow.getMinutes() - dateNow.getTimezoneOffset());
    dateInput.min = dateNow.toISOString().slice(0, 10);

    function showStep(step) {
        currentStep = step;
        steps.forEach(function (item) { item.hidden = Number(item.dataset.step) !== step; });
        progress.forEach(function (item, index) {
            item.classList.toggle('is-active', index + 1 === step);
            item.classList.toggle('is-complete', index + 1 < step);
            var number = item.querySelector('.progress-number');
            if (index + 1 < step) number.textContent = '✓';
            else number.textContent = String(index + 1);
        });
        window.scrollTo({ top: document.querySelector('.booking-shell').offsetTop - 30, behavior: 'smooth' });
    }

    function setError(message) {
        var error = document.getElementById('bookingError');
        error.textContent = message || '';
        error.hidden = !message;
    }

    function renderSubservices() {
        var service = services[selectedService.value];
        subserviceOptions.innerHTML = '';
        selectedSubservice.value = '';
        if (!service) return;
        service.subservices.forEach(function (name) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'booking-option';
            button.dataset.value = name;
            button.setAttribute('aria-pressed', 'false');
            button.innerHTML = '<span>' + name.replace(/&/g, '&amp;') + '</span><span class="booking-option__check" aria-hidden="true">✓</span>';
            button.addEventListener('click', function () {
                Array.prototype.forEach.call(subserviceOptions.children, function (option) { option.classList.remove('is-selected'); });
                button.classList.add('is-selected');
                button.setAttribute('aria-pressed', 'true');
                selectedSubservice.value = name;
                setError('');
            });
            subserviceOptions.appendChild(button);
        });
    }

    function loadSlots() {
        timeInput.value = '';
        timeOptions.innerHTML = '';
        if (!dateInput.value) {
            timeStatus.textContent = 'Select a date to see open times.';
            return;
        }
        timeStatus.textContent = 'Loading available times...';
        fetch('availability?availability=1&date=' + encodeURIComponent(dateInput.value), { headers: { Accept: 'application/json' } })
            .then(function (response) { if (!response.ok) throw new Error(); return response.json(); })
            .then(function (data) {
                timeOptions.innerHTML = '';
                if (!data.slots || !data.slots.length) {
                    timeStatus.textContent = 'There are no open times on this date. Please choose another date.';
                    return;
                }
                timeStatus.textContent = 'Choose an open time.';
                data.slots.forEach(function (slot) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'time-option';
                    button.dataset.value = slot.slot_time;
                    button.setAttribute('aria-pressed', 'false');
                    var parts = slot.slot_time.split(':');
                    var hour = Number(parts[0]);
                    var suffix = hour >= 12 ? 'PM' : 'AM';
                    button.textContent = ((hour % 12) || 12) + ':' + parts[1] + ' ' + suffix;
                    button.addEventListener('click', function () {
                        Array.prototype.forEach.call(timeOptions.children, function (item) { item.classList.remove('is-selected'); });
                        button.classList.add('is-selected');
                        button.setAttribute('aria-pressed', 'true');
                        timeInput.value = slot.slot_time;
                        setError('');
                    });
                    timeOptions.appendChild(button);
                });
            })
            .catch(function () { timeStatus.textContent = 'Availability is temporarily unavailable. Please try again.'; });
    }

    function updateReview() {
        var values = {
            service: services[selectedService.value] ? services[selectedService.value].label : '',
            subservice: selectedSubservice.value,
            date: dateInput.value ? new Date(dateInput.value + 'T00:00:00').toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' }) : '',
            time: timeInput.value,
            name: form.querySelector('[name="name"]').value,
            phone: form.querySelector('[name="phone"]').value,
            email: form.querySelector('[name="email"]').value,
            notes: form.querySelector('[name="notes"]').value || 'None provided'
        };
        Object.keys(values).forEach(function (key) {
            var target = document.querySelector('[data-review="' + key + '"]');
            if (target) target.textContent = values[key];
        });
    }

    function validateStep(step) {
        if (step === 1 && !selectedService.value) return 'Please select a service type.';
        if (step === 2 && !selectedSubservice.value) return 'Please select a sub-service.';
        if (step === 3 && (!dateInput.value || !timeInput.value)) return 'Please select an available date and time.';
        if (step === 4) {
            var name = form.querySelector('[name="name"]');
            var phone = form.querySelector('[name="phone"]');
            var email = form.querySelector('[name="email"]');
            if (!name.checkValidity()) return 'Please enter your name.';
            if (!phone.checkValidity()) return 'Please enter a valid phone number.';
            if (!email.checkValidity()) return 'Please enter a valid email address.';
        }
        return '';
    }

    form.addEventListener('click', function (event) {
        var next = event.target.closest('[data-next]');
        var back = event.target.closest('[data-back]');
        if (next) {
            var nextError = validateStep(currentStep);
            if (nextError) { setError(nextError); return; }
            setError('');
            if (currentStep === 4) updateReview();
            showStep(Math.min(5, currentStep + 1));
        }
        if (back) { setError(''); showStep(Math.max(1, currentStep - 1)); }
    });
    form.querySelectorAll('.js-service-option').forEach(function (button) {
        button.addEventListener('click', function () {
            form.querySelectorAll('.js-service-option').forEach(function (item) { item.classList.remove('is-selected'); });
            button.closest('.booking-option').classList.add('is-selected');
            selectedService.value = button.value;
            button.setAttribute('aria-pressed', 'true');
            renderSubservices();
            setError('');
        });
    });
    dateInput.addEventListener('change', loadSlots);
    form.addEventListener('submit', function (event) {
        if (currentStep !== 5) { event.preventDefault(); return; }
        var submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        submit.textContent = 'Requesting...';
    });
    showStep(Math.min(5, Math.max(1, currentStep)));
    if (selectedService.value) {
        var serviceButton = form.querySelector('.js-service-option[value="' + selectedService.value + '"]');
        if (serviceButton) serviceButton.closest('.booking-option').classList.add('is-selected');
        renderSubservices();
        var subButton = subserviceOptions.querySelector('[data-value="' + selectedSubservice.value.replace(/"/g, '\\"') + '"]');
        if (subButton) subButton.classList.add('is-selected');
    }
    if (dateInput.value) loadSlots();
})();
