// ===== MENU BURGER (mobile) =====
const burger = document.getElementById("burger");
const navLinks = document.querySelector(".nav-links");

burger.addEventListener("click", () => {
  navLinks.classList.toggle("active");
});

document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => {
    navLinks.classList.remove("active");
  });
});

// ===== NAVBAR : transparente puis sombre au scroll =====
const navbar = document.getElementById("navbar");

window.addEventListener("scroll", () => {
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

// ===== VALIDATION ET ENVOI DU FORMULAIRE =====
const form = document.getElementById("contactForm");

form.addEventListener("submit", (e) => {
  e.preventDefault();

  const nomInput = document.getElementById("nom");
  const telInput = document.getElementById("tel");
  const emailInput = document.getElementById("email");
  const messageInput = document.getElementById("message");

  [nomInput, telInput, emailInput, messageInput].forEach((champ) =>
    champ.classList.remove("error"),
  );

  const nom = nomInput.value.trim();
  const tel = telInput.value.trim();
  const email = emailInput.value.trim();
  const message = messageInput.value.trim();

  let valide = true;

  if (nom === "") {
    nomInput.classList.add("error");
    valide = false;
  }
  if (tel === "") {
    telInput.classList.add("error");
    valide = false;
  }
  if (email === "") {
    emailInput.classList.add("error");
    valide = false;
  }
  if (message === "") {
    messageInput.classList.add("error");
    valide = false;
  }

  if (!valide) {
    afficherMessage("Veuillez remplir tous les champs.", "erreur");
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    emailInput.classList.add("error");
    afficherMessage("Veuillez entrer un email valide.", "erreur");
    return;
  }

  const telRegex = /^[0-9]{10,}$/;
  if (!telRegex.test(tel)) {
    telInput.classList.add("error");
    afficherMessage("Numéro de téléphone invalide.", "erreur");
    return;
  }

  const formData = new FormData();
  formData.append("nom", nom);
  formData.append("telephone", tel);
  formData.append("email", email);
  formData.append("message", message);

  fetch("traitement.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        afficherMessage(data.message, "succes");
        form.reset();
      } else {
        afficherMessage(data.message, "erreur");
      }
    })
    .catch(() => {
      afficherMessage("Une erreur est survenue. Réessayez.", "erreur");
    });
});

// Enlever le rouge dès que l'utilisateur tape (à l'extérieur du submit)
document
  .querySelectorAll("#contactForm input, #contactForm textarea")
  .forEach((champ) => {
    champ.addEventListener("input", () => {
      champ.classList.remove("error");
    });
  });

// ===== FONCTION : afficher un message =====
function afficherMessage(texte, type) {
  const ancien = document.getElementById("formMessage");
  if (ancien) ancien.remove();

  const msg = document.createElement("div");
  msg.id = "formMessage";
  msg.textContent = texte;
  msg.style.padding = "12px 16px";
  msg.style.borderRadius = "8px";
  msg.style.marginTop = "10px";
  msg.style.fontSize = "14px";
  msg.style.fontWeight = "500";

  if (type === "erreur") {
    msg.style.background = "#fdecea";
    msg.style.color = "#c0392b";
    msg.style.border = "1px solid #f5c6cb";
  } else {
    msg.style.background = "#e8f5f0";
    msg.style.color = "#1a7a5e";
    msg.style.border = "1px solid #a8d5c2";
  }

  form.appendChild(msg);

  setTimeout(() => {
    msg.remove();
  }, 4000);
}

// ===== ANIMATION : apparition au scroll =====
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      }
    });
  },
  { threshold: 0.15 },
);

document.querySelectorAll(".card, #apropos, #contact").forEach((el) => {
  el.classList.add("fade-in");
  observer.observe(el);
});

// ===== CARROUSEL SERVICES =====
const cardsTrack = document.getElementById("cardsTrack");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const dotsContainer = document.getElementById("carouselDots");
const totalCards = document.querySelectorAll("#cardsTrack .card").length;

let cardsPerView = window.innerWidth <= 768 ? 1 : 3;
let totalSlides = Math.ceil(totalCards / cardsPerView);
let currentSlide = 0;

function creerDots() {
  dotsContainer.innerHTML = "";
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement("button");
    dot.classList.add("dot");
    if (i === 0) dot.classList.add("active");
    dot.addEventListener("click", () => allerVers(i));
    dotsContainer.appendChild(dot);
  }
}

function mettreAJour() {
  const decalage = currentSlide * 100;
  cardsTrack.style.transform = `translateX(-${decalage}%)`;
  document.querySelectorAll("#carouselDots .dot").forEach((dot, i) => {
    dot.classList.toggle("active", i === currentSlide);
  });
}

function allerVers(slide) {
  currentSlide = slide;
  mettreAJour();
}
function suivant() {
  currentSlide = (currentSlide + 1) % totalSlides;
  mettreAJour();
}
function precedent() {
  currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
  mettreAJour();
}

nextBtn.addEventListener("click", suivant);
prevBtn.addEventListener("click", precedent);

let autoSlide = setInterval(suivant, 4000);

document
  .querySelector(".carousel-container")
  .addEventListener("mouseenter", () => clearInterval(autoSlide));
document
  .querySelector(".carousel-container")
  .addEventListener("mouseleave", () => {
    autoSlide = setInterval(suivant, 4000);
  });

// ===== CARROUSEL TÉMOIGNAGES =====
const temoignagesTrack = document.getElementById("temoignagesTrack");
const prevTemoign = document.getElementById("prevTemoign");
const nextTemoign = document.getElementById("nextTemoign");
const temoignagesDots = document.getElementById("temoignagesDots");
const totalTemoign = document.querySelectorAll(
  "#temoignagesTrack .temoignage",
).length;

let temoignPerView = window.innerWidth <= 768 ? 1 : 3;
let totalSlidesT = Math.ceil(totalTemoign / temoignPerView);
let currentSlideT = 0;

function creerDotsT() {
  temoignagesDots.innerHTML = "";
  for (let i = 0; i < totalSlidesT; i++) {
    const dot = document.createElement("button");
    dot.classList.add("dot");
    if (i === 0) dot.classList.add("active");
    dot.addEventListener("click", () => allerVersT(i));
    temoignagesDots.appendChild(dot);
  }
}

function mettreAJourT() {
  const decalage = currentSlideT * 100;
  temoignagesTrack.style.transform = `translateX(-${decalage}%)`;
  document.querySelectorAll("#temoignagesDots .dot").forEach((dot, i) => {
    dot.classList.toggle("active", i === currentSlideT);
  });
}

function allerVersT(slide) {
  currentSlideT = slide;
  mettreAJourT();
}
function suivantT() {
  currentSlideT = (currentSlideT + 1) % totalSlidesT;
  mettreAJourT();
}
function precedentT() {
  currentSlideT = (currentSlideT - 1 + totalSlidesT) % totalSlidesT;
  mettreAJourT();
}

nextTemoign.addEventListener("click", suivantT);
prevTemoign.addEventListener("click", precedentT);

let autoSlideT = setInterval(suivantT, 5000);

document
  .querySelector(".temoignages-container")
  .addEventListener("mouseenter", () => clearInterval(autoSlideT));
document
  .querySelector(".temoignages-container")
  .addEventListener("mouseleave", () => {
    autoSlideT = setInterval(suivantT, 5000);
  });

// ===== ADAPTATION DES CARROUSELS (UN SEUL handler pour tout) =====
function ajusterCarrousels() {
  // Carrousel services
  cardsPerView = window.innerWidth <= 768 ? 1 : 3;
  totalSlides = Math.ceil(totalCards / cardsPerView);
  currentSlide = 0;
  creerDots();
  mettreAJour();

  // Carrousel témoignages
  temoignPerView = window.innerWidth <= 768 ? 1 : 3;
  totalSlidesT = Math.ceil(totalTemoign / temoignPerView);
  currentSlideT = 0;
  creerDotsT();
  mettreAJourT();
}

window.addEventListener("resize", ajusterCarrousels);
window.addEventListener("load", ajusterCarrousels);

// Initialisation immédiate (avant le load)
creerDots();
creerDotsT();
mettreAJour();
mettreAJourT();

// ═══════════════════════════════════════════════════════
// LIGHTBOX GALERIE
// ═══════════════════════════════════════════════════════
document.addEventListener("DOMContentLoaded", function () {
  const lightbox = document.getElementById("lightbox");
  const lightboxImg = document.getElementById("lightboxImage");
  const lightboxCaption = document.getElementById("lightboxCaption");
  const lightboxCounter = document.getElementById("lightboxCounter");
  const btnClose = document.getElementById("lightboxClose");
  const btnPrev = document.getElementById("lightboxPrev");
  const btnNext = document.getElementById("lightboxNext");

  if (!lightbox) return;

  const items = document.querySelectorAll(".galerie-item");
  let currentIndex = 0;

  // Ouvrir la lightbox sur l'image cliquée
  items.forEach((item, index) => {
    item.addEventListener("click", () => {
      currentIndex = index;
      afficher(index);
      lightbox.classList.add("actif");
      document.body.style.overflow = "hidden";
    });
  });

  function afficher(index) {
    const item = items[index];
    const img = item.querySelector("img");
    const caption = item.querySelector(".galerie-overlay span");

    lightboxImg.src = img.src;
    lightboxImg.alt = img.alt || "";
    lightboxCaption.textContent = caption ? caption.textContent : "";
    lightboxCounter.textContent = `${index + 1} / ${items.length}`;
  }

  function suivantImage() {
    // au lieu de suivant
    currentIndex = (currentIndex + 1) % items.length;
    afficher(currentIndex);
  }

  function precedentImage() {
    // au lieu de precedent
    currentIndex = (currentIndex - 1 + items.length) % items.length;
    afficher(currentIndex);
  }
  function fermer() {
    lightbox.classList.remove("actif");
    document.body.style.overflow = "";
  }

  btnClose.addEventListener("click", fermer);
  btnPrev.addEventListener("click", precedentImage); // mis à jour
  btnNext.addEventListener("click", suivantImage); // mis à jour

  document.addEventListener("keydown", (e) => {
    if (!lightbox.classList.contains("actif")) return;

    if (e.key === "Escape") fermer();
    else if (e.key === "ArrowLeft")
      precedentImage(); // mis à jour
    else if (e.key === "ArrowRight") suivantImage(); // mis à jour
  });

  lightbox.addEventListener("click", (e) => {
    if (e.target === lightbox) fermer();
  });
});
