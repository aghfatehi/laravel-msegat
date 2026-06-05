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

class MsegatManager
{
    private ?MsegatClient $client = null;

    private array $numbers = [];

    private ?string $message = null;

    private ?string $sender = null;

    private string $encoding = 'UTF8';

    private string $timeToSend = 'now';

    private ?string $exactTime = null;

    private bool $requestBulkId = false;

    private bool $filterDuplicates = true;

    private ?string $otpLanguage = null;

    private ?string $whatsAppTemplate = null;

    private array $whatsAppVariables = [];

    private string $mode = 'sms';

    public function sms(): self
    {
        $this->mode = 'sms';

        return $this;
    }

    public function otp(): self
    {
        $this->mode = 'otp';

        return $this;
    }

    public function whatsapp(): self
    {
        $this->mode = 'whatsapp';

        return $this;
    }

    public function to(string|array $numbers): self
    {
        $this->numbers = is_array($numbers) ? $numbers : [$numbers];

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function sender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function encoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function at(string|Carbon $at): self
    {
        if ($at instanceof Carbon) {
            $at = $at->format('Y-m-d H:i:s');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $at)) {
            throw new ValidationException('Invalid datetime format. Use Y-m-d H:i:s.');
        }

        $this->timeToSend = 'later';
        $this->exactTime = $at;

        return $this;
    }

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

    public function lang(string $language): self
    {
        $this->otpLanguage = $language;

        return $this;
    }

    public function template(string $template): self
    {
        $this->whatsAppTemplate = $template;

        return $this;
    }

    public function variables(array $variables): self
    {
        $this->whatsAppVariables = $variables;

        return $this;
    }

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

    public function queue(string $connection = null, string $queue = null): void
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

    public function sendOtp(): OtpResponse
    {
        if (empty($this->numbers)) {
            throw new ValidationException('Recipient number is required for OTP.');
        }

        $number = PhoneNumberFormatter::format($this->numbers[0]);

        $data = [
            'number' => $number,
            'userSender' => $this->resolveSender(),
            'lang' => $this->otpLanguage ?? config('msegat.otp.code_length') > 4 ? 'En' : 'Ar',
        ];

        $response = $this->getClient()->sendOtp($data);
        $result = OtpResponse::fromApiResponse($response);

        OtpGenerated::dispatch($number, $result);

        $this->reset();

        return $result;
    }

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

    public function getBalance(): BalanceResponse
    {
        $raw = $this->getClient()->getBalance();
        $result = BalanceResponse::fromRawResponse($raw);

        $this->reset();

        return $result;
    }

    public function getSenders(): array
    {
        $response = $this->getClient()->getSenders();
        $this->reset();

        return $response;
    }

    public function getMessages(string $bulkId, int $page = 1, int $limit = null): array
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

    public function sendTestMessage(): SmsResponse
    {
        $this->sender('OTP');
        $this->message('Verification Code: xxxx');

        return $this->send();
    }

    public function forBulkId(string $bulkId): self
    {
        $this->requestBulkId = true;

        return $this;
    }

    private function validateForSend(): void
    {
        if (empty($this->numbers)) {
            throw new ValidationException('At least one recipient number is required.');
        }
        if (empty($this->message)) {
            throw new ValidationException('Message body is required.');
        }
    }

    private function resolveSender(): string
    {
        return $this->sender ?: config('msegat.sender', '');
    }

    private function getClient(): MsegatClient
    {
        if (! $this->client) {
            $this->client = new MsegatClient;
        }

        return $this->client;
    }

    private function reset(): void
    {
        $this->client = null;
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
