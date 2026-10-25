/* ==========================================================================
   Navigation : menu mobile, bordure de l'en-tête au défilement,
   lien actif selon la section visible, année du pied de page
   ========================================================================== */
(function () {
  "use strict";

  var entete = document.getElementById("entete");
  var bouton = document.querySelector(".entete__menu-bouton");
  var navigation = document.getElementById("navigation");
  if (!entete || !bouton || !navigation) return;

  /* --- Menu mobile --------------------------------------------------------- */
  function ouvrirMenu(ouvrir) {
    bouton.setAttribute("aria-expanded", String(ouvrir));
    bouton.setAttribute("aria-label", ouvrir ? "Fermer le menu" : "Ouvrir le menu");
    navigation.classList.toggle("navigation--ouverte", ouvrir);
  }

  bouton.addEventListener("click", function () {
    ouvrirMenu(bouton.getAttribute("aria-expanded") !== "true");
  });

  // Un clic sur un lien referme le menu
  navigation.addEventListener("click", function (e) {
    if (e.target.closest("a")) ouvrirMenu(false);
  });

  // Échap referme le menu et rend le focus au bouton
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && bouton.getAttribute("aria-expanded") === "true") {
      ouvrirMenu(false);
      bouton.focus();
    }
  });

  // En repassant en affichage large, on remet le menu à zéro
  var large = window.matchMedia("(min-width: 1000px)");
  large.addEventListener("change", function () { ouvrirMenu(false); });

  /* --- Bordure de l'en-tête dès qu'on fait défiler ------------------------- */
  function surDefilement() {
    entete.classList.toggle("entete--defile", window.scrollY > 8);
  }
  window.addEventListener("scroll", surDefilement, { passive: true });
  surDefilement();

  /* --- Lien actif selon la section visible --------------------------------- */
  var liens = Array.prototype.slice.call(navigation.querySelectorAll(".navigation__liste a"));
  var sections = liens
    .map(function (lien) { return document.querySelector(lien.getAttribute("href")); })
    .filter(Boolean);

  if ("IntersectionObserver" in window && sections.length) {
    var visibles = {};
    var observateur = new IntersectionObserver(function (entrees) {
      entrees.forEach(function (entree) {
        visibles[entree.target.id] = entree.isIntersecting;
      });
      // Aucune section suivie au milieu de l'écran (accueil, enjeux…) : aucun lien actif
      var courante = sections.filter(function (s) { return visibles[s.id]; })[0];
      liens.forEach(function (lien) {
        if (courante && lien.getAttribute("href") === "#" + courante.id) lien.setAttribute("aria-current", "true");
        else lien.removeAttribute("aria-current");
      });
    }, { rootMargin: "-45% 0px -50% 0px" });
    sections.forEach(function (s) { observateur.observe(s); });
  }

  /* --- Année courante ------------------------------------------------------ */
  document.querySelectorAll("[data-annee]").forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });
})();
