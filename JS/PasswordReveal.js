const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const toggleIcon = document.getElementById("toggleIcon");

togglePassword.addEventListener("mousedown", function () {
    // Reveal the password
    passwordInput.type = "text";
    toggleIcon.setAttribute("name", "eye-outline");
});

togglePassword.addEventListener("mouseup", function () {
    // Hide the password when mouse is released
    passwordInput.type = "password";
    toggleIcon.setAttribute("name", "eye-off-outline");
});

togglePassword.addEventListener("mouseleave", function () {
    // Hide the password if mouse leaves the button
    passwordInput.type = "password";
    toggleIcon.setAttribute("name", "eye-off-outline");
});