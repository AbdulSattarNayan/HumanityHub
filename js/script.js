// script.js

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded: Initializing page scripts");

    // Highlight active menu link
    console.log("Highlighting active menu link");
    let links = document.querySelectorAll(".menu a");
    let currentPage = window.location.pathname.split("/").pop();

    links.forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });

    // Show volunteer login form if present
    console.log("Checking for loginForm");
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        showVolunteerLogin();
    } else {
        console.log("loginForm not found on this page, skipping showVolunteerLogin");
    }
});

function registerEvent() {
    // Show a success message without redirecting
    document.getElementById("volunteerMessage").innerText = "You have successfully registered for the event!";
}

function showVolunteerLogin() {
    console.log("showVolunteerLogin called");
    // No need to check for loginForm again since we already did in the event listener
    document.getElementById("loginForm").style.display = "block";
}

function toggleEditForm() {
    console.log("toggleEditForm called");

    const form = document.getElementById("editForm");
    const infoTable = document.querySelector(".info-table");
    const editButton = document.querySelector(".edit-button");

    if (!form) {
        console.error("Form element (#editForm) not found");
    }
    if (!infoTable) {
        console.error("Info table (.info-table) not found");
    }
    if (!editButton) {
        console.error("Edit button (.edit-button) not found");
    }

    if (!form || !infoTable || !editButton) {
        console.error("One or more elements not found, cannot proceed with toggleEditForm");
        return;
    }

    if (form.style.display === "none" || form.style.display === "") {
        console.log("Showing form, hiding table");
        form.style.display = "block";
        infoTable.style.display = "none";
        editButton.textContent = "Hide Form";
    } else {
        console.log("Hiding form, showing table");
        form.style.display = "none";
        infoTable.style.display = "table";
        editButton.textContent = "Edit";
    }
}

function generateCertificate() {
    console.log("generateCertificate called");
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(20);
    doc.text("Volunteer Certificate", 105, 20, { align: "center" });
    doc.setFontSize(14);
    doc.text("This certifies that", 105, 40, { align: "center" });
    doc.setFontSize(16);
    doc.text("<?php echo htmlspecialchars($volunteer['name']); ?>", 105, 50, { align: "center" });
    doc.setFontSize(14);
    doc.text("has contributed to Humanity Hub as a volunteer.", 105, 60, { align: "center" });
    doc.text("Volunteer ID: <?php echo htmlspecialchars($volunteer['volunteer_id']); ?>", 105, 70, { align: "center" });
    doc.text("Date: <?php echo date('Y-m-d'); ?>", 105, 80, { align: "center" });

    doc.save("Volunteer_Certificate_<?php echo htmlspecialchars($volunteer['volunteer_id']); ?>.pdf");
}