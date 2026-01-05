<?php

declare(strict_types=1);

namespace ItauBoletoPix\Services;

use ItauBoletoPix\Contracts\BoletoServiceInterface;
use ItauBoletoPix\DTOs\{
    BoletoRequestDTO as BoletoRequest,
    BoletoResponseDTO as BoletoResponse
};
use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Models\{Beneficiary, Charge, Payer};
use Psr\Log\LoggerInterface;

/**
 * Serviço para geração automática mensal de boletos
 *
 * Este serviço é responsável por:
 * - Gerar boletos para todos os usuários no dia 01
 * - Calcular valores e datas automaticamente
 * - Gerenciar sequência de nosso número
 */
class BoletoSchedulerService
{
    public function __construct(
        private BoletoServiceInterface $boletoService,
        private UserRepositoryInterface $userRepository,
        private Beneficiary $defaultBeneficiary,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Gera boletos mensais para todos os usuários ativos
     *
     * @return array Resultado da geração com sucessos e falhas
     */
    public function generateMonthlyBoletos(): array
    {
        $this->logger?->info('Iniciando geração mensal de boletos');

        $users = $this->userRepository->getActiveUsers();
        $results = [
            'success' => [],
            'failed' => [],
            'total' => \count($users),
        ];

        $currentMonth = new \DateTimeImmutable('first day of this month');
        $dueDate = new \DateTimeImmutable('last day of this month');

        foreach ($users as $user) {
            try {
                $request = $this->buildBoletoRequestForUser(
                    $user,
                    $currentMonth,
                    $dueDate
                );

                $response = $this->boletoService->createBoleto($request);

                $results['success'][] = [
                    'user_id' => $user->getId(),
                    'user_name' => $user->getName(),
                    'boleto_id' => $response->getId(),
                    'our_number' => $response->getOurNumber(),
                ];

                $this->logger?->info('Boleto gerado com sucesso', [
                    'user_id' => $user->getId(),
                    'our_number' => $response->getOurNumber(),
                ]);

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'user_id' => $user->getId(),
                    'user_name' => $user->getName(),
                    'error' => $e->getMessage(),
                ];

                $this->logger?->error('Falha ao gerar boleto', [
                    'user_id' => $user->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger?->info('Geração mensal concluída', [
            'total' => $results['total'],
            'success' => count($results['success']),
            'failed' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Gera boleto para um usuário específico
     *
     * @param  UserInterface           $user    Usuário para gerar o boleto
     * @param  \DateTimeImmutable|null $dueDate Data de vencimento customizada
     * @return BoletoResponse          Resposta da criação do boleto
     */
    public function generateBoletoForUser(
        UserInterface $user,
        ?\DateTimeImmutable $dueDate = null
    ): BoletoResponse {
        $issueDate = new \DateTimeImmutable();
        $dueDate = $dueDate ?? new \DateTimeImmutable('+30 days');

        $request = $this->buildBoletoRequestForUser($user, $issueDate, $dueDate);

        return $this->boletoService->createBoleto($request);
    }

    /**
     * Constrói o request de boleto para um usuário
     */
    private function buildBoletoRequestForUser(
        UserInterface $user,
        \DateTimeImmutable $issueDate,
        \DateTimeImmutable $dueDate
    ): BoletoRequest {
        // Obtém próximo nosso número da sequência
        $ourNumber = $this->getNextOurNumber();

        // Gera número único do cliente (seu número)
        $yourNumber = str_pad((string)$user->getId(), 6, '0', STR_PAD_LEFT);

        // Cria o pagador a partir dos dados do usuário
        $payer = new Payer($user->toPerson());

        // Calcula valor da mensalidade
        $amount = $this->calculateMonthlyAmount($user);

        // Cria configuração de cobrança (juros, multa, etc)
        $charge = $this->buildChargeConfig($amount);

        return new BoletoRequest(
            beneficiary: $this->defaultBeneficiary,
            payer: $payer,
            ourNumber: $ourNumber,
            yourNumber: $yourNumber,
            amount: $amount,
            issueDate: $issueDate,
            dueDate: $dueDate,
            charge: $charge,
            processStep: ProcessStep::REGISTRATION
        );
    }

    /**
     * Calcula valor da mensalidade do usuário
     */
    private function calculateMonthlyAmount(UserInterface $user): float
    {
        // Implementar lógica de cálculo conforme regras de negócio
        return $user->getMonthlyFee();
    }

    /**
     * Cria configuração de cobrança (juros, multa, desconto)
     */
    private function buildChargeConfig(float $amount): Charge
    {
        // Juros de 1% ao mês (0.033% ao dia)
        $interest = new \ItauBoletoPix\Models\Interest(
            type: '93', // Valor por dia
            amountPerDay: $amount * 0.0003 // 0.03% ao dia
        );

        // Multa de 2%
        $fine = new \ItauBoletoPix\Models\Fine(
            type: '02', // Percentual
            percentage: 2.0
        );

        $messages = [
            'Não receber após o vencimento',
            'Juros de 0,03% ao dia após vencimento',
            'Multa de 2% após vencimento',
        ];

        return new Charge(
            interest: $interest,
            fine: $fine,
            discount: null,
            messages: $messages
        );
    }

    /**
     * Obtém próximo nosso número da sequência
     *
     * Implementar persistência adequada (banco de dados, Redis, etc)
     */
    private function getNextOurNumber(): string
    {
        // Exemplo simplificado - implementar controle real de sequência
        $lastNumber = $this->userRepository->getLastOurNumber();
        $nextNumber = $lastNumber + 1;

        $this->userRepository->saveLastOurNumber($nextNumber);

        return str_pad((string)$nextNumber, 8, '0', STR_PAD_LEFT);
    }
}

/**
 * Interface para repositório de usuários
 * Deve ser implementada pela aplicação
 */
interface UserRepositoryInterface
{
    /**
     * Retorna todos os usuários ativos
     *
     * @return array<UserInterface>
     */
    public function getActiveUsers(): array;

    /**
     * Obtém último nosso número utilizado
     */
    public function getLastOurNumber(): int;

    /**
     * Salva último nosso número
     */
    public function saveLastOurNumber(int $number): void;
}

/**
 * Interface para modelo de usuário
 * Deve ser implementada pela aplicação
 */
interface UserInterface
{
    public function getId(): int;
    public function getName(): string;
    public function getMonthlyFee(): float;
    public function toPerson(): \ItauBoletoPix\Models\PersonInterface;
}
