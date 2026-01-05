<?php

declare(strict_types=1);

namespace ItauBoletoPix\DTOs;

/**
 * DTO para resposta de criação de boleto Pix Itaú
 */
readonly class BoletoResponseDTO
{
    public function __construct(
        public string $id,
        public string $ourNumber,
        public string $barcode,
        public string $digitableLine,
        public string $pixCopyPaste,
        public string $pixQrCode,
        public string $pixTxid,
        public string $amount,
        public string $dueDate,
        public array $rawResponse = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'our_number' => $this->ourNumber,
            'barcode' => $this->barcode,
            'digitable_line' => $this->digitableLine,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'pix' => [
                'copy_paste' => $this->pixCopyPaste,
                'qr_code_base64' => $this->pixQrCode,
                'txid' => $this->pixTxid,
            ],
        ];
    }
}
