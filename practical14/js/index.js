
// Restrained page-load reveals for dashboard content.
function initReveals() {
  const revealTargets = document.querySelectorAll(
    "main .reveal, header .reveal, #notification, .metric, .panel, .section, .page-header"
  );

  revealTargets.forEach((el, index) => {
    if (!el.classList.contains("reveal")) {
      el.classList.add("reveal");
    }
    el.style.animationDelay = `${0.08 + index * 0.11}s`;
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initReveals();

  // Mobile sidebar toggle
  const sidebar = document.getElementById("sidebar");
  const closeSidebar = document.getElementById("closeSidebar");
  const openSidebar = document.getElementById("openSidebar");

  if (openSidebar && sidebar) {
    openSidebar.addEventListener("click", function () {
      sidebar.classList.toggle("open");
    });
  }

  if (closeSidebar && sidebar) {
    closeSidebar.addEventListener("click", function () {
      sidebar.classList.remove("open");
    });
  }

  // Close sidebar when clicking a navigation link
  document.querySelectorAll(".sidebar-nav a").forEach(function (link) {
    link.addEventListener("click", function () {
      if (sidebar) {
        sidebar.classList.remove("open");
      }
    });
  });

  // Theme toggle (dark / light) via CSS variables
  const themeBtn = document.getElementById("themeBtn");
  const savedTheme = localStorage.getItem("theme");

  function applyTheme(theme) {
    if (theme === "light") {
      document.body.classList.remove("dark");
      document.body.classList.add("light");
      if (themeBtn) themeBtn.innerHTML = "🌙 <span>Dark Mode</span>";
      localStorage.setItem("theme", "light");
    } else {
      document.body.classList.remove("light");
      document.body.classList.add("dark");
      if (themeBtn) themeBtn.innerHTML = "☀️ <span>Light Mode</span>";
      localStorage.setItem("theme", "dark");
    }
  }

  if (savedTheme) {
    applyTheme(savedTheme);
  } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
    applyTheme("dark");
  } else {
    applyTheme("light");
  }

  if (themeBtn) {
    themeBtn.addEventListener("click", function () {
      const isDark = document.body.classList.contains("dark");
      applyTheme(isDark ? "light" : "dark");
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

  // Dismiss notification
  const notification = document.getElementById("notification");
  const closeNotification = document.getElementById("closeNotification");

  if (closeNotification && notification) {
    closeNotification.addEventListener("click", function () {
      notification.style.display = "none";
    });
  }
});
