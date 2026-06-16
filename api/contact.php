<?php
header('Content-Type: application/json; charset=utf-8');

// --- Nettoyage brut ---
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// --- Champs obligatoires ---
if (!$name || !$email || !$message) {
    echo json_encode(['error' => 'Tous les champs sont obligatoires.']); exit;
}

// --- Longueurs max ---
if (mb_strlen($name) > 80) {
    echo json_encode(['error' => 'Nom trop long (80 caractères max).']); exit;
}
if (mb_strlen($message) > 2000) {
    echo json_encode(['error' => 'Message trop long (2000 caractères max).']); exit;
}

// --- Validation email ---
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Adresse email invalide.']); exit;
}

// --- Injection d'en-têtes mail (CR/LF dans nom ou email) ---
if (preg_match('/[\r\n]/', $name) || preg_match('/[\r\n]/', $email)) {
    echo json_encode(['error' => 'Caractères non autorisés.']); exit;
}

// --- Nom : lettres, espaces, tirets, apostrophes uniquement ---
if (!preg_match('/^[\p{L}\p{M}\s\'\-\.]{1,80}$/u', $name)) {
    echo json_encode(['error' => 'Nom invalide.']); exit;
}

$to      = 'angrimmer@pm.me';
$subject = '[Antre] Message de ' . $name;
$body    = "Nom : $name\nEmail : $email\n\n" . wordwrap($message, 70, "\n", true);
$headers = implode("\r\n", [
    'From: noreply@antre-devo.fr',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=utf-8',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => "Échec de l'envoi. Contacte-nous directement par email."]);
}
