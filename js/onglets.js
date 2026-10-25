/* ==========================================================================
   Onglets accessibles (prestations Cybersécurité / IA)
   Clavier : flèches gauche/droite, Début, Fin.
   ========================================================================== */
(function () {
  "use strict";

  document.querySelectorAll("[data-onglets]").forEach(function (bloc) {
    var onglets = Array.prototype.slice.call(bloc.querySelectorAll('[role="tab"]'));

    function activer(onglet, donnerFocus) {
      onglets.forEach(function (o) {
        var actif = o === onglet;
        o.setAttribute("aria-selected", String(actif));
        o.tabIndex = actif ? 0 : -1;
        var panneau = document.getElementById(o.getAttribute("aria-controls"));
        if (panneau) panneau.hidden = !actif;
      });
      if (donnerFocus) onglet.focus();
    }

    onglets.forEach(function (onglet, i) {
      onglet.addEventListener("click", function () { activer(onglet, false); });

      onglet.addEventListener("keydown", function (e) {
        var cible = null;
        if (e.key === "ArrowRight") cible = onglets[(i + 1) % onglets.length];
        else if (e.key === "ArrowLeft") cible = onglets[(i - 1 + onglets.length) % onglets.length];
        else if (e.key === "Home") cible = onglets[0];
        else if (e.key === "End") cible = onglets[onglets.length - 1];
        if (cible) {
          e.preventDefault();
          activer(cible, true);
        }
      });
    });
  });
})();
