// Client-side validation before form submission
document.getElementById("donationForm").addEventListener("submit", function(event) {
    let name = document.getElementById("donorName").value;
    let amount = document.getElementById("donationAmount").value;

    if (!name || !amount || amount <= 0) {
        event.preventDefault(); // Prevent submission if validation fails
        alert("Please fill out all fields correctly and ensure the donation amount is greater than 0.");
    }
});

// Function to close the popup
function closePopup() {
    document.getElementById("popup").style.display = "none";
    document.getElementById("overlay").style.display = "none";
}