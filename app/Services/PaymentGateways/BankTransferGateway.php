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

    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        return 'manual';
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        // 銀行轉帳無線上退款 API,線上退款一律擋下,改由 RefundService 走純人工記錄路徑
        throw new \LogicException('Bank transfer refund must be recorded manually, not via gateway.');
    }
}
