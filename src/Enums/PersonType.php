<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de pessoa
 * Person type
 *
 * Campo API: codigo_tipo_pessoa
 */
enum PersonType: string
{
    /**
     * Pessoa física (CPF)
     * Individual person (CPF)
     */
    case INDIVIDUAL = 'F';

    /**
     * Pessoa jurídica (CNPJ)
     * Legal entity / Company (CNPJ)
     */
    case COMPANY = 'J';
}
