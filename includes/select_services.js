document.addEventListener("DOMContentLoaded", function () {
    // Get form and apttype_id value
    const form = document.getElementById("appointmentForm");
    const aptTypeId = document.getElementById("apttype_id").value;

    // Set correct form action based on apttype_id
    if (aptTypeId == "1") {
        form.action = "/FurCareHub/appointment/daycare/store.php";
    } else if (aptTypeId == "2") {
        form.action = "/FurCareHub/appointment/hotel/store.php";
    }
});