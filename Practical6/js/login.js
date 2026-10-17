



document.addEventListener("DOMContentLoaded", function () {



    const loginForm =
        document.getElementById("loginForm");


    if (!loginForm) {

        console.error("Login form not found.");

        return;

    }



    loginForm.addEventListener("submit", function (event) {


        event.preventDefault();



        const studentID =
            document.getElementById("studentID").value.trim();


        const password =
            document.getElementById("studentpass").value;



        if (studentID === "") {

            alert("Please enter your Student ID.");

            return;

        }



        const idCheck =
            /^[0-9]{2}[A-Z]{3}[0-9]{3}$/;


        if (!idCheck.test(studentID)) {

            alert(
                "Please enter a valid Student ID. Example: 24CE001"
            );

            return;

        }



        if (password === "") {

            alert("Please enter your password.");

            return;

        }



        if (password.length < 6) {

            alert(
                "Password must contain at least 6 characters."
            );

            return;

        }



        localStorage.setItem(
            "userID",
            studentID
        );



        alert("Login Successful!");



        window.location.href = "index.html";


    });

});