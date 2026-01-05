<?php

declare(strict_types=1);

namespace ItauBoletoPix\Contracts;

use ItauBoletoPix\Models\Address;

/**
 * Interface para representar pessoas (física ou jurídica)
 */
interface PersonInterface
{
    public function getName(): string;
    public function getDocument(bool $unmasked = true): ?string;
    public function getDocumentType(): string;
    public function getAddress(): Address;
}
