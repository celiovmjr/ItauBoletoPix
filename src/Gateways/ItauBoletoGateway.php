<?php

declare(strict_types=1);

namespace ItauBoletoPix\Gateways;

use ItauBoletoPix\Contracts\PaymentGatewayInterface;
use ItauBoletoPix\Exceptions\AuthenticationException;
use ItauBoletoPix\Exceptions\GatewayException;
use Psr\Log\LoggerInterface;

/**
 * Gateway para API do Itaú Boleto PIX
 */
class ItauBoletoGateway implements PaymentGatewayInterface
{
    private ?string $accessToken = null;
    private ?int $tokenExpiration = null;
    private ?array $lastResponse = null;

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $certificatePath,
        private string $certificateKeyPath,
        private bool $sandbox = true,
        private ?LoggerInterface $logger = null
    ) {
        $this->validateCertificates();
    }

    private function validateCertificates(): void
    {
        if (! file_exists($this->certificatePath)) {
            throw new GatewayException(
                "Certificado não encontrado: {$this->certificatePath}"
            );
        }

        if (! file_exists($this->certificateKeyPath)) {
            throw new GatewayException(
                "Chave privada não encontrada: {$this->certificateKeyPath}"
            );
        }
    }

    public function authenticate(): string
    {
        // Retorna token em cache se ainda válido
        if ($this->accessToken && $this->tokenExpiration > time()) {
            return $this->accessToken;
        }

        $this->logger?->info('Solicitando novo token de acesso');

        $url = 'https://sts.itau.com.br/api/oauth/token';

        $response = $this->makeRequest($url, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ], [
            'Content-Type: application/x-www-form-urlencoded',
            'x-itau-flowID: ' . $this->generateUuid(),
            'x-itau-correlationID: ' . $this->generateUuid(),
        ], 'POST', true);

        if (! isset($response['access_token'])) {
            throw new AuthenticationException(
                'Token não retornado na resposta',
                0,
                null,
                $response
            );
        }

        $this->accessToken = $response['access_token'];
        $this->tokenExpiration = time() + ($response['expires_in'] ?? 3600) - 60;

        $this->logger?->info('Token obtido com sucesso', [
            'expires_at' => date('Y-m-d H:i:s', $this->tokenExpiration),
        ]);

        return $this->accessToken;
    }

    public function sendBoletoRequest(array $payload): array
    {
        $token = $this->authenticate();

        $baseUrl = $this->sandbox
            ? 'https://secure.api.itau'
            : 'https://secure.api.cloud.itau.com.br';

        $url = $baseUrl . '/pix_recebimentos_conciliacoes/v2/boletos_pix';

        $this->logger?->info('Enviando requisição de boleto', [
            'url' => $url,
        ]);

        $response = $this->makeRequest($url, $payload, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'x-itau-apikey: ' . $this->clientId,
            'x-itau-correlationID: ' . $this->generateUuid(),
            'x-itau-flowID: ' . $this->generateUuid(),
        ], 'POST');

        $this->logger?->info('Boleto criado com sucesso', [
            'response' => $response,
        ]);

        return $response;
    }

    public function fetchBoleto(string $beneficiaryId, string $ourNumber): array
    {
        $token = $this->authenticate();

        $baseUrl = $this->sandbox
            ? 'https://secure.api.itau'
            : 'https://secure.api.cloud.itau.com.br';

        $queryParams = http_build_query([
            'id_beneficiario' => $beneficiaryId,
            'codigo_carteira' => '109',
            'nosso_numero' => $ourNumber,
            'view' => 'specific',
        ]);

        $url = $baseUrl . '/boletoscash/v2/boletos?' . $queryParams;

        $response = $this->makeRequest($url, null, [
            'Authorization: Bearer ' . $token,
            'x-itau-apikey: ' . $this->clientId,
            'x-itau-correlationID: ' . $this->generateUuid(),
            'x-itau-flowID: ' . $this->generateUuid(),
            'Content-Type: application/json',
        ], 'GET');

        return $response;
    }

    public function registerWebhook(array $webhookConfig): array
    {
        $token = $this->authenticate();

        $url = 'https://boletos.cloud.itau.com.br/boletos/v3/notificacoes_boletos';

        $response = $this->makeRequest($url, ['data' => $webhookConfig], [
            'Authorization: Bearer ' . $token,
            'x-itau-apikey: ' . $this->clientId,
            'x-itau-correlationID: ' . $this->generateUuid(),
            'Content-Type: application/json',
            'Accept: application/json',
        ], 'POST');

        return $response;
    }

    public function testConnection(): bool
    {
        try {
            $token = $this->authenticate();

            return ! empty($token);
        } catch (\Exception $e) {
            $this->logger?->error('Falha no teste de conexão', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    private function makeRequest(
        string $url,
        ?array $data,
        array $headers,
        string $method = 'POST',
        bool $formUrlEncoded = false
    ): array {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSLCERT => $this->certificatePath,
            CURLOPT_SSLKEY => $this->certificateKeyPath,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;

            if ($data) {
                $options[CURLOPT_POSTFIELDS] = $formUrlEncoded
                    ? http_build_query($data)
                    : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new GatewayException(
                "Erro CURL: {$error}",
                0,
                null,
                ['url' => $url]
            );
        }

        $decodedResponse = json_decode($response, true);
        $this->lastResponse = $decodedResponse ?? ['raw' => $response];

        // HTTP 200/201 são sucessos
        if (! in_array($httpCode, [200, 201])) {
            var_dump($response);

            throw new GatewayException(
                "Erro na API: HTTP {$httpCode}",
                0,
                $httpCode,
                $this->lastResponse
            );
        }

        return $this->lastResponse;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
