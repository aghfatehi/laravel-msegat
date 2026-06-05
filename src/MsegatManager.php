<?php

namespace Aghfatehi\Msegat;

use Aghfatehi\Msegat\DTOs\BalanceResponse;
use Aghfatehi\Msegat\DTOs\OtpResponse;
use Aghfatehi\Msegat\DTOs\SmsResponse;
use Aghfatehi\Msegat\Events\MessageSending;
use Aghfatehi\Msegat\Events\MessageSent;
use Aghfatehi\Msegat\Events\OtpGenerated;
use Aghfatehi\Msegat\Events\OtpVerified;
use Aghfatehi\Msegat\Exceptions\ValidationException;
use Aghfatehi\Msegat\Support\PhoneNumberFormatter;
use Carbon\Carbon;

/**
 * Fluent manager for Msegat SMS, OTP, and WhatsApp operations.
 *
 * Provides a builder-style interface to configure and send messages,
 * manage OTP flows, check balance, and retrieve sender lists.
 */
class MsegatManager
{
    /** The HTTP client used to communicate with Msegat API. */
    private ?MsegatClient $client = null;

    /** @var array<int,string> Recipient phone number(s). */
    private array $numbers = [];

    /** The SMS message body. */
    private ?string $message = null;

    /** The sender name (approved on Msegat). Falls back to config. */
    private ?string $sender = null;

    /** Message encoding: 'UTF8' (default) or 'UCS2'. */
    private string $encoding = 'UTF8';

    /** Scheduling mode: 'now' (default) or 'later'. */
    private string $timeToSend = 'now';

    /** Scheduled send datetime (Y-m-d H:i:s) when timeToSend is 'later'. */
    private ?string $exactTime = null;

    /** Whether to request a bulk ID from the API. */
    private bool $requestBulkId = false;

    /** Whether to filter duplicate messages (default true). */
    private bool $filterDuplicates = true;

    /** Language code for OTP messages (e.g. 'En', 'Ar'). */
    private ?string $otpLanguage = null;

    /** WhatsApp template name. */
    private ?string $whatsAppTemplate = null;

    /** @var array<string,mixed> Variables for the WhatsApp template. */
    private array $whatsAppVariables = [];

    /** Current mode: 'sms', 'otp', or 'whatsapp'. */
    private string $mode = 'sms';

    /**
     * Set mode to SMS (default).
     *
     * @return $this
     */
    public function sms(): self
    {
        $this->mode = 'sms';

        return $this;
    }

    /**
     * Set mode to OTP (one-time password).
     *
     * @return $this
     */
    public function otp(): self
    {
        $this->mode = 'otp';

        return $this;
    }

    /**
     * Set mode to WhatsApp messaging (currently limited support).
     *
     * @return $this
     */
    public function whatsapp(): self
    {
        $this->mode = 'whatsapp';

        return $this;
    }

    /**
     * Set the recipient number(s).
     *
     * @param  string|array<int,string>  $numbers  Single number or array of numbers.
     * @return $this
     */
    public function to(string|array $numbers): self
    {
        $this->numbers = is_array($numbers) ? $numbers : [$numbers];

        return $this;
    }

    /**
     * Set the message body.
     *
     * @param  string  $message  REQUIRED. The SMS text content.
     * @return $this
     */
    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the sender name (must be approved on your Msegat account).
     *
     * @param  string  $sender  OPTIONAL. Falls back to config('msegat.sender').
     * @return $this
     */
    public function sender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Set the message encoding.
     *
     * @param  string  $encoding  OPTIONAL. 'UTF8' (default) or 'UCS2'. UCS2 needed for Arabic/Unicode.
     * @return $this
     */
    public function encoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Schedule the message for a future date/time.
     *
     * @param  string|Carbon  $at  OPTIONAL. DateTime string (Y-m-d H:i:s) or Carbon instance.
     * @return $this
     *
     * @throws ValidationException If format is invalid.
     */
    public function at(string|Carbon $at): self
    {
        if ($at instanceof Carbon) {
            $at = $at->format('Y-m-d H:i:s');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $at)) {
            throw new ValidationException('Invalid datetime format. Use Y-m-d H:i:s.');
        }

        $this->timeToSend = 'later';
        $this->exactTime = $at;

        return $this;
    }

    /**
     * Set additional API options.
     *
     * @param  array<string,mixed>  $options  OPTIONAL. Keys: 'msgEncoding', 'reqBulkId', 'reqFilter'.
     * @return $this
     */
    public function options(array $options): self
    {
        if (isset($options['msgEncoding'])) {
            $this->encoding = $options['msgEncoding'];
        }
        if (isset($options['reqBulkId'])) {
            $this->requestBulkId = (bool) $options['reqBulkId'];
        }
        if (isset($options['reqFilter'])) {
            $this->filterDuplicates = (bool) $options['reqFilter'];
        }

        return $this;
    }

    /**
     * Set the OTP language.
     *
     * @param  string  $language  OPTIONAL. 'En' for English, 'Ar' for Arabic.
     * @return $this
     */
    public function lang(string $language): self
    {
        $this->otpLanguage = $language;

        return $this;
    }

    /**
     * Set the WhatsApp template name.
     *
     * @param  string  $template  REQUIRED for WhatsApp mode.
     * @return $this
     */
    public function template(string $template): self
    {
        $this->whatsAppTemplate = $template;

        return $this;
    }

    /**
     * Set variables to interpolate into a WhatsApp template.
     *
     * @param  array<string,mixed>  $variables  OPTIONAL. Key-value pairs for template placeholders.
     * @return $this
     */
    public function variables(array $variables): self
    {
        $this->whatsAppVariables = $variables;

        return $this;
    }

    /**
     * Send the SMS message immediately.
     *
     * @return SmsResponse The API response with success status, message, and optional bulkId.
     *
     * @throws ValidationException If numbers or message are missing.
     */
    public function send(): SmsResponse
    {
        if ($this->mode === 'whatsapp') {
            $result = new SmsResponse(
                successful: false,
                code: 'M0099',
                message: 'WhatsApp API endpoints are not yet documented by Msegat. Use SMS or OTP instead.',
            );

            $this->reset();

            return $result;
        }

        $this->validateForSend();

        $normalized = array_map(
            fn ($n) => PhoneNumberFormatter::format($n),
            $this->numbers
        );

        $data = [
            'numbers' => implode(',', $normalized),
            'userSender' => $this->resolveSender(),
            'msg' => $this->message,
            'msgEncoding' => $this->encoding,
            'reqBulkId' => $this->requestBulkId,
            'reqFilter' => $this->filterDuplicates,
        ];

        if ($this->timeToSend === 'later' && $this->exactTime) {
            $data['timeToSend'] = 'later';
            $data['exactTime'] = $this->exactTime;
        }

        MessageSending::dispatch($data);

        $response = $this->getClient()->send($data);

        $result = SmsResponse::fromApiResponse($response, $this->requestBulkId);

        MessageSent::dispatch($result);

        $this->reset();

        return $result;
    }

    /**
     * Queue the SMS message for async delivery via Laravel queue.
     *
     * @param  string|null  $connection  OPTIONAL. Queue connection name.
     * @param  string|null  $queue  OPTIONAL. Queue name.
     *
     * @throws ValidationException If numbers or message are missing.
     */
    public function queue(?string $connection = null, ?string $queue = null): void
    {
        $this->validateForSend();

        $normalized = array_map(
            fn ($n) => PhoneNumberFormatter::format($n),
            $this->numbers
        );

        $data = [
            'numbers' => implode(',', $normalized),
            'userSender' => $this->resolveSender(),
            'msg' => $this->message,
            'msgEncoding' => $this->encoding,
            'reqBulkId' => $this->requestBulkId,
            'reqFilter' => $this->filterDuplicates,
        ];

        if ($this->timeToSend === 'later' && $this->exactTime) {
            $data['timeToSend'] = 'later';
            $data['exactTime'] = $this->exactTime;
        }

        $job = new Jobs\SendSmsJob($data);

        if ($connection) {
            $job->onConnection($connection);
        }
        if ($queue) {
            $job->onQueue($queue);
        }

        dispatch($job);

        $this->reset();
    }

    /**
     * Send personalized (variable-based) SMS messages.
     *
     * Each recipient gets a message with their own variable values interpolated.
     * The count of $vars arrays must match the count of recipient numbers.
     *
     * @param  array<int,array<string,string>>  $vars  REQUIRED. Array of variable maps, one per recipient.
     * @return SmsResponse
     *
     * @throws ValidationException If numbers/message missing or vars count mismatched.
     */
    public function sendPersonalized(array $vars): SmsResponse
    {
        if (empty($this->numbers)) {
            throw new ValidationException('Recipient numbers are required.');
        }
        if (empty($this->message)) {
            throw new ValidationException('Message body is required.');
        }
        if (count($this->numbers) !== count($vars)) {
            throw new ValidationException('Variables count must match numbers count.');
        }

        $normalized = array_map(
            fn ($n) => PhoneNumberFormatter::format($n),
            $this->numbers
        );

        $data = [
            'numbers' => implode(',', $normalized),
            'userSender' => $this->resolveSender(),
            'msg' => $this->message,
            'msgEncoding' => $this->encoding,
            'reqBulkId' => $this->requestBulkId,
            'reqFilter' => $this->filterDuplicates,
            'vars' => $vars,
        ];

        MessageSending::dispatch($data);

        $response = $this->getClient()->sendPersonalized($data);
        $result = SmsResponse::fromApiResponse($response, $this->requestBulkId);

        MessageSent::dispatch($result);

        $this->reset();

        return $result;
    }

    /**
     * Send an OTP (one-time password) to the recipient.
     *
     * Only the first number in the recipients list is used.
     *
     * @return OtpResponse
     *
     * @throws ValidationException If no recipient number is set.
     */
    public function sendOtp(): OtpResponse
    {
        if (empty($this->numbers)) {
            throw new ValidationException('Recipient number is required for OTP.');
        }

        $number = PhoneNumberFormatter::format($this->numbers[0]);

        $data = [
            'number' => $number,
            'userSender' => $this->resolveSender(),
            'lang' => $this->otpLanguage ?? 4 < config('msegat.otp.code_length') ? 'En' : 'Ar',
        ];

        $response = $this->getClient()->sendOtp($data);
        $result = OtpResponse::fromApiResponse($response);

        OtpGenerated::dispatch($number, $result);

        $this->reset();

        return $result;
    }

    /**
     * Verify an OTP code submitted by the user.
     *
     * @param  string  $code  REQUIRED. The OTP code to verify.
     * @return OtpResponse
     *
     * @throws ValidationException If no recipient number is set.
     */
    public function verifyOtp(string $code): OtpResponse
    {
        if (empty($this->numbers)) {
            throw new ValidationException('Recipient number is required for OTP verification.');
        }

        $number = PhoneNumberFormatter::format($this->numbers[0]);

        $data = [
            'number' => $number,
            'code' => $code,
            'lang' => $this->otpLanguage ?? 'En',
        ];

        $response = $this->getClient()->verifyOtp($data);
        $result = OtpResponse::fromApiResponse($response);

        OtpVerified::dispatch($number, $code, $result);

        $this->reset();

        return $result;
    }

    /**
     * Check the Msegat account balance (remaining SMS credits).
     *
     * @return BalanceResponse
     */
    public function getBalance(): BalanceResponse
    {
        $raw = $this->getClient()->getBalance();
        $result = BalanceResponse::fromRawResponse($raw);

        $this->reset();

        return $result;
    }

    /**
     * Retrieve all registered sender names on the Msegat account.
     *
     * @return array The raw API response containing sender list.
     */
    public function getSenders(): array
    {
        $response = $this->getClient()->getSenders();
        $this->reset();

        return $response;
    }

    /**
     * Get delivery report for a previously sent bulk message.
     *
     * @param  string  $bulkId  REQUIRED. The bulk ID returned from a send operation.
     * @param  int  $page  OPTIONAL. Page number for paginated results (default 1).
     * @param  int|null  $limit  OPTIONAL. Results per page.
     * @return array The raw API response with message statuses.
     */
    public function getMessages(string $bulkId, int $page = 1, ?int $limit = null): array
    {
        $filters = [
            'reqBulkId' => $bulkId,
            'pageNumber' => $page,
        ];
        if ($limit) {
            $filters['limit'] = $limit;
        }

        $response = $this->getClient()->getMessages($filters);
        $this->reset();

        return $response;
    }

    /**
     * Calculate the cost of sending a message to the configured recipients.
     *
     * @return float The estimated cost as a float.
     *
     * @throws ValidationException If numbers or message are missing.
     */
    public function calculateCost(): float
    {
        if (empty($this->numbers) || empty($this->message)) {
            throw new ValidationException('Numbers and message are required to calculate cost.');
        }

        $normalized = array_map(
            fn ($n) => PhoneNumberFormatter::format($n),
            $this->numbers
        );

        $raw = $this->getClient()->calculateCost([
            'contactType' => 'numbers',
            'contacts' => implode(',', $normalized),
            'msg' => $this->message,
            'By' => 'Link',
            'msgEncoding' => 'UTF8',
        ]);

        $this->reset();

        return (float) str_replace(',', '.', $raw);
    }

    /**
     * Send a quick test message using default sender 'OTP'.
     *
     * @return SmsResponse
     */
    public function sendTestMessage(): SmsResponse
    {
        $this->sender('OTP');
        $this->message('Verification Code: xxxx');

        return $this->send();
    }

    /**
     * Enable bulk ID tracking for this message.
     *
     * @param  string  $bulkId  OPTIONAL. The bulk ID is obtained from the API response.
     * @return $this
     */
    public function forBulkId(string $bulkId): self
    {
        $this->requestBulkId = true;

        return $this;
    }

    /**
     * Validate that required fields (numbers, message) are present before sending.
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function validateForSend(): void
    {
        if (empty($this->numbers)) {
            throw new ValidationException('At least one recipient number is required.');
        }
        if (empty($this->message)) {
            throw new ValidationException('Message body is required.');
        }
    }

    /**
     * Resolve the sender name: use explicitly set sender or fall back to config default.
     *
     * @return string
     */
    private function resolveSender(): string
    {
        return $this->sender ?: config('msegat.sender', '');
    }

    /**
     * Inject a custom MsegatClient instance (useful for testing).
     *
     * @param  MsegatClient  $client  The client instance to use.
     * @return $this
     */
    public function setClient(MsegatClient $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the MsegatClient instance, creating a default one if none was injected.
     *
     * @return MsegatClient
     */
    public function getClient(): MsegatClient
    {
        if (!$this->client) {
            $this->client = new MsegatClient;
        }

        return $this->client;
    }

    /**
     * Reset all builder properties to their defaults for the next use.
     *
     * @return void
     */
    private function reset(): void
    {
        $this->numbers = [];
        $this->message = null;
        $this->sender = null;
        $this->encoding = 'UTF8';
        $this->timeToSend = 'now';
        $this->exactTime = null;
        $this->requestBulkId = false;
        $this->filterDuplicates = true;
        $this->otpLanguage = null;
        $this->whatsAppTemplate = null;
        $this->whatsAppVariables = [];
        $this->mode = 'sms';
    }
}
