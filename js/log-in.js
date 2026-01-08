document.addEventListener("DOMContentLoaded", function () {
    // LOGIN FUNCTION
    function login() {
        let user = document.getElementById("username").value.trim();
        let pass = document.getElementById("password").value.trim();
        let loginMessage = document.getElementById("loginMessage");

        if (!user || !pass) {
            alert("Enter valid credentials.");
            return;
        }

        // Simulated login check (replace this with real backend authentication)
        if (user.toLowerCase() === "admin" && pass === "admin123") {
            loginMessage.innerText = "Admin Login successful!";
            loginMessage.style.color = "green";

            // Redirect to admin page after 1 second
            setTimeout(() => {
                window.location.href = "admin.html";
            }, 100);
        } else if (user.toLowerCase() === "volunteer" && pass === "volunteer123") {
            loginMessage.innerText = "Volunteer Login successful!";
            loginMessage.style.color = "green";

            // Redirect to volunteer page after 1 second
            setTimeout(() => {
                window.location.href = "volunteer.html";
            }, 100);
        } else {
            loginMessage.innerText = "Invalid credentials. Try again.";
            loginMessage.style.color = "red";
        }
    }

    // Attach login function to the button
    const loginButton = document.querySelector("#loginButton"); // Make sure the button has this ID
    if (loginButton) {
        loginButton.addEventListener("click", login);
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const adminLoginBtn = document.getElementById("adminLoginBtn");
    const volunteerLoginBtn = document.getElementById("volunteerLoginBtn");
    const loginButton = document.getElementById("loginButton");

    let loginType = ""; // Will store whether it's Admin or Volunteer

    if (adminLoginBtn) {
        adminLoginBtn.addEventListener("click", function () {
            loginType = "admin";
            document.getElementById("loginMessage").innerText = "Admin Login Selected";
        });
    }

    if (volunteerLoginBtn) {
        volunteerLoginBtn.addEventListener("click", function () {
            loginType = "volunteer";
            document.getElementById("loginMessage").innerText = "Volunteer Login Selected";
        });
    }

    if (loginButton) {
        loginButton.addEventListener("click", function () {
            let user = document.getElementById("username").value;
            let pass = document.getElementById("password").value;

            if (user && pass) {
                document.getElementById("loginMessage").innerText =
                    loginType ? `Login successful as ${loginType}!` : "Please select a login type.";
            } else {
                alert("Enter valid credentials.");
            }
        });
    }
});
