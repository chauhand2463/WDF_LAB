
let userID = localStorage.getItem("userID");

if (userID) {
    document.getElementById("userID").textContent = userID;
} else {
    document.getElementById("userID").textContent = "Student";
}



const menuBtn = document.getElementById("menuBtn");
const navbar = document.getElementById("navbar");

menuBtn.addEventListener("click", function () {

    navbar.classList.toggle("active");

});



const notification = document.getElementById("notification");
const closeNotification = document.getElementById("closeNotification");

closeNotification.addEventListener("click", function () {

    notification.style.display = "none";

});



const themeBtn = document.getElementById("themeBtn");



let savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {

    document.body.classList.add("dark");
    themeBtn.textContent = "☀️ Light Mode";

}

themeBtn.addEventListener("click", function () {

    document.body.classList.toggle("dark");

    if (document.body.classList.contains("dark")) {

        themeBtn.textContent = "☀️ Light Mode";
        localStorage.setItem("theme", "dark");

    } else {

        themeBtn.textContent = "🌙 Dark Mode";
        localStorage.setItem("theme", "light");

    }

});

const modal = document.getElementById("modal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");

openModal.addEventListener("click", function () {

    modal.style.display = "flex";

});

closeModal.addEventListener("click", function () {

    modal.style.display = "none";

});

window.addEventListener("click", function (event) {

    if (event.target === modal) {

        modal.style.display = "none";

    }

});

const slides = [

    {
        title: "Java Exam",
        text: "Java Programming examination will be conducted on Monday."
    },

    {
        title: "Network Assignment",
        text: "Submit your Computer Network assignment before Friday."
    },

    {
        title: "WDF Practical",
        text: "Web Development Framework practical submission is next week."
    },

    {
        title: "Semester Result",
        text: "Semester results will be available on the Student HUB Portal."
    }

];


let currentSlide = 0;

const slideTitle = document.getElementById("slideTitle");
const slideText = document.getElementById("slideText");

function showSlide() {

    slideTitle.textContent = slides[currentSlide].title;

    slideText.textContent = slides[currentSlide].text;
}

document.getElementById("nextBtn").addEventListener("click", function () {

    currentSlide++;

    if (currentSlide >= slides.length) {

        currentSlide = 0;

    }

    showSlide();

});

document.getElementById("prevBtn").addEventListener("click", function () {

    currentSlide--;

    if (currentSlide < 0) {

        currentSlide = slides.length - 1;

    }

    showSlide();

});

const faqQuestions = document.querySelectorAll(".faq-question");

faqQuestions.forEach(function (question) {

    question.addEventListener("click", function () {

        const answer = question.nextElementSibling;
        const icon = question.querySelector("span:last-child");


        if (answer.style.maxHeight) {

            answer.style.maxHeight = null;

            icon.textContent = "+";

        } else {

            answer.style.maxHeight = answer.scrollHeight + "px";

            icon.textContent = "−";

        }

    });

});
