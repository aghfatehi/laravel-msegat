<?php

namespace Aghfatehi\Msegat\Facades;

use Aghfatehi\Msegat\DTOs\BalanceResponse;
use Aghfatehi\Msegat\DTOs\OtpResponse;
use Aghfatehi\Msegat\DTOs\SmsResponse;
use Aghfatehi\Msegat\MsegatManager;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for the Msegat SMS manager.
 *
 * Provides a static interface to MsegatManager methods.
 *
 * @method static MsegatManager sms()
 * @method static MsegatManager otp()
 * @method static MsegatManager whatsapp()
 * @method static MsegatManager to(string|array $numbers)
 * @method static MsegatManager message(string $message)
 * @method static MsegatManager sender(string $sender)
 * @method static MsegatManager encoding(string $encoding)
 * @method static MsegatManager at(string|\Carbon\Carbon $at)
 * @method static MsegatManager options(array $options)
 * @method static MsegatManager lang(string $language)
 * @method static MsegatManager template(string $template)
 * @method static MsegatManager variables(array $variables)
 * @method static SmsResponse send()
 * @method static void queue(string $connection = null, string $queue = null)
 * @method static SmsResponse sendPersonalized(array $vars)
 * @method static OtpResponse sendOtp()
 * @method static OtpResponse verifyOtp(string $code)
 * @method static BalanceResponse getBalance()
 * @method static array getSenders()
 * @method static array getMessages(string $bulkId, int $page = 1, ?int $limit = null)
 * @method static float calculateCost()
 * @method static SmsResponse sendTestMessage()
 * @method static MsegatManager forBulkId(string $bulkId)
 * @see \Aghfatehi\Msegat\MsegatManager
 */
class Msegat extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'msegat';
    }
}
