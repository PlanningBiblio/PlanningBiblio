<?php

namespace App\Planno;

use Unirest\Request;

class PlanningGenerationClient
{
    private $url;
    private $apiKey;

    public function __construct(?string $url, ?string $apiKey = null)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    /**
     * Envoie le payload à l'API de génération de planning et retourne la réponse décodée.
     * Lève une exception si l'appel échoue ou si la réponse n'est pas un JSON valide.
     */
    public function send(array $payload): array
    {
        if (empty($this->url)) {
            throw new \RuntimeException('L\'URL de l\'API de génération de planning n\'est pas configurée (PlanningGeneration-ApiUrl).');
        }

        $headers = ['Content-Type' => 'application/json'];
        if (!empty($this->apiKey)) {
            $headers['X-API-Key'] = $this->apiKey;
        }

        Request::timeout(120);

        $endpoint = rtrim($this->url, '/') . '/generer-planning';

        try {
            $response = Request::post($endpoint, $headers, json_encode($payload));
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'appel à l\'API de génération de planning : ' . $e->getMessage());
        }

        if (!$response or $response->code < 200 or $response->code >= 300) {
            $code = $response ? $response->code : 'aucune réponse';
            $body = $response ? (is_string($response->body) ? $response->body : json_encode($response->body)) : '';
            throw new \RuntimeException("Réponse invalide de l'API de génération de planning (code: $code) : $body");
        }

        $decoded = is_array($response->body) ? $response->body : json_decode(json_encode($response->body), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('La réponse de l\'API de génération de planning n\'est pas un JSON valide.');
        }

        return $decoded;
    }
}
