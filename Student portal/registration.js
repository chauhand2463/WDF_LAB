function error(id, msg) {
  document.getElementById(id).innerHTML = msg;
  return false;
}

function clearErrors() {
  let ids = ["fnameError", "lnameError", "contactError", "emailError", "genderError", "addressError", "cityError", "stateError", "countryError", "pincodeError", "imageError"];
  for (let i = 0; i < ids.length; i++) document.getElementById(ids[i]).innerHTML = "";
}

function validateRegistration() {
  clearErrors();
  let fname = document.getElementById("fname").value;
  let lname = document.getElementById("lname").value;
  let contact = document.getElementById("contact").value;
  let email = document.getElementById("email").value;
  let address = document.getElementById("address").value;
  let city = document.getElementById("city").value;
  let state = document.getElementById("state").value;
  let country = document.getElementById("country").value;
  let pincode = document.getElementById("pincode").value;
  let gender = document.querySelector('input[name="gender"]:checked');

  if (!/^[A-Za-z]{2,20}$/.test(fname)) return error("fnameError", "Enter a valid first name");
  if (!/^[A-Za-z]{2,20}$/.test(lname)) return error("lnameError", "Enter a valid last name");
  if (!/^[6-9]\d{9}$/.test(contact)) return error("contactError", "Enter a 10 digit mobile number");
  if (!/^\w+@\w+\.\w+$/.test(email)) return error("emailError", "Enter a valid email");
  if (!gender) return error("genderError", "Select a gender");
  if (address.length < 5) return error("addressError", "Enter a valid address");
  if (!/^[A-Za-z ]{2,30}$/.test(city)) return error("cityError", "Enter a valid city");
  if (!/^[A-Za-z ]{2,30}$/.test(state)) return error("stateError", "Enter a valid state");
  if (!/^[A-Za-z ]{2,30}$/.test(country)) return error("countryError", "Enter a valid country");
  if (!/^\d{6}$/.test(pincode)) return error("pincodeError", "Enter a 6 digit pincode");

  let file = document.getElementById("image").files[0];
  if (file) {
    if (!/\.(jpg|jpeg|png)$/i.test(file.name)) return error("imageError", "Only .jpg, .jpeg, .png allowed");
    if (file.size > 20 * 1024) return error("imageError", "Image must be less than 20 KB");
  }

  alert("Registration Successful!");
  window.location.href = "login.html";
  return true;
}