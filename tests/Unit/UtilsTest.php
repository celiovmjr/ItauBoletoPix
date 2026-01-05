<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit;

use ItauBoletoPix\Utils\{DocumentValidator, MoneyFormatter};
use PHPUnit\Framework\TestCase;

/**
 * Testes para utilitários
 */
class UtilsTest extends TestCase
{
    public function testMoneyFormatter(): void
    {
        // Testa formatação
        $this->assertEquals('00000000000010000', MoneyFormatter::format(100.00));
        $this->assertEquals('00000000000000010', MoneyFormatter::format(0.10));
        $this->assertEquals('00000000000150075', MoneyFormatter::format(1500.75));

        // Testa parsing
        $this->assertEquals(100.00, MoneyFormatter::parse('00000000000010000'));
        $this->assertEquals(0.10, MoneyFormatter::parse('00000000000000010'));
        $this->assertEquals(1500.75, MoneyFormatter::parse('00000000000150075'));
    }

    public function testDocumentValidatorCPF(): void
    {
        // CPFs válidos
        $this->assertTrue(DocumentValidator::isValidCPF('111.444.777-35'));

        // CPFs inválidos
        $this->assertFalse(DocumentValidator::isValidCPF('111.111.111-11'));
        $this->assertFalse(DocumentValidator::isValidCPF('123.456.789-00'));
        $this->assertFalse(DocumentValidator::isValidCPF('12345'));
    }

    public function testDocumentValidatorCNPJ(): void
    {
        // CNPJs válidos
        $this->assertTrue(DocumentValidator::isValidCNPJ('11.222.333/0001-81'));

        // CNPJs inválidos
        $this->assertFalse(DocumentValidator::isValidCNPJ('11.111.111/1111-11'));
        $this->assertFalse(DocumentValidator::isValidCNPJ('12.345.678/0001-00'));
        $this->assertFalse(DocumentValidator::isValidCNPJ('12345'));
    }

    public function testDocumentCleaner(): void
    {
        $this->assertEquals('12345678900', DocumentValidator::clean('123.456.789-00'));
        $this->assertEquals('12345678000199', DocumentValidator::clean('12.345.678/0001-99'));
    }
}
