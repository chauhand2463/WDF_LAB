document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");

    if (!form) {

        return;

    }

    form.addEventListener("submit", function (event) {

        const studentidInput = document.getElementById("studentid");
        const fullname = document.getElementById("fullname").value.trim();
        const email = document.getElementById("email").value.trim();
        const mobile = document.getElementById("mobile").value.trim();
        const course = document.getElementById("course").value;
        const password = document.getElementById("password").value;
        const confirm = document.getElementById("confirm").value;

        const studentid = studentidInput.value.trim().toUpperCase();

        if (!/^[0-9]{2}[A-Z]{2,3}[0-9]{3}$/.test(studentid)) {

            event.preventDefault();
            alert("Student ID must be like 25CE015.");
            studentidInput.focus();
            return;

        }

        studentidInput.value = studentid;

        if (!/^[A-Za-z ]{3,}$/.test(fullname)) {

            event.preventDefault();
            alert("Please enter a valid name.");
            return;

        }

        if (!/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email)) {

            event.preventDefault();
            alert("Please enter a valid email address.");
            return;

        }

        if (!/^[0-9]{10}$/.test(mobile)) {

            event.preventDefault();
            alert("Mobile number must contain exactly 10 digits.");
            return;

        }

        if (course === "") {

            event.preventDefault();
            alert("Please select a course.");
            return;

        }

        const passwordPattern =
            /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;

        if (!passwordPattern.test(password)) {

            event.preventDefault();
            alert(
                "Password must contain 8 characters, uppercase, lowercase, number and special character."
            );
            return;

        }

        if (password !== confirm) {

            event.preventDefault();
            alert("Password and Confirm Password do not match.");
            return;

        }

    });

});
