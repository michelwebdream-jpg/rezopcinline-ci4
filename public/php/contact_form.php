<?php

header('Content-Type: text/html; charset=utf-8');

$email   = isset($_POST['email1']) ? trim((string) $_POST['email1']) : '';
$message = isset($_POST['message1']) ? (string) $_POST['message1'] : '';
$nom_activite_mission = isset($_POST['nom_activite_mission']) ? (string) $_POST['nom_activite_mission'] : '';

$email = filter_var($email, FILTER_SANITIZE_EMAIL);

if (filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {

    $subject = 'Historique de : ' . $nom_activite_mission . '.';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: info@web-dream.fr' . "\r\n";

    // Échapper le HTML tout en laissant les <br /> du message s'afficher comme retours à la ligne
    $messageSafe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $messageSafe = str_replace(['&lt;br /&gt;', '&lt;br/&gt;', '&lt;br&gt;'], '<br />', $messageSafe);

    $template = '<div style="padding:50px; color:white;"><h2>Historique de : ' . htmlspecialchars($nom_activite_mission, ENT_QUOTES, 'UTF-8') . '</h2><br/>'
        . '<br/>' . nl2br($messageSafe) . '<br/><br/>'
        . 'Envoyé de l\'application web REZO+ PC Inline.'
        . '<br/>';
    $sendmessage = "<div style=\"background-color:#7E7E7E; color:white;\">" . $template . "</div>";
    $sendmessage = wordwrap($sendmessage, 70);

    if (@mail($email, $subject, $sendmessage, $headers)) {
        echo "Message envoyé.";
    } else {
        echo "Erreur lors de l'envoi du message.";
    }

} else {
    echo "<span>* email invalide *</span>";
}
