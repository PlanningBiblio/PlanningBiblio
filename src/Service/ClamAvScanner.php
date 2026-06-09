<?php
namespace App\Service;

#use ClamAV\ClamAV;
use AdrienGras\PhpClamAV\ClamAV;
use AdrienGras\PhpClamAV\ClamAV\Exception\ClamAVException;
use Symfony\Component\HttpFoundation\File\File;
use Psr\Log\LoggerInterface;

class ClamAvScanner
{
    private ClamAV $client;
    private string $clamavSocket;
    private LoggerInterface $logger;

    public function __construct(?string $clamavSocket, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $clamavSocket ??= '';
        $this->clamavSocket = $clamavSocket;
        if ($clamavSocket !== '' && $clamavSocket !== '0') {
            $this->logger->info("Connecting to ClamAV with CLAMAV_SOCKET=$clamavSocket");
            $this->client = ClamAV::fromDSN($clamavSocket);
        } else {
            $this->logger->warning("ClamAV is not configured, please set CLAMAV_SOCKET in your .env file");
        }
    }

    public function isEnabled(): bool
    {
        return !empty($this->clamavSocket);
    }

    public function scan(File|string $file)
    {

        if (!$this->isEnabled() || !$this->client) {
            $this->logger->error("ClamAV is not configured or not started, please set CLAMAV_SOCKET in your .env file");
            return;
        }

        $path = $file instanceof File
            ? $file->getRealPath()
            : $file;

        if (!$path || !is_readable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File does not exist : %s',
                $path
            ));
        }

        try {
            $clean = $this->client->scan($path);

            if ($clean == 1) {
                $this->logger->debug("ClamAV scanning $path: OK");
            } else {
                $this->logger->error("ClamAV scanning $path: ALERT");
            }
            return $clean;
        } catch (ClamAVException $e) {
            throw new \RuntimeException(
                "ClamAV on $path: scan error",
                previous: $e
            );
        }
    }
}


