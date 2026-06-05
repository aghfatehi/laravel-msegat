<?php

namespace Aghfatehi\Msegat\Enums;

/**
 * Enumeration of Msegat API response codes with their human-readable messages.
 *
 * Use to programmatically interpret API responses.
 */
enum ResponseCode: string
{
    case Success = '1';
    case SuccessAlt = 'M0000';
    case VariablesMissing = 'M0001';
    case InvalidLogin = 'M0002';
    case ExceedSendersAllowed = 'M0022';
    case SenderInvalid = 'M0023';
    case SenderLang = 'M0024';
    case SenderLength = 'M0025';
    case SenderNotFound = 'M0026';
    case ActivationCodeInvalid = 'M0027';
    case LegacyVarsMissing = '1010';
    case LegacyInvalidLogin = '1020';
    case MsgEmpty = '1050';
    case InsufficientBalance = '1060';
    case MsgDuplicated = '1061';
    case FreeOtpInvalid = '1064';
    case SenderMissing = '1110';
    case InvalidNumbers = '1120';
    case MsgTooLong = '1140';
    case SenderNameInvalid = 'M0029';
    case SenderNameSuffix = 'M0030';
    case FileSizeExceeded = 'M0031';
    case FileTypeNotAllowed = 'M0032';
    case SenderTypeInvalid = 'M0033';
    case MethodNotAllowed = 'M0034';
    case NoSenders = 'M0036';

    /**
     * Determine if this code represents a successful API response.
     *
     * @return bool True if code is '1' or 'M0000'.
     */
    public function isSuccess(): bool
    {
        return in_array($this, [self::Success, self::SuccessAlt], true);
    }

    /**
     * Get a human-readable description of this response code.
     *
     * @return string
     */
    public function message(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::SuccessAlt => 'Success',
            self::VariablesMissing => 'Variables missing',
            self::InvalidLogin => 'Invalid login info',
            self::ExceedSendersAllowed => 'Exceed number of senders allowed',
            self::SenderInvalid => 'Sender name is active or under activation or refused',
            self::SenderLang => 'Sender name should be in English or number',
            self::SenderLength => 'Invalid sender name length',
            self::SenderNotFound => 'Sender name is already activated or not found',
            self::ActivationCodeInvalid => 'Activation code is not correct',
            self::LegacyVarsMissing => 'Variables missing',
            self::LegacyInvalidLogin => 'Invalid login info',
            self::MsgEmpty => 'MSG body is empty',
            self::InsufficientBalance => 'Balance is not enough',
            self::MsgDuplicated => 'MSG duplicated',
            self::FreeOtpInvalid => 'Free OTP: Invalid MSG content. Use "Pin Code is: xxxx" or upgrade your account',
            self::SenderMissing => 'Sender name is missing or incorrect',
            self::InvalidNumbers => 'Mobile numbers are not correct',
            self::MsgTooLong => 'MSG length is too long',
            self::SenderNameInvalid => 'Sender name should contain only letters and numbers (max 11 characters)',
            self::SenderNameSuffix => 'Sender name should end with AD',
            self::FileSizeExceeded => 'Maximum allowed file size is 5 MB',
            self::FileTypeNotAllowed => 'Only pdf, png, jpg and jpeg files are allowed',
            self::SenderTypeInvalid => 'Sender type should be normal or whitelist only',
            self::MethodNotAllowed => 'Please use POST method',
            self::NoSenders => 'There are no senders',
        };
    }
}
