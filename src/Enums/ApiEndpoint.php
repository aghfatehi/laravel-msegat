<?php

namespace Aghfatehi\Msegat\Enums;

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

    public function path(): string
    {
        return self::PATHS[$this->value];
    }
}
