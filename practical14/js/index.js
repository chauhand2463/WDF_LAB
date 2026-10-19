
// Staggered page-load reveals for the dashboard hero content.
function initReveals() {
  const revealTargets = document.querySelectorAll(
    "main > .page-title-wrap, main > .page-intro, main > .top-buttons, main > .dashboard-grid, main > .slider-section, main > .faq-section, main > section, header .header-inner, #notification"
  );

  revealTargets.forEach((el, index) => {
    el.classList.add("reveal");
    el.style.animationDelay = `${0.08 + index * 0.11}s`;
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initReveals();

  // User id from localStorage (fallback label)
  let userID = localStorage.getItem("userID");
  const userIDEl = document.getElementById("userID");
  if (userIDEl) {
    userIDEl.textContent = userID ? userID : "Student";
  }

  // Mobile hamburger menu
  const menuBtn = document.getElementById("menuBtn");
  const navbar = document.getElementById("navbar");

  if (menuBtn && navbar) {
    menuBtn.addEventListener("click", function () {
      const active = navbar.classList.toggle("active");
      menuBtn.setAttribute("aria-expanded", active === true);
    });

    // Close menu when a link is tapped
    navbar.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        navbar.classList.remove("active");
        menuBtn.setAttribute("aria-expanded", "false");
      });
    });
  }

  // Notification dismiss
  const notification = document.getElementById("notification");
  const closeNotification = document.getElementById("closeNotification");

  if (closeNotification) {
    closeNotification.addEventListener("click", function () {
      if (notification) {
        notification.style.display = "none";
      }
    });
  }

  // Theme toggle (dark / light) with CSS variables
  const themeBtn = document.getElementById("themeBtn");
  const savedTheme = localStorage.getItem("theme");

  function applyTheme(theme) {
    if (theme === "light") {
      document.body.classList.remove("dark");
      document.body.classList.add("light");
      if (themeBtn) themeBtn.textContent = "☀️ Light Mode";
      localStorage.setItem("theme", "light");
    } else {
      document.body.classList.remove("light");
      document.body.classList.add("dark");
      if (themeBtn) themeBtn.textContent = "☀️ Light Mode";
      localStorage.setItem("theme", "dark");
    }
  }

  if (savedTheme) {
    applyTheme(savedTheme);
  } else {
    // Default to dark (the signature aesthetic)
    applyTheme("dark");
  }

  if (themeBtn) {
    themeBtn.addEventListener("click", function () {
      const isDark = document.body.classList.contains("dark");
      applyTheme(isDark ? "light" : "dark");
    });
  }

  // Modal popup
  const modal = document.getElementById("modal");
  const openModal = document.getElementById("openModal");
  const closeModal = document.getElementById("closeModal");

  if (openModal && modal) {
    openModal.addEventListener("click", function () {
      modal.style.display = "flex";
    });
  }

  if (closeModal && modal) {
    closeModal.addEventListener("click", function () {
      modal.style.display = "none";
    });
  }

  if (modal) {
    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  }

  // Announcement slider
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
    if (!slideTitle || !slideText) return;
    slideTitle.textContent = slides[currentSlide].title;
    slideText.textContent = slides[currentSlide].text;
  }

  const nextBtn = document.getElementById("nextBtn");
  const prevBtn = document.getElementById("prevBtn");

  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      currentSlide++;
      if (currentSlide >= slides.length) {
        currentSlide = 0;
      }
      showSlide();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      currentSlide--;
      if (currentSlide < 0) {
        currentSlide = slides.length - 1;
      }
      showSlide();
    });
  }

  // FAQ accordions
  const faqQuestions = document.querySelectorAll(".faq-question");

  faqQuestions.forEach(function (question) {
    question.addEventListener("click", function () {
      const answer = question.nextElementSibling;
      const icon = question.querySelector("span:last-child");

      if (answer && answer.style.maxHeight) {
        answer.style.maxHeight = null;
        if (icon) icon.textContent = "+";
      } else {
        if (answer) {
          answer.style.maxHeight = answer.scrollHeight + "px";
        }
        if (icon) icon.textContent = "−";
      }
    });
  });
});
