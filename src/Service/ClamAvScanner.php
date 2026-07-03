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
    private bool $enabled = false;
    private LoggerInterface $logger;

    public function __construct(?string $clamavSocket, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $clamavSocket ??= '';
        $this->clamavSocket = $clamavSocket;

        if ($this->isConfigured()) {
            $this->logger->info("Connecting to ClamAV with CLAMAV_SOCKET=$clamavSocket");
            try {
                $this->client = ClamAV::fromDSN($clamavSocket);
                $this->logger->info("Successfully connected to CLAMAV_SOCKET=$clamavSocket");
                $this->enabled = true;
            } catch (\ErrorException $e) {
                $this->logger->error("Unable to connect to ClamaAV with CLAMAV_SOCKET=$clamavSocket");
            }
        } else {
            $this->logger->warning("ClamAV is not configured, please set CLAMAV_SOCKET in your .env file");
        }
    }

    public function isConfigured(): bool
    {
        $this->logger->info("clamav isConfigured:" . ($this->clamavSocket !== '' && $this->clamavSocket !== '0'));
        return ($this->clamavSocket !== '' && $this->clamavSocket !== '0');
    }

    public function isEnabled(): bool
    {
        $this->logger->info("clamav isEnabled:" . $this->enabled);
        return $this->enabled;
    }

    public function scan(File|string $file)
    {

        if (!$this->isEnabled() || !$this->isEnabled()) {
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


