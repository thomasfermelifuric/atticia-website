/* ==========================================================================
   Formulaire de contact
   - vérifie les champs obligatoires et affiche les erreurs à côté du champ ;
   - présélectionne « futur partenaire » depuis la section Partenaires ;
   - envoie la demande à l'API Web3Forms.
   ========================================================================== */
(function () {
  "use strict";

  var formulaire = document.getElementById("formulaire-contact");
  if (!formulaire) return;

  var statut = formulaire.querySelector(".formulaire__statut");
  var bouton = formulaire.querySelector(".formulaire__envoyer");
  var libelleBouton = bouton.textContent;
  var envoiEnCours = false;

  var horodatage = formulaire.elements.affiche_le;
  if (horodatage) horodatage.value = String(Date.now());

  var regles = {
    nom: function (v) { return v.trim().length > 1; },
    email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()); },
    message: function (v) { return v.trim().length > 4; }
  };

  function verifierChamp(nom) {
    var champ = formulaire.elements[nom];
    var erreur = document.getElementById(nom + "-erreur");
    var valide = regles[nom](champ.value);
    champ.setAttribute("aria-invalid", String(!valide));
    if (erreur) {
      erreur.hidden = valide;
      if (valide) champ.removeAttribute("aria-describedby");
      else champ.setAttribute("aria-describedby", erreur.id);
    }
    return valide;
  }

  function afficherStatut(texte, type) {
    statut.textContent = texte;
    statut.classList.toggle("formulaire__statut--succes", type === "succes");
    statut.classList.toggle("formulaire__statut--erreur", type === "erreur");
  }

  // L'erreur disparaît dès que le visiteur corrige le champ
  Object.keys(regles).forEach(function (nom) {
    formulaire.elements[nom].addEventListener("input", function () {
      if (this.getAttribute("aria-invalid") === "true") verifierChamp(nom);
    });
  });

  // Depuis « Échanger sur un partenariat », on présélectionne le bon profil
  document.querySelectorAll('[data-profil="partenaire"]').forEach(function (lien) {
    lien.addEventListener("click", function () {
      var radio = formulaire.querySelector('input[name="profil"][value^="Futur partenaire"]');
      if (radio) radio.checked = true;
    });
  });

  formulaire.addEventListener("submit", function (e) {
    e.preventDefault();
    if (envoiEnCours) return;
    afficherStatut("", null);

    var invalides = Object.keys(regles).filter(function (nom) { return !verifierChamp(nom); });
    if (invalides.length) {
      formulaire.elements[invalides[0]].focus();
      return;
    }

    envoiEnCours = true;
    bouton.textContent = "Envoi en cours…";
    bouton.setAttribute("aria-busy", "true");

    fetch(formulaire.getAttribute("action"), {
      method: "POST",
      body: new FormData(formulaire),
      headers: { "Accept": "application/json" }
    })
      .then(function (reponse) {
        return reponse.json();
      })
      .then(function (donnees) {
        if (donnees.success) {
          formulaire.reset();
          if (horodatage) horodatage.value = String(Date.now());
          afficherStatut("Merci et à bientôt !", "succes");
        } else {
          throw new Error(donnees.message || "Erreur Web3Forms");
        }
      })
      .catch(function (erreur) {
        console.error("[Atticia] Erreur lors de l'envoi Web3Forms :", erreur);
        afficherStatut(
          "Une erreur est survenue. Si vous le souhaitez, vous pouvez nous contacter directement par email.",
          "erreur"
        );
      })
      .finally(function () {
        envoiEnCours = false;
        bouton.textContent = libelleBouton;
        bouton.removeAttribute("aria-busy");
        statut.focus();
      });
  });
})();
