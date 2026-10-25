/* ==========================================================================
   Apparition douce des blocs au défilement
   Ne masque que les blocs encore sous la ligne de flottaison, et ne fait
   rien si l'utilisateur a demandé à réduire les animations.
   ========================================================================== */
(function () {
  "use strict";

  var reduire = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduire || !("IntersectionObserver" in window)) return;

  var blocs = document.querySelectorAll(".apparition");
  var hauteur = window.innerHeight;

  var observateur = new IntersectionObserver(function (entrees) {
    entrees.forEach(function (entree) {
      if (entree.isIntersecting) {
        entree.target.classList.add("apparition--visible");
        observateur.unobserve(entree.target);
      }
    });
  }, { rootMargin: "0px 0px -8% 0px" });

  blocs.forEach(function (bloc) {
    if (bloc.getBoundingClientRect().top > hauteur) {
      bloc.classList.add("apparition--attente");
      observateur.observe(bloc);
    }
  });
})();
