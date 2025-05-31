<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\PaymentMethod;
class SubscriptionController extends Controller
{
    public function initializePayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $user = auth()->user();
            
            // Create or retrieve Stripe customer
            $customer = $this->getOrCreateCustomer($user);
            
            // Create ephemeral key
            $ephemeralKey = EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2023-10-16']
            );

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => 999, // €9.99
                'currency' => 'eur',
                'customer' => $customer->id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'user_id' => $user->id
                ]
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $customer->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initialization error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Update user subscription status
            $user->update([
                'is_subscribed' => true,
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addMonth(),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Subscription confirmation error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getOrCreateCustomer($user)
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id
            ]
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

     public function processPayment(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $user = auth()->user();
            
            // Create Payment Method
            $paymentMethod = PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'number' => $request->cardData['cardNumber'],
                    'exp_month' => $request->cardData['expiryMonth'],
                    'exp_year' => $request->cardData['expiryYear'],
                    'cvc' => $request->cardData['cvc'],
                ],
                'billing_details' => [
                    'name' => $request->cardData['cardHolder'],
                ],
            ]);
            
            Log::info('Processing payment for user: ' . $user);

            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => 999, // €9.99 in cents
                'currency' => 'eur',
                'payment_method' => $paymentMethod->id,
                'confirm' => true,
                'return_url' => config('app.url') . '/payment/success',
                'metadata' => [
                    'user_id' => $user->id
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Update user subscription status
                $user->update([
                    'is_subscribed' => true,
                    'subscription_start_date' => now(),
                    'subscription_end_date' => now()->addMonth(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}