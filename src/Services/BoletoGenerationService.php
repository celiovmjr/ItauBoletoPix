<?php

declare(strict_types=1);

namespace ItauBoletoPix\Services;

use ItauBoletoPix\Contracts\BoletoServiceInterface;
use ItauBoletoPix\Contracts\PaymentGatewayInterface;
use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\DTOs\BoletoResponseDTO;
use ItauBoletoPix\Exceptions\BoletoException;
use ItauBoletoPix\Models\Boleto;
use ItauBoletoPix\Utils\MoneyFormatter;
use ItauBoletoPix\Utils\UuidHelper;
use Psr\Log\LoggerInterface;

/**
 * BoletoGenerationService
 *
 * Serviço responsável pela geração de Boletos PIX Itaú.
 *
 * PHP 8.4
 */
final class BoletoGenerationService implements BoletoServiceInterface
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private ?LoggerInterface $logger = null
    ) {}

    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO
    {
        try {
            $this->logger?->info('Iniciando criação de boleto', [
                'our_number' => $request->ourNumber,
                'payer' => $request->payer->getName(),
            ]);

            $payload = $this->buildPayload($request);
            $response = $this->gateway->sendBoletoRequest($payload);

            $boletoResponse = $this->parseResponse($response, $request);

            $this->logger?->info('Boleto criado com sucesso', [
                'id' => $boletoResponse->id,
                'our_number' => $boletoResponse->ourNumber,
            ]);

            return $boletoResponse;
        } catch (\Throwable $e) {
            $this->logger?->error('Erro ao criar boleto', [
                'error' => $e->getMessage(),
                'our_number' => $request->ourNumber,
            ]);

            throw new BoletoException(
                "Falha ao criar boleto: {$e->getMessage()}",
                (int) $e->getCode(),
                ['request' => $request->toArray()],
                $e
            );
        }
    }

    public function getBoleto(string $ourNumber): Boleto
    {
        throw new \RuntimeException('Método não implementado');
    }

    public function listBoletos(array $filters = []): array
    {
        throw new \RuntimeException('Método não implementado');
    }

    public function cancelBoleto(string $ourNumber): bool
    {
        throw new \RuntimeException('Método não implementado');
    }

    public function getPaymentStatus(string $ourNumber): string
    {
        throw new \RuntimeException('Método não implementado');
    }

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
                        'numero_nosso_numero' => str_pad($request->ourNumber, 8, '0', STR_PAD_LEFT),
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

        if ($charge?->hasInterest()) {
            $interest = $charge->getInterest();
            $type = $interest->getType();
            $typeCode = is_object($type) && property_exists($type, 'value') ? $type->value : $type;

            $juros = ['codigo_tipo_juros' => $typeCode];

            if ($interest->getStartDate()) {
                $juros['data_juros'] = $interest->getStartDate()->format('Y-m-d');
            }

            if ($interest->getDaysAfterDue()) {
                $juros['quantidade_dias_juros'] = $interest->getDaysAfterDue();
            }

            if (in_array($typeCode, ['93'], true) && $interest->getAmountPerDay() > 0) {
                $juros['valor_juros'] = MoneyFormatter::format($interest->getAmountPerDay());
            }

            if (in_array($typeCode, ['90', '91', '92'], true) && $interest->getPercentage() > 0) {
                $juros['percentual_juros'] = $this->formatJurosPercentage($interest->getPercentage());
            }

            if (count($juros) > 1) {
                $payload['dado_boleto']['juros'] = $juros;
            }
        }

        if ($charge?->hasFine()) {
            $fine = $charge->getFine();
            $type = $fine->getType();
            $typeCode = is_object($type) && property_exists($type, 'value') ? $type->value : $type;

            $multa = ['codigo_tipo_multa' => $typeCode];

            if ($fine->getStartDate()) {
                $multa['data_multa'] = $fine->getStartDate()->format('Y-m-d');
            }

            if ($fine->getDaysAfterDue()) {
                $multa['quantidade_dias_multa'] = $fine->getDaysAfterDue();
            }

            if ($typeCode === '01' && $fine->getAmount() > 0) {
                $multa['valor_multa'] = MoneyFormatter::format($fine->getAmount());
            }

            if ($typeCode === '02' && $fine->getPercentage() > 0) {
                $multa['percentual_multa'] = $this->formatMultaPercentage($fine->getPercentage());
            }

            if (count($multa) > 1) {
                $payload['dado_boleto']['multa'] = $multa;
            }
        }

        if ($charge && $charge->getMessages() !== []) {
            $payload['dado_boleto']['lista_mensagem_cobranca'] = array_map(
                static fn (string $msg): array => ['mensagem' => $msg],
                $charge->getMessages()
            );
        }

        return $payload;
    }

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

    private function formatJurosPercentage(float $percentage): string
    {
        return str_pad((string) (int) ($percentage * 100000), 12, '0', STR_PAD_LEFT);
    }

    private function formatMultaPercentage(float $percentage): string
    {
        $integer = (int) $percentage;
        $decimal = (int) round(($percentage - $integer) * 100000);

        return \sprintf('%07d%05d', $integer, $decimal);
    }
}
