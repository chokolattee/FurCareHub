document.addEventListener("DOMContentLoaded", function () {
    var paymentType = document.getElementById("payment_type");
    var cashlessChoice = document.getElementById("cashless_choice");

    if (paymentType) {
        paymentType.addEventListener("change", function () {
            toggleReferenceField();
        });
        toggleReferenceField(); // Ensure correct state on page load
    }

    if (cashlessChoice) {
        cashlessChoice.addEventListener("change", function () {
            toggleCashlessOption();
        });
        toggleCashlessOption(); // Ensure correct state on page load
    }
});

function toggleReferenceField() {
    var paymentType = document.getElementById("payment_type").value;
    var cashlessOptions = document.getElementById("cashless-options");

    if (paymentType === "2") {
        cashlessOptions.classList.remove("hidden");
    } else {
        cashlessOptions.classList.add("hidden");
    }
}

function toggleCashlessOption() {
    var cashlessChoice = document.getElementById("cashless_choice").value;
    var gcashFields = document.getElementById("gcash-fields");

    if (cashlessChoice === "gcash") {
        gcashFields.classList.remove("hidden");
    } else {
        gcashFields.classList.add("hidden");
    }
}
