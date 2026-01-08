document.addEventListener("DOMContentLoaded", function () {
    const signupForm = document.getElementById("signupForm");
    
    if (signupForm) {
        signupForm.addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent form from refreshing the page

            let username = document.getElementById("username").value;
            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirmPassword").value;
            let userType = document.getElementById("userType").value;
            let messageBox = document.getElementById("signupMessage");

            if (password !== confirmPassword) {
                messageBox.innerText = "Passwords do not match!";
                messageBox.style.color = "red";
                return;
            }

            messageBox.innerText = `Sign-up successful as ${userType}!`;
            messageBox.style.color = "green";
        });
    }
});
function submitSignup(event) {
    event.preventDefault(); // Prevents form from refreshing the page
    alert("Signup successful!");
    window.location.href = "login.html"; // Redirects to login page
}

 document.getElementById("signupForm").addEventListener("submit", submitSignup);

 document.getElementById("signupForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("signup.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let message = document.getElementById("signupMessage");
        message.textContent = data.message;
        message.style.color = data.status === "success" ? "green" : "red";

        if (data.status === "success") {
            setTimeout(() => {
                window.location.href = "login.html";
            }, 2000);
        }
    })
    .catch(error => console.error("Error:", error));
});

    