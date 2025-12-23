<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit;

use ItauBoletoPix\Models\{Address, LegalPerson, PhysicalPerson};
use PHPUnit\Framework\TestCase;

/**
 * Testes para modelos de domínio
 */
class ModelTest extends TestCase
{
    public function testAddressCreation(): void
    {
        $address = new Address(
            street: 'Av Paulista, 1000',
            neighborhood: 'Bela Vista',
            city: 'São Paulo',
            state: 'SP',
            zipCode: '01310-100'
        );

        $this->assertEquals('SP', $address->getState());
        $this->assertEquals('01310100', $address->getZipCode());
        $this->assertStringContainsString('Av Paulista', $address->format());
    }

    public function testPhysicalPersonValidation(): void
    {
        $address = new Address('Rua X', 'Bairro Y', 'Cidade', 'SP', '12345678');

        $person = new PhysicalPerson(
            name: 'João Silva',
            document: '123.456.789-00',
            address: $address
        );

        $this->assertEquals('João Silva', $person->getName());
        $this->assertEquals('F', $person->getDocumentType());
    }

    public function testLegalPersonValidation(): void
    {
        $address = new Address('Rua X', 'Bairro Y', 'Cidade', 'SP', '12345678');

        $company = new LegalPerson(
            name: 'Empresa LTDA',
            document: '12.345.678/0001-99',
            address: $address
        );

        $this->assertEquals('Empresa LTDA', $company->getName());
        $this->assertEquals('J', $company->getDocumentType());
    }
}
