navbar = document.querySelector(".header .flex .navbar");

document.querySelector("#menu-btn").onclick = () => {
  navbar.classList.toggle("active");
  profile.classList.remove("active");
};

document.addEventListener("DOMContentLoaded", function () {
  const userBtn = document.querySelector("#user-btn");
  const profile = document.querySelector(".profile");

  if (userBtn && profile) {
    userBtn.onclick = (e) => {
      e.stopPropagation();
      profile.classList.toggle("active");
    };

    document.onclick = (e) => {
      if (!profile.contains(e.target) && !userBtn.contains(e.target)) {
        profile.classList.remove("active");
      }
    };
  }
});

window.onclick = (e) => {
  if (!e.target.matches("#user-btn")) {
    if (profile.classList.contains("active")) {
      profile.classList.remove("active");
    }
  }
};

window.onscroll = () => {
  navbar.classList.remove("active");
  profile.classList.remove("active");
};

function loader() {
  document.querySelector(".loader").style.display = "none";
}

function fadeOut() {
  setInterval(loader, 2000);
}

window.onload = fadeOut;

document.querySelectorAll('input[type="number"]').forEach((numberInput) => {
  numberInput.oninput = () => {
    if (numberInput.value.length > numberInput.maxLength)
      numberInput.value = numberInput.value.slice(0, numberInput.maxLength);
  };
});
