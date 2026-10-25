/* ==========================================================================
   Formulaire de contact
   - vérifie les champs obligatoires et affiche les erreurs à côté du champ ;
   - présélectionne « futur partenaire » depuis la section Partenaires ;
   - envoi : ouvre la messagerie du visiteur avec un e-mail prérempli.

   À FAIRE avant la mise en ligne : brancher un vrai service d'envoi
   (voir README.md, section « Formulaire de contact »). Il suffira alors de
   remplacer la fonction envoyer() ci-dessous.
   ========================================================================== */
(function () {
  "use strict";

  var formulaire = document.getElementById("formulaire-contact");
  if (!formulaire) return;

  var statut = formulaire.querySelector(".formulaire__statut");
  var destinataire = formulaire.getAttribute("data-destinataire") || "contact@atticia.fr";
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  var regles = {
    nom: function (v) { return v.trim().length > 1; },
    email: function (v) { return EMAIL.test(v.trim()); },
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

  function envoyer(donnees) {
    var sujet = "Demande de contact — " + (donnees.entreprise || donnees.nom);
    var corps = [
      "Nom : " + donnees.nom,
      "Entreprise : " + (donnees.entreprise || "—"),
      "E-mail : " + donnees.email,
      "Téléphone : " + (donnees.telephone || "—"),
      "Profil : " + donnees.profil,
      "",
      donnees.message
    ].join("\n");

    window.location.href = "mailto:" + destinataire +
      "?subject=" + encodeURIComponent(sujet) +
      "&body=" + encodeURIComponent(corps);
  }

  formulaire.addEventListener("submit", function (e) {
    e.preventDefault();
    statut.textContent = "";

    var invalides = Object.keys(regles).filter(function (nom) { return !verifierChamp(nom); });
    if (invalides.length) {
      formulaire.elements[invalides[0]].focus();
      return;
    }

    var f = formulaire.elements;
    envoyer({
      nom: f.nom.value.trim(),
      entreprise: f.entreprise.value.trim(),
      email: f.email.value.trim(),
      telephone: f.telephone.value.trim(),
      profil: formulaire.querySelector('input[name="profil"]:checked').value,
      message: f.message.value.trim()
    });

    statut.textContent = "Votre messagerie s'ouvre avec votre demande préremplie. Il ne vous reste qu'à l'envoyer.";
  });
})();
