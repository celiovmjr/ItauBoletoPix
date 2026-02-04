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
            $payload = $this->buildPayload($request);
            $response = $this->gateway->sendBoletoRequest($payload);

            return $this->parseResponse($response, $request);
        } catch (\Throwable $e) {
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

        $pagadorData = [
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
        ];

        if ($payer->getAddress() !== null) {
            $pagadorData['endereco'] = [
                'nome_logradouro' => $payer->getAddress()->getStreet(),
                'nome_bairro' => $payer->getAddress()->getNeighborhood(),
                'nome_cidade' => $payer->getAddress()->getCity(),
                'sigla_UF' => $payer->getAddress()->getState(),
                'numero_CEP' => $payer->getAddress()->getZipCode(),
            ];
        }

        return [
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
                'pagador' => $pagadorData,
                'dados_individuais_boleto' => [
                    [
                        'numero_nosso_numero' => str_pad($request->ourNumber, 8, '0', STR_PAD_LEFT),
                        'data_vencimento' => $request->dueDate->format('Y-m-d'),
                        'texto_uso_beneficiario' => $request->yourNumber,
                        'valor_titulo' => MoneyFormatter::format($request->amount),
                        'data_limite_pagamento' => (new \DateTimeImmutable())
                            ->modify('+180 days')
                            ->format('Y-m-d'),
                    ],
                ],
            ],
            'dados_qrcode' => [
                'chave' => $beneficiary->getPixKey(),
            ],
        ];
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
}
