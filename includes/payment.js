document.addEventListener("DOMContentLoaded", function () {
    var paymentType = document.getElementById("payment_type");
    if (paymentType) {
        paymentType.addEventListener("change", toggleReferenceField);
        toggleReferenceField(); // Ensure correct state on page load
    }
});

function toggleReferenceField() {
    var paymentType = document.getElementById("payment_type").value;
    var cashlessFields = document.getElementById("cashless-fields");

    if (paymentType === "2") {
        cashlessFields.style.display = "block";
    } else {
        cashlessFields.style.display = "none";
    }
}
