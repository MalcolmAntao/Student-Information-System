document.getElementById('login-form').addEventListener('submit', function(event) 
{
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Basic email validation (you can expand this)
    if (!email || !password) {
        alert("Please fill in both email and password fields.");
        event.preventDefault(); // Prevent form submission
        return;
    }

    // Additional client-side validation for specific roles (optional)
    const studentDomain = "@dbcegoa.ac.in";
    const teacherDomain = "@teacher.com";
    const adminDomain = "@admin.com";

    if (!(email.endsWith(studentDomain) || email.endsWith(teacherDomain) || email.endsWith(adminDomain))) {
        alert("Please enter a valid email (student, teacher, or admin).");
        event.preventDefault(); // Prevent form submission
    }
});
