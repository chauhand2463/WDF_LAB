
document.addEventListener("DOMContentLoaded", function () {



    const loginForm = document.getElementById("loginForm");



    loginForm.addEventListener("submit", function (event) {

        event.preventDefault();



        const studentID = 
    document.getElementById("studentID").value.trim();

        const idCheck = /^[0-9]{2}[A-Z]{3}[0-9]{3}$/;

        if (!idCheck.test(studentID)) {
        alert("Please enter a valid ID");
        return;
        }

        const password = 
    document.getElementById("studentpass").value.trim();
        



        if (studentID === "") {

            alert("Please enter your Student ID.");

            return;
        }



        if (password === "") {

            alert("Please enter your password.");

            return;
        }



        if (password.length < 6) {

            alert("Password must contain at least 6 characters.");

            return;
        }



        localStorage.setItem("userID", studentID);



        alert("Login Successful!");



        window.location.href = "index.html";

    });

});