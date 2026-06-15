<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Update user subscription based on payment status
     */
    public static function updateSubscription(Transaction $transaction): void
    {
        if ($transaction->purpose !== 'subscription' || $transaction->status !== 'approved') {
            return;
        }

        $user = $transaction->user;
        if (!$user) {
            Log::warning('User not found for transaction', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id
            ]);
            return;
        }

        // Calculate subscription expiry date
        $currentExpiry = $user->subscription_end_date;
        $newExpiry = $currentExpiry && $currentExpiry->isFuture()
            ? $currentExpiry->addMonth()
            : now()->addMonth();

        $user->update([
            'is_subscribed' => true,
            'subscription_end_date' => $newExpiry
        ]);

        Log::info('User subscription updated', [
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'end_date' => $newExpiry
        ]);
    }

    /**
     * Check if user subscription has expired and update status
     */
    public static function checkExpiredSubscriptions(): void
    {
        $expiredUsers = User::where('is_subscribed', true)
            ->where('subscription_end_date', '<', now())
            ->get();

        foreach ($expiredUsers as $user) {
            $user->update([
                'is_subscribed' => false,
                'subscription_end_date' => null
            ]);

            Log::info('User subscription expired', [
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Get subscription pricing
     */
    public static function getSubscriptionPricing(): array
    {
        return [
            'monthly' => [
                'amount' => 500, // 5000 XOF in centimes
                'currency' => 'XOF',
                'duration' => '1 month',
                'features' => [
                    'Apply to unlimited jobs',
                    'Priority support',
                    'Featured profile',
                    'Advanced analytics'
                ]
            ]
        ];
    }

    /**
     * Calculate subscription amount in minor currency unit
     */
    public static function calculateAmount(string $currency = 'XOF'): int
    {
        $pricing = self::getSubscriptionPricing();
        return $pricing['monthly']['amount'];
    }
}
