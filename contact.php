<?php
/* ==========================================================================
   Atticia — envoi du formulaire de contact
   Fonctionne sur un hébergement web OVH (PHP 5.4 ou plus, fonction mail()).
   Le formulaire (js/contact.js) envoie les champs ici en POST ; ce script
   vérifie les données, filtre les robots, puis envoie un e-mail à
   l'adresse ci-dessous. Il répond en JSON.
   ========================================================================== */

// --- Réglages ---------------------------------------------------------------
define('DESTINATAIRE', 'contact@atticia.com');
// L'expéditeur doit être une adresse de votre propre domaine, sinon le
// message risque d'être refusé ou classé en spam.
define('EXPEDITEUR', 'contact@atticia.com');
define('NOM_EXPEDITEUR', 'Site Atticia');
// Délai minimal (en secondes) entre l'affichage de la page et l'envoi :
// un humain met plus de 3 secondes à remplir le formulaire, un robot non.
define('DELAI_MINIMUM', 3);

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Europe/Paris');
}
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function repondre($code, $ok, $message, $detail = null) {
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        header('X-Status: ' . $code, true, $code);
    }
    $reponse = array('ok' => $ok, 'message' => $message);
    if ($detail !== null) {
        $reponse['detail'] = $detail; // visible dans la console du navigateur, utile au diagnostic
    }
    echo json_encode($reponse);
    exit;
}

/* Fonctions de texte qui marchent même si l'extension mbstring est absente */
function longueur($texte) {
    return function_exists('mb_strlen') ? mb_strlen($texte, 'UTF-8') : strlen(utf8_decode($texte));
}
function couper($texte, $max) {
    return function_exists('mb_substr') ? mb_substr($texte, 0, $max, 'UTF-8') : substr($texte, 0, $max * 2);
}
function entete_utf8($texte) {
    return '=?UTF-8?B?' . base64_encode($texte) . '?=';
}

/** Nettoie un champ : supprime les retours à la ligne (anti-injection d'en-têtes) et limite la longueur. */
function champ_court($nom, $max) {
    $valeur = isset($_POST[$nom]) ? trim((string) $_POST[$nom]) : '';
    $valeur = str_replace(array("\r", "\n", "\0"), ' ', $valeur);
    return couper($valeur, $max);
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, false, 'Méthode non autorisée.');
}

// --- Anti-spam ------------------------------------------------------------
// 1. Champ piège invisible pour les humains : s'il est rempli, c'est un robot.
if (!empty($_POST['site_web'])) {
    repondre(200, true, 'Merci, votre demande a bien été envoyée.'); // on ne prévient pas le robot
}
// 2. Envoi trop rapide après l'affichage de la page.
$affichage = isset($_POST['affiche_le']) ? (float) $_POST['affiche_le'] : 0;
if ($affichage > 0 && (time() - floor($affichage / 1000)) < DELAI_MINIMUM) {
    repondre(200, true, 'Merci, votre demande a bien été envoyée.');
}

// --- Lecture et vérification des champs -------------------------------------
$nom        = champ_court('nom', 120);
$entreprise = champ_court('entreprise', 160);
$email      = champ_court('email', 200);
$telephone  = champ_court('telephone', 40);
$profil     = champ_court('profil', 120);
$message    = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
$message    = couper(str_replace("\0", '', $message), 5000);

$profilsAutorises = array(
    "Dirigeant ou salarié d'entreprise",
    "Futur partenaire (apporteur d'affaires)",
    'Autre',
);
if (!in_array($profil, $profilsAutorises, true)) {
    $profil = 'Non précisé';
}

if (longueur($nom) < 2) {
    repondre(422, false, 'Indiquez votre nom.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(422, false, 'Indiquez une adresse e-mail valide.');
}
if (longueur($message) < 5) {
    repondre(422, false, 'Décrivez votre besoin en quelques mots.');
}

// --- Composition de l'e-mail ------------------------------------------------
$sujet = 'Demande de contact - ' . ($entreprise !== '' ? $entreprise : $nom);

$corps = "Nouvelle demande reçue depuis le site atticia.com\n"
       . "--------------------------------------------------\n\n"
       . "Nom : $nom\n"
       . 'Entreprise : ' . ($entreprise !== '' ? $entreprise : '-') . "\n"
       . "E-mail : $email\n"
       . 'Téléphone : ' . ($telephone !== '' ? $telephone : '-') . "\n"
       . "Profil : $profil\n\n"
       . "Message :\n$message\n\n"
       . "--------------------------------------------------\n"
       . 'Envoyé le ' . date('d/m/Y à H:i') . "\n"
       . "Répondez directement à cet e-mail pour écrire à la personne.\n";

$enTetes = implode("\r\n", array(
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: ' . entete_utf8(NOM_EXPEDITEUR) . ' <' . EXPEDITEUR . '>',
    'Reply-To: ' . entete_utf8($nom) . " <$email>",
    'X-Mailer: Atticia',
));

if (!function_exists('mail')) {
    repondre(500, false, "Votre demande n'a pas pu être envoyée. Écrivez-nous directement à " . DESTINATAIRE . '.',
        "La fonction mail() est désactivée sur cet hébergement.");
}

// Certains hébergements refusent le paramètre -f : on réessaie sans lui.
$envoye = @mail(DESTINATAIRE, entete_utf8($sujet), $corps, $enTetes, '-f' . EXPEDITEUR);
if (!$envoye) {
    $envoye = @mail(DESTINATAIRE, entete_utf8($sujet), $corps, $enTetes);
}

if (!$envoye) {
    $erreur = error_get_last();
    repondre(500, false, "Votre demande n'a pas pu être envoyée. Écrivez-nous directement à " . DESTINATAIRE . '.',
        'mail() a renvoyé false' . ($erreur ? ' : ' . $erreur['message'] : '') . '. Vérifiez dans l\'espace client OVH que l\'envoi d\'e-mails est actif.');
}

repondre(200, true, 'Merci, votre demande a bien été envoyée. Nous vous répondons rapidement.');
