<?php
/* ==========================================================================
   PAGE DE DIAGNOSTIC TEMPORAIRE — À SUPPRIMER APRÈS LE TEST
   Ouvrez https://votre-domaine/test-envoi.php dans votre navigateur.
   Elle indique si PHP fonctionne, sa version, et tente d'envoyer un
   e-mail de test à l'adresse de contact.
   ========================================================================== */
header('Content-Type: text/plain; charset=utf-8');

$destinataire = 'contact@atticia.com';

echo "DIAGNOSTIC DU FORMULAIRE ATTICIA\n";
echo "================================\n\n";
echo "1. PHP fonctionne : oui\n";
echo "2. Version de PHP : " . PHP_VERSION . "\n";
echo "3. Extension mbstring : " . (function_exists('mb_strlen') ? 'présente' : 'absente (pas bloquant)') . "\n";
echo "4. Fonction mail() : " . (function_exists('mail') ? 'disponible' : 'DÉSACTIVÉE') . "\n";
echo "5. Fichier contact.php présent : " . (is_file(__DIR__ . '/contact.php') ? 'oui' : 'NON') . "\n\n";

if (function_exists('mail')) {
    $enTetes = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: Site Atticia <$destinataire>";
    $ok = @mail($destinataire, 'Test du formulaire Atticia', "Si vous lisez ceci, l'envoi d'e-mails fonctionne.\n", $enTetes);
    if (!$ok) {
        $ok = @mail($destinataire, 'Test du formulaire Atticia (sans -f)', "Si vous lisez ceci, l'envoi d'e-mails fonctionne.\n", $enTetes);
    }
    $erreur = error_get_last();
    echo "6. Envoi d'un e-mail de test à $destinataire : " . ($ok ? 'accepté par le serveur' : 'ÉCHEC') . "\n";
    if (!$ok && $erreur) {
        echo "   Détail : " . $erreur['message'] . "\n";
    }
    if ($ok) {
        echo "   Vérifiez votre boîte de réception (et les spams) dans quelques minutes.\n";
    }
}

echo "\nPensez à supprimer ce fichier de votre hébergement une fois le test terminé.\n";
