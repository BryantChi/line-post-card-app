<?php

namespace App\Services\PaymentGateways;

use App\Models\RenewalOrder;

class BankTransferGateway extends AbstractPaymentGateway
{
    public function code(): string
    {
        return 'bank_transfer';
    }

    public function paymentMethodValue(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return '銀行轉帳';
    }

    public function isRedirect(): bool
    {
        return false;
    }

    public function extractOrderNo(array $postData): ?string
    {
        return null;
    }

    protected function parseVerifiedData(array $postData): array
    {
        throw new \LogicException('Bank transfer has no async notify callback.');
    }

    protected function successResponse(): string
    {
        return 'OK';
    }

    protected function failureResponse(string $reason): string
    {
        return $reason;
    }

    protected function sanitizeGatewayResponse(array $raw): array
    {
        return [];
    }
}
