<?php
declare(strict_types=1);
// Vérifier strictement les types déclarés lors des appels de fonctions depuis ce fichier.

// Configurer ces deux variables sur le serveur (voir README.md).
$recipient = getenv('CONTACT_TO') ?: '';
$sender = getenv('CONTACT_FROM') ?: '';

// Une réponse JSON pour fetch, ou une page HTML si le formulaire est envoyé sans JavaScript.
function respond(int $status, bool $success, string $message): void
{
    http_response_code($status);
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'message' => $message]);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        // Échapper les caractères spéciaux pour afficher du texte, pas du code HTML.
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contact — Web2Risk</title><body><main><h1>Contact</h1><p>' . $safeMessage . '</p><a href="index.html#contact">Back to the website</a></main></body></html>';
    }
    // Arrêter le script après la réponse pour ne pas poursuivre un envoi invalide.
    exit;
}

// Une visite directe de cette URL ne doit pas déclencher d'envoi.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'Please use the contact form to send a message.');
}
// Une requête peut être fabriquée sans passer par notre formulaire : refuser les tableaux.
foreach (['name', 'email', 'message', 'website'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        respond(400, false, 'Invalid form data.');
    }
}
// ?? fournit une chaîne vide si le champ manque ; trim retire les espaces aux extrémités.
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
// Champ piège : certains robots remplissent même les champs masqués.
if (!empty($_POST['website'])) {
    respond(400, false, 'Your message could not be sent.');
}
// Refaire les contrôles côté serveur, car ceux du navigateur peuvent être contournés.
// Refuser les retours à la ligne dans l'adresse pour protéger les en-têtes du mail.
// strlen compte des octets : les limites du texte laissent de la place aux caractères UTF-8.
if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || preg_match('/[\r\n]/', $email) || strlen($name) > 400
    || strlen($email) > 254 || strlen($message) > 20000) {
    respond(422, false, 'Please provide a valid name, email address and message.');
}
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) || !filter_var($sender, FILTER_VALIDATE_EMAIL)
    || preg_match('/[\r\n]/', $recipient . $sender)) {
    respond(503, false, 'The contact service is not configured yet. Please try again later.');
}

// Limiter les tentatives à une par minute dans la même session (protection simple).
session_start();
if (time() - ($_SESSION['last_contact'] ?? 0) < 60) {
    header('Retry-After: 60');
    respond(429, false, 'Please wait one minute before sending another message.');
}
$_SESSION['last_contact'] = time();
session_write_close();
$body = "Name: $name\nEmail: $email\n\n$message";
$headers = [
    // From utilise l'adresse du serveur ; Reply-To permet de répondre au visiteur.
    'From' => $sender,
    'Reply-To' => $email,
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/plain; charset=UTF-8',
];
// mail nécessite un serveur d'envoi configuré. @ masque ses avertissements techniques,
// mais sa valeur de retour reste contrôlée pour afficher un message d'erreur adapté.
if (!function_exists('mail') || !@mail($recipient, 'Web2Risk - New enquiry', $body, $headers)) {
    respond(503, false, 'Your message could not be sent. Please try again later.');
}
// L'acceptation par le serveur ne garantit pas la réception dans la boîte du destinataire.
respond(200, true, 'Thank you! Your message has been accepted for sending.');
