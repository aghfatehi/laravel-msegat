<?php

namespace Aghfatehi\Msegat\Support;

/**
 * Utility for normalizing Saudi phone numbers to international format (+966).
 *
 * Handles local formats (05xxxxxxxx, 5xxxxxxxx) and ensures they
 * conform to the 9665xxxxxxxx pattern expected by Msegat.
 */
class PhoneNumberFormatter
{
    /**
     * Normalize a phone number to Msegat-compatible format (9665xxxxxxxx).
     *
     * Strips all non-digit characters and converts:
     * - 05xxxxxxxx -> 9665xxxxxxxx
     * - 5xxxxxxxx  -> 9665xxxxxxxx
     * - +9665xxxxxxxx -> 9665xxxxxxxx
     *
     * @param  string  $number  REQUIRED. The raw phone number in any common format.
     * @return string The normalized number (e.g. 966501234567).
     */
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
