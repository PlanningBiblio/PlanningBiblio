<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261118163700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT 55621: Change type of OIDC-ClientSecret and MSGraph-ClientSecret to "secret"';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        foreach (['OIDC-ClientSecret', 'MSGraph-ClientSecret'] as $name) {
            $type = $this->connection->fetchOne("SELECT type FROM config WHERE nom = ?", [$name]);
            if ($type === 'secret') {
                // Migration is already done for this parameter.
                continue;
            }

            $value = (string) $this->connection->fetchOne("SELECT valeur FROM config WHERE nom = ?", [$name]);
            $this->addSql(
                "UPDATE {$dbprefix}config SET type = 'secret', valeur = ? WHERE nom = ?",
                [$this->encrypt($value), $name]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        foreach (['OIDC-ClientSecret', 'MSGraph-ClientSecret'] as $name) {
            $type = $this->connection->fetchOne("SELECT type FROM config WHERE nom = ?", [$name]);
            if ($type === 'text') {
                // Migration is already done for this parameter.
                continue;
            }

            $value = (string) $this->connection->fetchOne("SELECT valeur FROM config WHERE nom = ?", [$name]);
            $this->addSql(
                "UPDATE {$dbprefix}config SET type = 'text', valeur = ? WHERE nom = ?",
                [$this->decrypt($value), $name]
            );
        }
    }

    /*
     * encrypt and decrypt methods were copied from legacy/Common/function.php
     * We can't use the original functions in a migration because the may
     * change in the future, making the migration invalid.
     * They were slightly modified to throw exceptions in case of failure
     */

    protected function encrypt(string $data): string|false
    {
        if ($data === '') {
            return '';
        }

        $key = $_ENV['APP_SECRET'] ?? null;
        if (empty($key)) {
            throw new \Exception('No secret is configured');
        }

        $cipherAlgo = 'AES-128-CTR';
        $passphrase = openssl_digest($key, 'SHA256', true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipherAlgo));

        $encryptedData = openssl_encrypt($data, $cipherAlgo, $passphrase, 0, $iv) . "::" . bin2hex($iv);
        if ($encryptedData === false) {
            throw new \Exception('openssl_encrypt failed');
        }

        return $encryptedData;
    }

    protected function decrypt(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $key = $_ENV['APP_SECRET'] ?? null;
        if (empty($key)) {
            throw new \Exception('No secret is configured');
        }

        if (!preg_match("/^(.*)::(.*)$/", $data, $matches)) {
            throw new \Exception('Data does not match expected format');
        }

        $encryptedData = $matches[1];
        $iv = hex2bin($matches[2]);

        $cipherAlgo = 'AES-128-CTR';
        $passphrase = openssl_digest($key, 'SHA256', true);

        $decryptedData = openssl_decrypt($encryptedData, $cipherAlgo, $passphrase, 0, $iv);
        if ($decryptedData === false) {
            throw new \Exception("openssl_decrypt failed");
        }

        return $decryptedData;
    }
}
