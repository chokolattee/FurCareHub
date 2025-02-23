document.addEventListener("DOMContentLoaded", function () {
    function updateCheckoutDateTime() {
        const checkInDate = document.getElementById('check_in_date').value;
        const checkInTime = document.getElementById('check_in_time').value;

        if (!checkInDate || !checkInTime) return;

        const durationSelect = document.getElementById('duration_id');
        const selectedOption = durationSelect.options[durationSelect.selectedIndex];
        const duration = parseFloat(selectedOption.getAttribute('data-duration')) || 0;

        let totalHours = duration;

        const checkInDateTime = new Date(`${checkInDate}T${checkInTime}`);
        checkInDateTime.setHours(checkInDateTime.getHours() + totalHours);

        const formattedCheckOutDate = checkInDateTime.toISOString().split('T')[0];
        const formattedCheckOutTime = checkInDateTime.toTimeString().split(' ')[0].substring(0, 5);

        document.getElementById('check_out_date').value = formattedCheckOutDate;
        document.getElementById('check_out_time').value = formattedCheckOutTime;

        document.getElementById('check_out_date').setAttribute("min", checkInDate);

        updateAdditionalDaysAndHours();
    }

    function updateAdditionalDaysAndHours() {
        const checkInDate = document.getElementById('check_in_date').value;
        const checkInTime = document.getElementById('check_in_time').value;
        const checkOutDate = document.getElementById('check_out_date').value;
        const checkOutTime = document.getElementById('check_out_time').value;

        if (!checkInDate || !checkInTime || !checkOutDate || !checkOutTime) return;

        const checkInDateTime = new Date(`${checkInDate}T${checkInTime}`);
        const checkOutDateTime = new Date(`${checkOutDate}T${checkOutTime}`);

        const diffInMs = checkOutDateTime - checkInDateTime;
        const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));

        const additionalDays = Math.floor(diffInHours / 24);
        const additionalHours = diffInHours % 24;

        const additionalDaysText = `${additionalDays} ${additionalDays === 1 ? 'day' : 'days'}`;
        const additionalHoursText = `${additionalHours} ${additionalHours === 1 ? 'hour' : 'hours'}`;
        const concatenatedText = `${additionalDaysText}, ${additionalHoursText}`;

        document.getElementById("additional_days").value = concatenatedText;
        document.getElementById("hidden_additional_days").value = additionalDays;
        document.getElementById("hidden_additional_hours").value = additionalHours;
    }

    function validateCheckoutDate() {
        const checkInDate = document.getElementById('check_in_date').value;
        const checkOutDate = document.getElementById('check_out_date').value;

        if (checkOutDate < checkInDate) {
            alert("Check-Out Date cannot be before Check-In Date!");
            document.getElementById('check_out_date').value = checkInDate;
            updateCheckoutDateTime();
        }
    }

    function validateCheckoutTime() {
        updateAdditionalDaysAndHours();
    }

    // Attach event listeners
    document.getElementById('check_in_date').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('check_in_time').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('duration_id').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('check_out_date').addEventListener('change', validateCheckoutDate);
    document.getElementById('check_out_time').addEventListener('change', validateCheckoutTime);
});
