<?php

declare(strict_types=1);

namespace ItauBoletoPix\Contracts;

/**
 * Interface para gateways de pagamento
 *
 * Abstrai a comunicação com APIs de bancos,
 * permitindo implementar múltiplos provedores
 */
interface PaymentGatewayInterface
{
    /**
     * Obtém token de autenticação
     *
     * @return string                                     Access token para uso na API
     * @throws \ItauBoletoPix\Exceptions\GatewayException
     */
    public function authenticate(): string;

    /**
     * Envia requisição para criar boleto
     *
     * @param  array                                      $payload Dados do boleto no formato da API
     * @return array                                      Resposta da API
     * @throws \ItauBoletoPix\Exceptions\GatewayException
     */
    public function sendBoletoRequest(array $payload): array;

    /**
     * Consulta boleto na API
     *
     * @param  string                                     $beneficiaryId ID do beneficiário
     * @param  string                                     $ourNumber     Nosso número
     * @return array                                      Dados do boleto
     * @throws \ItauBoletoPix\Exceptions\GatewayException
     */
    public function fetchBoleto(string $beneficiaryId, string $ourNumber): array;

    /**
     * Registra webhook para notificações
     *
     * @param  array                                      $webhookConfig Configuração do webhook
     * @return array                                      Resposta do registro
     * @throws \ItauBoletoPix\Exceptions\GatewayException
     */
    public function registerWebhook(array $webhookConfig): array;

    /**
     * Testa conectividade com a API
     *
     * @return bool True se conectado com sucesso
     */
    public function testConnection(): bool;

    /**
     * Retorna última resposta da API (para debug)
     *
     * @return array|null Última resposta recebida
     */
    public function getLastResponse(): ?array;
}
