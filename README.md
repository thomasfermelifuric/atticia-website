# Site Atticia

Site vitrine d'une page, en HTML, CSS et JavaScript sans dépendance ni outil de compilation, avec un petit script PHP pour l'envoi du formulaire de contact.

## Arborescence

```
index.html              Page d'accueil (toutes les sections)
mentions-legales.html   Mentions légales
contact.php             Envoi du formulaire de contact par e-mail
favicon.ico             Icône du site (Google, navigateurs)
robots.txt              Autorise l'indexation, indique le plan du site
sitemap.xml             Plan du site pour Google
css/
  tokens.css            Couleurs, police, espacements : l'identité visuelle
  base.css              Remise à zéro, typographie, focus clavier
  layout.css            Conteneur, grilles, en-tête, menu, pied de page
  components.css        Boutons, cartes, pastilles, puces, formulaire
  sections.css          Mise en forme propre à chaque section
  pages.css             Pages de texte (mentions légales)
js/
  navigation.js         Menu mobile, lien actif, année du pied de page
  apparition.js         Apparition douce des blocs au défilement
  contact.js            Vérification et envoi du formulaire
assets/
  logo/                 Logos Nuit et blanc, symbole seul, logo carré 512 px
  fonts/                Poppins (400, 500, 600), licence OFL
  favicon.svg, favicon-48x48.png, favicon-96x96.png, favicon-192x192.png, apple-touch-icon.png
```

## Mettre en ligne chez OVH

Envoyez tout le contenu de ce dossier dans le dossier `www` de votre hébergement web OVH (par FTP, avec FileZilla par exemple). `index.html` et `favicon.ico` doivent se trouver à la racine du site.

## Formulaire de contact

Le formulaire envoie la demande à `contact.php`, qui l'adresse par e-mail à `contact@atticia.com`. Le visiteur reçoit une confirmation sur la page, et vous pouvez lui répondre directement depuis l'e-mail reçu.

- **Prérequis** : un hébergement web OVH avec PHP (c'est le cas des offres d'hébergement mutualisé). L'envoi ne fonctionne pas en ouvrant simplement `index.html` sur votre ordinateur.
- **Changer d'adresse** : modifiez `DESTINATAIRE` en haut de `contact.php`. L'expéditeur (`EXPEDITEUR`) doit rester une adresse de votre domaine, sinon les e-mails risquent d'arriver en spam.
- **Anti-spam** : un champ invisible piège les robots, et les envois faits moins de 3 secondes après l'affichage de la page sont ignorés.
- **Premier test** : après la mise en ligne, envoyez-vous une demande et vérifiez votre boîte de réception, y compris les spams. Si rien n'arrive, vérifiez dans l'espace client OVH que l'envoi d'e-mails est activé pour l'hébergement.

## Logo dans les résultats Google

Google affiche l'icône du site (`favicon.ico` et les PNG), pas le SVG. Les icônes et les données structurées (nom, logo, adresse de l'entreprise) sont déjà en place dans `index.html`.

1. Vérifiez que l'adresse `https://atticia.com` utilisée dans `index.html`, `robots.txt` et `sitemap.xml` est bien la vôtre (avec ou sans `www`).
2. Après la mise en ligne, ajoutez votre site dans Google Search Console, envoyez le fichier `sitemap.xml`, puis demandez l'indexation de la page d'accueil avec l'outil d'inspection d'URL.
3. Google met de quelques jours à quelques semaines à afficher la nouvelle icône.

## Voir le site en local

```
php -S localhost:8000
```

puis allez sur http://localhost:8000 (le formulaire s'affiche, mais l'e-mail ne part pas sans serveur d'envoi).

## Modifier l'identité

Toutes les couleurs sont des variables dans `css/tokens.css` (`--bleu`, `--violet`, `--encre`…), avec un thème clair et un thème sombre. Une section passe en thème sombre avec l'attribut `data-theme="sombre"`.

## Encore à faire

1. **Image de partage** : ajoutez une image 1200 × 630 px (`assets/partage.png`) et la balise `<meta property="og:image" content="https://atticia.com/assets/partage.png">` dans `index.html` pour un bel aperçu sur LinkedIn.
2. **Statut des solutions** : vérifiez la mention « En développement » du filtre anti-phishing dans la section `#solutions`.

## Choix techniques

- **Aucun service tiers** : la police est auto-hébergée, il n'y a ni cookie, ni outil de suivi, ni script externe, et le formulaire passe par votre propre hébergement en France. Le site est donc conforme au RGPD sans bandeau de cookies, tant qu'aucun outil de mesure d'audience n'est ajouté.
- **Accessible** : lien d'évitement, navigation au clavier, focus visible, contrastes vérifiés, animations désactivées si le visiteur le demande.
- **Responsive** : mobile d'abord, menu déroulant sous 1000 px.
