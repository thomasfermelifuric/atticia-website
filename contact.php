<?php
/* ==========================================================================
   Atticia — envoi du formulaire de contact
   Fonctionne sur un hébergement web OVH (PHP + fonction mail()).
   Le formulaire (js/contact.js) envoie les champs ici en POST ; ce script
   vérifie les données, filtre les robots, puis envoie un e-mail à
   l'adresse ci-dessous. Il répond en JSON.
   ========================================================================== */

// --- Réglages ---------------------------------------------------------------
const DESTINATAIRE = 'contact@atticia.com';
// L'expéditeur doit être une adresse de votre propre domaine, sinon le
// message risque d'être refusé ou classé en spam.
const EXPEDITEUR = 'contact@atticia.com';
const NOM_EXPEDITEUR = 'Site Atticia';
// Délai minimal (en secondes) entre l'affichage de la page et l'envoi :
// un humain met plus de 3 secondes à remplir le formulaire, un robot non.
const DELAI_MINIMUM = 3;

date_default_timezone_set('Europe/Paris');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function repondre(int $code, bool $ok, string $message): void {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Nettoie un champ : supprime les retours à la ligne (anti-injection d'en-têtes) et limite la longueur. */
function champ_court(string $nom, int $max = 200): string {
    $valeur = trim((string)($_POST[$nom] ?? ''));
    $valeur = str_replace(["\r", "\n", "\0"], ' ', $valeur);
    return mb_substr($valeur, 0, $max);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    repondre(405, false, 'Méthode non autorisée.');
}

// --- Anti-spam ------------------------------------------------------------
// 1. Champ piège invisible pour les humains : s'il est rempli, c'est un robot.
if (!empty($_POST['site_web'])) {
    repondre(200, true, 'Merci, votre demande a bien été envoyée.'); // on ne prévient pas le robot
}
// 2. Envoi trop rapide après l'affichage de la page.
$affichage = (int)($_POST['affiche_le'] ?? 0);
if ($affichage > 0 && (time() - intdiv($affichage, 1000)) < DELAI_MINIMUM) {
    repondre(200, true, 'Merci, votre demande a bien été envoyée.');
}

// --- Lecture et vérification des champs -------------------------------------
$nom        = champ_court('nom', 120);
$entreprise = champ_court('entreprise', 160);
$email      = champ_court('email', 200);
$telephone  = champ_court('telephone', 40);
$profil     = champ_court('profil', 120);
$message    = trim((string)($_POST['message'] ?? ''));
$message    = mb_substr(str_replace("\0", '', $message), 0, 5000);

$profilsAutorises = [
    "Dirigeant ou salarié d'entreprise",
    "Futur partenaire (apporteur d'affaires)",
    'Autre',
];
if (!in_array($profil, $profilsAutorises, true)) {
    $profil = 'Non précisé';
}

if (mb_strlen($nom) < 2) {
    repondre(422, false, 'Indiquez votre nom.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(422, false, 'Indiquez une adresse e-mail valide.');
}
if (mb_strlen($message) < 5) {
    repondre(422, false, 'Décrivez votre besoin en quelques mots.');
}

// --- Composition de l'e-mail ------------------------------------------------
$sujet = 'Demande de contact — ' . ($entreprise !== '' ? $entreprise : $nom);

$corps = "Nouvelle demande reçue depuis le site atticia.com\n"
       . "--------------------------------------------------\n\n"
       . "Nom : $nom\n"
       . 'Entreprise : ' . ($entreprise !== '' ? $entreprise : '—') . "\n"
       . "E-mail : $email\n"
       . 'Téléphone : ' . ($telephone !== '' ? $telephone : '—') . "\n"
       . "Profil : $profil\n\n"
       . "Message :\n$message\n\n"
       . "--------------------------------------------------\n"
       . 'Envoyé le ' . date('d/m/Y à H:i') . "\n"
       . "Répondez directement à cet e-mail pour écrire à la personne.\n";

$enTetes = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: ' . mb_encode_mimeheader(NOM_EXPEDITEUR, 'UTF-8') . ' <' . EXPEDITEUR . '>',
    'Reply-To: ' . mb_encode_mimeheader($nom, 'UTF-8') . " <$email>",
    'X-Mailer: Atticia',
];

$envoye = mail(
    DESTINATAIRE,
    mb_encode_mimeheader($sujet, 'UTF-8'),
    $corps,
    implode("\r\n", $enTetes),
    '-f' . EXPEDITEUR
);

if (!$envoye) {
    repondre(500, false, "Votre demande n'a pas pu être envoyée. Écrivez-nous directement à " . DESTINATAIRE . '.');
}

repondre(200, true, 'Merci, votre demande a bien été envoyée. Nous vous répondons rapidement.');
