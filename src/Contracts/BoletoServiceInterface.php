<?php

declare(strict_types=1);

namespace ItauBoletoPix\Contracts;

use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\DTOs\BoletoResponseDTO;
use ItauBoletoPix\Models\Boleto;

/**
 * Interface para serviços de geração de boletos
 *
 * Define o contrato que qualquer provedor de boletos deve implementar,
 * permitindo trocar facilmente de banco/gateway
 */
interface BoletoServiceInterface
{
    /**
     * Cria um novo boleto PIX
     *
     * @param  BoletoRequestDTO                          $request Dados do boleto a ser criado
     * @return BoletoResponseDTO                         Resposta com dados do boleto gerado
     * @throws \ItauBoletoPix\Exceptions\BoletoException
     */
    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO;

    /**
     * Consulta um boleto existente
     *
     * @param  string                                    $ourNumber Nosso número do boleto
     * @return Boleto                                    Dados completos do boleto
     * @throws \ItauBoletoPix\Exceptions\BoletoException
     */
    public function getBoleto(string $ourNumber): Boleto;

    /**
     * Lista boletos com filtros
     *
     * @param  array         $filters Filtros de busca (período, status, etc)
     * @return array<Boleto> Lista de boletos encontrados
     */
    public function listBoletos(array $filters = []): array;

    /**
     * Cancela um boleto
     *
     * @param  string                                    $ourNumber Nosso número do boleto
     * @return bool                                      True se cancelado com sucesso
     * @throws \ItauBoletoPix\Exceptions\BoletoException
     */
    public function cancelBoleto(string $ourNumber): bool;

    /**
     * Verifica status de pagamento
     *
     * @param  string $ourNumber Nosso número do boleto
     * @return string Status do pagamento (pending, paid, cancelled, expired)
     */
    public function getPaymentStatus(string $ourNumber): string;
}
