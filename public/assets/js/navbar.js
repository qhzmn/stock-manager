// manage the active class of the navbar elements

document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop() || "index.php";
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach(link => {
    const linkPage = link.getAttribute("href").split("/").pop();
    console.log(linkPage);
    exit();
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
});

