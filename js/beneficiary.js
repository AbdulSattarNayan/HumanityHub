// beneficiary.js

console.log("beneficiary.js loaded");

const aidForm = document.getElementById('aidForm');
if (aidForm) {
    console.log("aidForm found");
    // No event listener needed for direct submission
} else {
    console.error("aidForm not found on the page");
}

// Check for a success message in the URL query parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');

    if (status && message) {
        console.log("Showing pop-up with status:", status, "and message:", message);
        const popupMessage = document.getElementById('popupMessage');
        popupMessage.textContent = message;

        // Show the pop-up and overlay
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('popup').style.display = 'block';

        // If the submission was successful, reset the form
        if (status === 'success') {
            document.getElementById('aidForm').reset();
        }

        // Clear the URL parameters to prevent the pop-up from showing on refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function closePopup() {
    // Hide the pop-up and overlay
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}