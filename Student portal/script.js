window.addEventListener("load", function () {
  let q = window.location.search;
  if (q.indexOf("data=") >= 0) {
    let row = decodeURIComponent(q.split("data=")[1]).split("|");
    document.getElementById("showName").textContent = row[2];
    document.getElementById("showId").textContent = row[0];
    document.getElementById("showPassword").textContent = row[1];
  } else {
    let showError = document.getElementById("showError");
    showError.style.display = "block";
    showError.textContent = "You are not logged in. Please login first.";
  }
});