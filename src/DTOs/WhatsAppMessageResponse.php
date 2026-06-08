<?php

namespace Aghfatehi\Msegat\DTOs;

/**
 * Data transfer object representing the result of a WhatsApp message send operation
 * via the T2 Communicate API.
 *
 * @see https://t2techdetails.docs.apiary.io/
 */
readonly class WhatsAppMessageResponse
{
    /**
     * @param  bool  $successful  Whether the message was accepted by the API.
     * @param  string  $messageId  Unique message identifier for status tracking.
     * @param  string  $conversationId  Conversation identifier.
     * @param  string  $contactNumber  Recipient phone number.
     * @param  string  $status  Message status (Sent, Delivered, Read, Failed, etc.).
     * @param  string|null  $contactName  OPTIONAL. Contact display name (null if unsaved).
     * @param  string  $message  Human-readable result description.
     * @param  array<string,mixed>  $raw  OPTIONAL. Raw API response for debugging.
     */
    public function __construct(
        public bool $successful,
        public string $messageId,
        public string $conversationId,
        public string $contactNumber,
        public string $status,
        public ?string $contactName = null,
        public string $message = '',
        public array $raw = [],
    ) {
    }

    /**
     * Create a WhatsAppMessageResponse from the T2 API's JSON-decoded response array.
     *
     * @param  array<string,mixed>  $response  The raw API response from /message/send-custom.
     */
    public static function fromApiResponse(array $response): self
    {
        $status = $response['status'] ?? 'Failed';
        $successful = $status !== 'Failed' && $status !== '';

        return new self(
            successful: $successful,
            messageId: $response['messageId'] ?? '',
            conversationId: $response['id'] ?? '',
            contactNumber: $response['contactNumber'] ?? '',
            status: $status,
            contactName: $response['contactName'] ?? null,
            message: $successful
                ? 'WhatsApp message queued successfully'
                : ($response['message'] ?? $response['Message'] ?? 'Unknown WhatsApp API error'),
            raw: $response,
        );
    }
}
