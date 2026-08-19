document.getElementById("fname").addEventListener("input", function () {
    let fname = this.value;
    let error = document.getElementById("fnameError");
    let pattern = /^[A-Za-z ]+$/;

    if (fname === "") {
        error.innerHTML = "First name is required";
    } else if (!pattern.test(fname)) {
        error.innerHTML = "Only characters are allowed";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("lname").addEventListener("input", function () {
    let lname = this.value;
    let error = document.getElementById("lnameError");
    let pattern = /^[A-Za-z ]+$/;

    if (lname === "") {
        error.innerHTML = "Last name is required";
    } else if (!pattern.test(lname)) {
        error.innerHTML = "Only characters are allowed";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("contact").addEventListener("input", function () {
    let contact = this.value;
    let error = document.getElementById("contactError");

    if (!/^[0-9]*$/.test(contact)) {
        error.innerHTML = "Only numbers are allowed";
    } else if (contact.length > 0 && contact.length < 10) {
        error.innerHTML = "Contact number must contain 10 digits";
    } else if (contact.length === 10) {
        error.innerHTML = "";
    } else {
        error.innerHTML = "Contact number is required";
    }
});

document.getElementById("email").addEventListener("input", function () {
    let email = this.value;
    let error = document.getElementById("emailError");
    let pattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.com$/;

    if (email === "") {
        error.innerHTML = "Email is required";
    } else if (!email.includes("@")) {
        error.innerHTML = "Email must contain @";
    } else if (!email.endsWith(".com")) {
        error.innerHTML = "Email must end with .com";
    } else if (!pattern.test(email)) {
        error.innerHTML = "Enter a valid email address";
    } else {
        error.innerHTML = "";
    }
});

let genderButtons = document.querySelectorAll('input[name="gender"]');

genderButtons.forEach(function (button) {
    button.addEventListener("change", function () {
        document.getElementById("genderError").innerHTML = "";
    });
});

document.getElementById("address").addEventListener("input", function () {
    let address = this.value;
    let error = document.getElementById("addressError");

    if (address.trim() === "") {
        error.innerHTML = "Address is required";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("city").addEventListener("input", function () {
    let city = this.value;
    let error = document.getElementById("cityError");
    let pattern = /^[A-Za-z ]+$/;

    if (city === "") {
        error.innerHTML = "City is required";
    } else if (!pattern.test(city)) {
        error.innerHTML = "City must contain only characters";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("state").addEventListener("input", function () {
    let state = this.value;
    let error = document.getElementById("stateError");
    let pattern = /^[A-Za-z ]+$/;

    if (state === "") {
        error.innerHTML = "State is required";
    } else if (!pattern.test(state)) {
        error.innerHTML = "State must contain only characters";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("country").addEventListener("input", function () {
    let country = this.value;
    let error = document.getElementById("countryError");
    let pattern = /^[A-Za-z ]+$/;

    if (country === "") {
        error.innerHTML = "Country is required";
    } else if (!pattern.test(country)) {
        error.innerHTML = "Country must contain only characters";
    } else {
        error.innerHTML = "";
    }
});

document.getElementById("pincode").addEventListener("input", function () {
    let pincode = this.value;
    let error = document.getElementById("pincodeError");

    if (!/^[0-9]*$/.test(pincode)) {
        error.innerHTML = "Only numbers are allowed";
    } else if (pincode.length > 0 && pincode.length < 6) {
        error.innerHTML = "Pincode must contain 6 digits";
    } else if (pincode.length === 6) {
        error.innerHTML = "";
    } else {
        error.innerHTML = "Pincode is required";
    }
});

document.getElementById("image").addEventListener("change", function () {
    let image = this.files[0];
    let error = document.getElementById("imageError");

    if (!image) {
        error.innerHTML = "Please select an image";
        return;
    }

    let maxSize = 20 * 1024;

    if (image.size > maxSize) {
        error.innerHTML = "Image size must not exceed 20 KB";
        return;
    }

    let allowedTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png"
    ];

    if (!allowedTypes.includes(image.type)) {
        error.innerHTML = "Only JPG, JPEG and PNG images are allowed";
        return;
    }

    error.innerHTML = "";
});

document.getElementById("registrationForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let valid = true;

    let fname = document.getElementById("fname").value.trim();
    let lname = document.getElementById("lname").value.trim();
    let contact = document.getElementById("contact").value.trim();
    let email = document.getElementById("email").value.trim();
    let address = document.getElementById("address").value.trim();
    let city = document.getElementById("city").value.trim();
    let state = document.getElementById("state").value.trim();
    let country = document.getElementById("country").value.trim();
    let pincode = document.getElementById("pincode").value.trim();
    let gender = document.querySelector('input[name="gender"]:checked');
    let image = document.getElementById("image").files[0];

    if (fname === "") {
        document.getElementById("fnameError").innerHTML = "First name is required";
        valid = false;
    }

    if (lname === "") {
        document.getElementById("lnameError").innerHTML = "Last name is required";
        valid = false;
    }

    if (contact.length !== 10) {
        document.getElementById("contactError").innerHTML = "Contact number must contain 10 digits";
        valid = false;
    }

    if (email === "" || !email.endsWith(".com")) {
        document.getElementById("emailError").innerHTML = "Enter a valid .com email";
        valid = false;
    }

    if (!gender) {
        document.getElementById("genderError").innerHTML = "Please select gender";
        valid = false;
    }

    if (address === "") {
        document.getElementById("addressError").innerHTML = "Address is required";
        valid = false;
    }

    if (city === "") {
        document.getElementById("cityError").innerHTML = "City is required";
        valid = false;
    }

    if (state === "") {
        document.getElementById("stateError").innerHTML = "State is required";
        valid = false;
    }

    if (country === "") {
        document.getElementById("countryError").innerHTML = "Country is required";
        valid = false;
    }

    if (pincode.length !== 6) {
        document.getElementById("pincodeError").innerHTML = "Pincode must contain 6 digits";
        valid = false;
    }

    if (!image) {
        document.getElementById("imageError").innerHTML = "Please select an image";
        valid = false;
    }

    if (valid) {
        alert("Registration successful!");
    }
});