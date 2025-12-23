<?php

declare(strict_types=1);

namespace ItauBoletoPix\Utils;

/**
 * Helper para datas
 */
class DateHelper
{
    /**
     * Retorna primeiro dia do mês
     */
    public static function firstDayOfMonth(?\DateTimeImmutable $date = null): \DateTimeImmutable
    {
        $date = $date ?? new \DateTimeImmutable();

        return $date->modify('first day of this month')->setTime(0, 0, 0);
    }

    /**
     * Retorna último dia do mês
     */
    public static function lastDayOfMonth(?\DateTimeImmutable $date = null): \DateTimeImmutable
    {
        $date = $date ?? new \DateTimeImmutable();

        return $date->modify('last day of this month')->setTime(23, 59, 59);
    }

    /**
     * Verifica se é dia 01 do mês
     */
    public static function isFirstDayOfMonth(?\DateTimeImmutable $date = null): bool
    {
        $date = $date ?? new \DateTimeImmutable();

        return (int)$date->format('d') === 1;
    }

    /**
     * Adiciona dias úteis a uma data
     */
    public static function addBusinessDays(\DateTimeImmutable $date, int $days): \DateTimeImmutable
    {
        $direction = $days > 0 ? 1 : -1;
        $days = abs($days);

        while ($days > 0) {
            $date = $date->modify($direction > 0 ? '+1 day' : '-1 day');

            // Pula finais de semana
            $dayOfWeek = (int)$date->format('N');
            if ($dayOfWeek < 6) {
                $days--;
            }
        }

        return $date;
    }
}
