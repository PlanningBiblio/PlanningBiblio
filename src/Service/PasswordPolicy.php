<?php

namespace App\Service;

/**
 * Password complexity rules, shared by the agent forms and the console commands.
 *
 * A password is complex enough if it contains at least $minimumLength characters
 * (parameter Auth-PasswordLength), a digit, an uppercase letter, a lowercase letter
 * and a special character.
 */
class PasswordPolicy
{
    public const DEFAULT_MINIMUM_LENGTH = 8;

    // Special chars list come from this list: https://owasp.org/www-community/password-special-characters
    public const SPECIAL_CHARS = ['!', '"', '#', '$', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~'];

    private const DIGITS = '0123456789';
    private const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz';

    public function isComplexEnough(string $password, int $minimumLength = self::DEFAULT_MINIMUM_LENGTH): bool
    {
        if (strlen($password) < $minimumLength) {
            return false;
        }

        if (!preg_match('#[0-9]+#', $password)) {
            return false;
        }

        if (!preg_match('#[A-Z]+#', $password)) {
            return false;
        }

        if (!preg_match('#[a-z]+#', $password)) {
            return false;
        }

        foreach (self::SPECIAL_CHARS as $char) {
            if (strpos($password, $char) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a random password that satisfies isComplexEnough().
     * Ambiguous characters (I, l, O, o) are excluded from letters.
     */
    public function generate(int $length = 16): string
    {
        $length = max($length, 4);

        // Special chars that are easy to type and safe in a shell
        $special = '!#%*+-.:=?@_~';
        $all = self::DIGITS . self::UPPERCASE . self::LOWERCASE . $special;

        $chars = [
            $this->randomChar(self::DIGITS),
            $this->randomChar(self::UPPERCASE),
            $this->randomChar(self::LOWERCASE),
            $this->randomChar($special),
        ];

        while (count($chars) < $length) {
            $chars[] = $this->randomChar($all);
        }

        // Fisher-Yates shuffle with a cryptographically secure generator
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }

    private function randomChar(string $alphabet): string
    {
        return $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
}
