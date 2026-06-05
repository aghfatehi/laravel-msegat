<?php

namespace Aghfatehi\Msegat\Support;

class PhoneNumberFormatter
{
    public static function format(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (preg_match('/^05(\d{8})$/', $cleaned)) {
            return '966'.substr($cleaned, 1);
        }

        if (preg_match('/^5(\d{8})$/', $cleaned)) {
            return '966'.$cleaned;
        }

        if (preg_match('/^9665\d{8}$/', $cleaned)) {
            return $cleaned;
        }

        if (preg_match('/^\+?9665\d{8}$/', $cleaned)) {
            return ltrim($cleaned, '+');
        }

        return $cleaned;
    }
}
