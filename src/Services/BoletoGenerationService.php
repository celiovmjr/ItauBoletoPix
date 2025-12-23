<?php

declare(strict_types=1);

namespace ItauBoletoPix\Services;

use ItauBoletoPix\Contracts\{BoletoServiceInterface, PaymentGatewayInterface};
use ItauBoletoPix\DTOs\{BoletoRequestDTO, BoletoResponseDTO};
use ItauBoletoPix\Exceptions\BoletoException;
use ItauBoletoPix\Models\Boleto;
use ItauBoletoPix\Utils\{MoneyFormatter, UuidHelper};
use Psr\Log\LoggerInterface;

/**
 * Serviço de geração de Boletos PIX
 */
class BoletoGenerationService implements BoletoServiceInterface
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO
    {
        try {
            $this->logger?->info('Iniciando criação de boleto', [
                'our_number' => $request->ourNumber,
                'payer' => $request->payer->getName(),
            ]);

            // Transforma o request em payload da API do Itaú
            $payload = $this->buildPayload($request);

            // Envia para o gateway
            $response = $this->gateway->sendBoletoRequest($payload);

            // Transforma resposta em DTO
            $boletoResponse = $this->parseResponse($response, $request);

            $this->logger?->info('Boleto criado com sucesso', [
                'id' => $boletoResponse->id,
                'our_number' => $boletoResponse->ourNumber,
            ]);

            return $boletoResponse;

        } catch (\Exception $e) {
            $this->logger?->error('Erro ao criar boleto', [
                'error' => $e->getMessage(),
                'our_number' => $request->ourNumber,
            ]);

            throw new BoletoException(
                "Falha ao criar boleto: {$e->getMessage()}",
                $e->getCode(),
                ['request' => $request->toArray()],
                $e
            );
        }
    }

    public function getBoleto(string $ourNumber): Boleto
    {
        // Implementação simplificada - adaptar conforme necessário
        throw new \RuntimeException('Método não implementado');
    }

    public function listBoletos(array $filters = []): array
    {
        // Implementação simplificada - adaptar conforme necessário
        throw new \RuntimeException('Método não implementado');
    }

    public function cancelBoleto(string $ourNumber): bool
    {
        // Implementação simplificada - adaptar conforme necessário
        throw new \RuntimeException('Método não implementado');
    }

    public function getPaymentStatus(string $ourNumber): string
    {
        // Implementação simplificada - adaptar conforme necessário
        throw new \RuntimeException('Método não implementado');
    }

    /**
     * Constrói o payload no formato da API do Itaú
     */
    private function buildPayload(BoletoRequestDTO $request): array
    {
        $beneficiary = $request->beneficiary;
        $payer = $request->payer;
        $charge = $request->charge;

        $payload = [
            'etapa_processo_boleto' => $request->processStep->value,
            'beneficiario' => [
                'id_beneficiario' => $beneficiary->getId(),
            ],
            'dado_boleto' => [
                'descricao_instrumento_cobranca' => 'boleto_pix',
                'tipo_boleto' => 'a vista',
                'texto_seu_numero' => $request->yourNumber,
                'codigo_carteira' => $beneficiary->getWalletCode(),
                'codigo_especie' => '01',
                'data_emissao' => $request->issueDate->format('Y-m-d'),
                'valor_abatimento' => '00000000000000000',
                'pagador' => [
                    'pessoa' => [
                        'nome_pessoa' => $payer->getName(),
                        'tipo_pessoa' => [
                            'codigo_tipo_pessoa' => $payer->getDocumentType(),
                            'numero_cadastro_pessoa_fisica' =>
                                $payer->getDocumentType() === 'F' ? $payer->getDocument() : null,
                            'numero_cadastro_nacional_pessoa_juridica' =>
                                $payer->getDocumentType() === 'J' ? $payer->getDocument() : null,
                        ],
                    ],
                    'endereco' => [
                        'nome_logradouro' => $payer->getAddress()->getStreet(),
                        'nome_bairro' => $payer->getAddress()->getNeighborhood(),
                        'nome_cidade' => $payer->getAddress()->getCity(),
                        'sigla_UF' => $payer->getAddress()->getState(),
                        'numero_CEP' => $payer->getAddress()->getZipCode(),
                    ],
                ],
                'dados_individuais_boleto' => [
                    [
                        'numero_nosso_numero' => str_pad($request->yourNumber, 8, '0', STR_PAD_LEFT),
                        'data_vencimento' => $request->dueDate->format('Y-m-d'),
                        'texto_uso_beneficiario' => $request->yourNumber,
                        'valor_titulo' => MoneyFormatter::format($request->amount),
                        'data_limite_pagamento' => $request->dueDate->format('Y-m-d'),
                    ],
                ],
            ],
            'dados_qrcode' => [
                'chave' => $beneficiary->getPixKey(),
            ],
        ];

        // Adiciona juros se configurado
        if ($charge?->hasInterest()) {
            $interest = $charge->getInterest();
            $payload['dado_boleto']['juros'] = [
                'codigo_tipo_juros' => $interest->getType(),
                'valor_juros' => MoneyFormatter::format($interest->getAmountPerDay()),
            ];
        }

        // Adiciona multa se configurado
        if ($charge?->hasFine()) {
            $fine = $charge->getFine();
            $payload['dado_boleto']['multa'] = [
                'codigo_tipo_multa' => $fine->getType(),
                'percentual_multa' => $this->formatPercentage($fine->getPercentage()),
            ];
        }

        // Adiciona desconto se configurado
        if ($charge?->hasDiscount()) {
            $discount = $charge->getDiscount();
            $payload['dado_boleto']['desconto'] = [
                'codigo_tipo_desconto' => $discount->getType(),
                'descontos' => [
                    [
                        'data_desconto' => $discount->getDueDate()->format('Y-m-d'),
                        'valor_desconto' => MoneyFormatter::format($discount->getAmount()),
                        'percentual_desconto' => $this->formatPercentage($discount->getPercentage()),
                    ],
                ],
            ];
        }

        // Adiciona mensagens se configurado
        if ($charge && ! empty($charge->getMessages())) {
            $payload['dado_boleto']['lista_mensagem_cobranca'] = array_map(
                static fn ($msg) => ['mensagem' => $msg],
                $charge->getMessages()
            );
        }

        return $payload;
    }

    /**
     * Transforma resposta da API em DTO
     */
    private function parseResponse(array $response, BoletoRequestDTO $request): BoletoResponseDTO
    {
        $boleto = $response['data']['dado_boleto']['dados_individuais_boleto'][0] ?? [];
        $pix = $response['data']['dados_qrcode'] ?? [];

        return new BoletoResponseDTO(
            id: UuidHelper::generate() ?? 'unknown',
            ourNumber: $boleto['numero_nosso_numero'] ?? $request->ourNumber,
            barcode: $boleto['codigo_barras'] ?? '',
            digitableLine: $boleto['numero_linha_digitavel'] ?? '',
            pixCopyPaste: $pix['emv'] ?? '',
            pixQrCode: $pix['base64'] ?? '',
            pixTxid: $pix['txid'] ?? '',
            amount: $boleto['valor_titulo'] ?? '',
            dueDate: $boleto['data_vencimento'] ?? '',
            rawResponse: $response
        );
    }

    /**
     * Formata percentual para o padrão do Itaú
     * Exemplo: 2.5% -> 000000002500
     */
    private function formatPercentage(float $percentage): string
    {
        $value = (int)($percentage * 1000);

        return str_pad((string)$value, 12, '0', STR_PAD_LEFT);
    }
}
