document.addEventListener("DOMContentLoaded", function () {
    function toggleAdditionalHoursSection() {
        const durationSelect = document.getElementById("duration_id");
        const additionalHoursSection = document.getElementById("additional_hours_section");

        // Get the selected duration value
        const selectedDuration = durationSelect.options[durationSelect.selectedIndex].getAttribute("data-duration");

        // Show the additional hours section only if 10 hours is selected
        if (selectedDuration === "10") {
            additionalHoursSection.style.display = "block";
        } else {
            additionalHoursSection.style.display = "none";
            // Reset the checkbox and input if another duration is selected
            document.getElementById("add_additional_hours").checked = false;
            document.getElementById("additional_hours").style.display = "none";
        }
    }

    function toggleAdditionalHoursInput() {
        const additionalHoursInput = document.getElementById("additional_hours");
        const addAdditionalHoursCheckbox = document.getElementById("add_additional_hours");

        // Show the additional hours input if the checkbox is checked
        if (addAdditionalHoursCheckbox.checked) {
            additionalHoursInput.style.display = "block";
        } else {
            additionalHoursInput.style.display = "none";
        }
    }

    function updateCheckoutDateTime() {
        // Get the check-in date and time
        const checkInDate = document.getElementById('check_in_date').value;
        const checkInTime = document.getElementById('check_in_time').value;

        // Get the selected duration and additional hours
        const durationSelect = document.getElementById('duration_id');
        const selectedOption = durationSelect.options[durationSelect.selectedIndex];
        const duration = parseFloat(selectedOption.getAttribute('data-duration')) || 0;
        const additionalHours = parseFloat(document.getElementById('additional_hours').value) || 0;
        const totalHours = duration + additionalHours;

        // Combine check-in date and time into a single datetime string
        const checkInDateTime = `${checkInDate}T${checkInTime}`;

        // Calculate checkout datetime
        const checkOutDateTime = new Date(checkInDateTime);
        checkOutDateTime.setHours(checkOutDateTime.getHours() + totalHours);

        // Format the checkout date and time for display
        const formattedCheckOutDate = checkOutDateTime.toISOString().split('T')[0];
        const formattedCheckOutTime = checkOutDateTime.toTimeString().split(' ')[0];

        // Update the checkout date and time fields
        document.getElementById('check_out_date').value = formattedCheckOutDate;
        document.getElementById('check_out_time').value = formattedCheckOutTime;
    }

    // Attach event listeners
    document.getElementById('check_in_date').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('check_in_time').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('duration_id').addEventListener('change', updateCheckoutDateTime);
    document.getElementById('additional_hours').addEventListener('input', updateCheckoutDateTime);
});
