<?php

declare(strict_types=1);

namespace App\Validation;

use App\Repositories\AssetRepository;

final class Validator
{
    private const HOSTNAME = '/^(?=.{1,253}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i';
    private const USERNAME = '/^[a-z0-9_.-]{3,32}$/i';

    public static function asset(array $input): array
    {
        $errors = [];
        $data = [
            'hostname' => strtolower(self::clean($input['hostname'] ?? '')),
            'ip_address' => self::clean($input['ip_address'] ?? ''),
            'location' => self::clean($input['location'] ?? ''),
            'environment' => self::clean($input['environment'] ?? ''),
            'status' => self::clean($input['status'] ?? ''),
            'notes' => self::clean($input['notes'] ?? '', true),
        ];

        if ($data['hostname'] === '') {
            $errors['hostname'] = 'Enter a hostname.';
        } elseif (!preg_match(self::HOSTNAME, $data['hostname'])) {
            $errors['hostname'] = 'Use letters, numbers, hyphens and dots only, e.g. web-01.example.net.';
        }

        if ($data['ip_address'] === '') {
            $data['ip_address'] = null;
        } elseif (!filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
            $errors['ip_address'] = 'Enter a valid IPv4 or IPv6 address, or leave it blank.';
        }

        if (mb_strlen($data['location']) > 120) {
            $errors['location'] = 'Keep the location under 120 characters.';
        }
        if (!in_array($data['environment'], AssetRepository::ENVIRONMENTS, true)) {
            $errors['environment'] = 'Choose an environment.';
        }
        if (!in_array($data['status'], AssetRepository::STATUSES, true)) {
            $errors['status'] = 'Choose a status.';
        }
        if (mb_strlen($data['notes']) > 2000) {
            $errors['notes'] = 'Keep notes under 2,000 characters.';
        }

        return [$data, $errors];
    }

    public static function registration(array $input): array
    {
        $errors = [];
        $data = [
            'username' => self::clean($input['username'] ?? ''),
            'email' => strtolower(self::clean($input['email'] ?? '')),
        ];
        $password = (string) ($input['password'] ?? '');

        if (!preg_match(self::USERNAME, $data['username'])) {
            $errors['username'] = 'Use 3–32 letters, numbers, dots, dashes or underscores.';
        }
        if (strlen($data['email']) > 254 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($error = self::passwordError($password, (string) ($input['password_confirmation'] ?? ''), $data['username'])) {
            $errors['password'] = $error;
        }
        if (($input['consent'] ?? '') !== '1') {
            $errors['consent'] = 'You need to accept the Terms and Privacy Policy to create an account.';
        }

        return [$data, $errors];
    }

    public static function passwordError(string $password, string $confirmation, string $username = ''): ?string
    {
        $length = mb_strlen($password);
        return match (true) {
            $length < 12 => 'Use at least 12 characters.',
            $length > 128 => 'Use 128 characters or fewer.',
            $username !== '' && stripos($password, $username) !== false => 'Your password must not contain your username.',
            $password !== $confirmation => 'The two passwords do not match.',
            default => null,
        };
    }

    private static function clean(mixed $value, bool $multiline = false): string
    {
        if (!is_string($value)) {
            return '';
        }
        $pattern = $multiline ? '/[^\P{C}\n\t]/u' : '/\p{C}/u';
        return trim((string) preg_replace($pattern, '', str_replace("\r\n", "\n", $value)));
    }
}
