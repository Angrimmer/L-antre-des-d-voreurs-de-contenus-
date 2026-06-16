<?php

namespace Antre;

class ContactValidator
{
    public static function sanitize(array $raw): array
    {
        return [
            'name'    => trim(strip_tags($raw['name']    ?? '')),
            'email'   => trim(strip_tags($raw['email']   ?? '')),
            'message' => trim(strip_tags($raw['message'] ?? '')),
        ];
    }

    /** Retourne un message d'erreur ou null si valide. */
    public static function validate(array $data): ?string
    {
        ['name' => $name, 'email' => $email, 'message' => $message] = $data;

        if (!$name || !$email || !$message) {
            return 'Tous les champs sont obligatoires.';
        }
        if (mb_strlen($name) > 80) {
            return 'Nom trop long (80 caractères max).';
        }
        if (mb_strlen($message) > 2000) {
            return 'Message trop long (2000 caractères max).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Adresse email invalide.';
        }
        if (preg_match('/[\r\n]/', $name) || preg_match('/[\r\n]/', $email)) {
            return 'Caractères non autorisés.';
        }
        if (!preg_match('/^[\p{L}\p{M}\s\'\-\.]{1,80}$/u', $name)) {
            return 'Nom invalide.';
        }

        return null;
    }
}
