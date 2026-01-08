// Get modal elements
const modal = document.getElementById("addCostModal");
const btn = document.getElementById("addCostBtn");
const closeBtn = document.querySelector(".close");

// Show the modal when button is clicked
btn.addEventListener("click", () => {
    modal.style.display = "block";
});

// Hide modal when close button is clicked
closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

// Hide modal when clicking outside of the modal
window.addEventListener("click", (event) => {
    if (event.target === modal) {
        modal.style.display = "none";
    }
});