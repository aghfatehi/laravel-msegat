<?php

namespace Aghfatehi\Msegat\Enums;

/**
 * Enumeration of all supported Msegat API endpoints.
 *
 * Each case maps to a logical operation and provides the actual API script path.
 */
enum ApiEndpoint: string
{
    case Send = 'send';
    case SendPersonalized = 'send_personalized';
    case SendOtp = 'send_otp';
    case VerifyOtp = 'verify_otp';
    case AddSender = 'add_sender';
    case GetSenders = 'get_senders';
    case GetMessages = 'get_messages';
    case CalculateCost = 'calculate_cost';
    case Balance = 'balance';

    private const PATHS = [
        'send' => 'sendsms.php',
        'send_personalized' => 'sendVars.php',
        'send_otp' => 'sendOTPCode.php',
        'verify_otp' => 'verifyOTPCode.php',
        'add_sender' => 'addSender.php',
        'get_senders' => 'senders.php',
        'get_messages' => 'getMessages.php',
        'calculate_cost' => 'calculateCost.php',
        'balance' => 'Credits.php',
    ];

    /**
     * Get the actual API script path for this endpoint.
     *
     * @return string Relative script path (e.g. 'sendsms.php').
     */
    public function path(): string
    {
        return self::PATHS[$this->value];
    }
}
