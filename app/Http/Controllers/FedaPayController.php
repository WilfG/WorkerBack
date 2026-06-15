<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use FedaPay\FedaPay;
use FedaPay\Transaction as FedaPayTransaction;

class FedaPayController extends Controller
{
    private $fedaPayApiKey;
    private $fedaPaySecretKey;
    private $fedaPayEnvironment;

    public function __construct()
    {
        $this->fedaPayApiKey = env('FEDAPAY_API_KEY');
        $this->fedaPaySecretKey = env('FEDAPAY_SECRET_KEY');
        $this->fedaPayEnvironment = env('FEDAPAY_ENVIRONMENT', 'production');
    }

    public function createTransaction(Request $request)
    {
        try {
            // Conditional validation based on payment method type
            $paymentMethodType = $request->input('payment_method.type');
            
            $rules = [
                'amount' => 'required|integer|min:100',
                'currency' => 'required|string',
                'description' => 'required|string',
                'customer.firstname' => 'required|string',
                'customer.lastname' => 'required|string',
                'customer.email' => 'required|email',
                'payment_method.type' => 'required|string|in:card,mobile_money',
                'callback_url' => 'required|url',
                'metadata' => 'array'
            ];

            // Add conditional validation for payment method
            if ($paymentMethodType === 'mobile_money') {
                $rules['customer.phone_number'] = 'required|string';
                $rules['payment_method.operator'] = 'required|string';
                $rules['payment_method.phone_number'] = 'required|string';
            }
            // For card payments, no additional validation needed - FedaPay will collect card details

            $validator = Validator::make($request->all(), $rules);

            Log::info('Creating FedaPay transaction request', $request->all());

            // Verify user exists
            $userId = $request->input('metadata.user_id');
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'Invalid user ID'
                ], 404);
            }

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Creating FedaPay transaction', [
                'user_id' => $userId,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'payment_method' => $paymentMethodType,
                'metadata' => $request->input('metadata', [])
            ]);

            // Create transaction on FedaPay with secret key
            FedaPay::setApiKey($this->fedaPaySecretKey);
            FedaPay::setEnvironment($this->fedaPayEnvironment);

            // Build transaction data based on payment method
            $transactionData = [
                'description' => $request->description,
                'amount' => $request->amount,
                'currency' => ['iso' => 'XOF'],
                'callback_url' => $request->callback_url,
                'customer' => [
                    'first_name' => $request->input('customer.firstname'),
                    'last_name' => $request->input('customer.lastname'),
                    'email' => $request->input('customer.email'),
                ]
            ];

            // Add phone number for mobile money
            if ($paymentMethodType === 'mobile_money') {
                $transactionData['customer']['phone_number'] = [
                    'number' => $request->input('customer.phone_number'),
                    'country' => 'bj'
                ];
                
                // Add payment method for mobile money
                $transactionData['payment_method'] = [
                    'type' => 'mobile_money',
                    'mobile_money' => [
                        'operator' => $request->input('payment_method.operator'),
                        'phone_number' => [
                            'number' => $request->input('payment_method.phone_number'),
                            'country' => 'bj'
                        ]
                    ]
                ];
            }
            // For card payments, don't add payment method to transaction creation
            // The checkout form will collect card details directly

            $transactionResponse = FedaPayTransaction::create($transactionData);

            if ($transactionResponse) {
                $token = $transactionResponse->generateToken();
                
                // Create subscription record (only one table now)
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'fedapay_transaction_id' => $transactionResponse->id,
                    'amount' => (int) $request->amount,
                    'currency' => htmlspecialchars($request->currency, ENT_QUOTES, 'UTF-8'),
                    'status' => htmlspecialchars($transactionResponse->status, ENT_QUOTES, 'UTF-8'),
                    'purpose' => htmlspecialchars($request->input('metadata.purpose', 'subscription'), ENT_QUOTES, 'UTF-8'),
                    'payment_method_type' => $paymentMethodType,
                    'metadata' => json_encode($request->input('metadata', [])),
                ]);

                Log::info('FedaPay transaction and subscription created', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $transactionResponse->id,
                    'amount' => $request->amount,
                    'payment_method' => $paymentMethodType,
                    'token' => $token
                ]);

                return response()->json([
                    'token' => $token,
                    'transaction_id' => $transactionResponse->id,
                    'public_key' => $this->fedaPayApiKey
                ]);
            }

        } catch (\FedaPay\Error\ApiConnection $e) {
            Log::error('FedaPay API Connection failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->input('metadata.user_id') ?? null
            ]);
            return response()->json([
                'message' => 'Payment service connection failed',
                'error' => $e->getMessage()
            ], 503);
        } catch (\FedaPay\Error\InvalidRequest $e) {
            Log::error('FedaPay Invalid Request', [
                'error' => $e->getMessage(),
                'user_id' => $request->input('metadata.user_id') ?? null
            ]);
            return response()->json([
                'message' => 'Invalid payment request',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('FedaPay transaction creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->input('metadata.user_id') ?? null
            ]);
            return response()->json([
                'message' => 'Transaction creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTransactionStatus($transactionId)
    {
        try {
            FedaPay::setApiKey($this->fedaPaySecretKey);
            FedaPay::setEnvironment($this->fedaPayEnvironment);

            $transaction = FedaPayTransaction::retrieve($transactionId);
            
            if (!$transaction) {
                return response()->json([
                    'message' => 'Transaction not found',
                    'status' => 'unknown'
                ], 404);
            }

            $status = htmlspecialchars($transaction->status, ENT_QUOTES, 'UTF-8');

            // Find and update subscription
            $subscription = Subscription::where('fedapay_transaction_id', $transactionId)->first();

            if ($subscription && $status === 'approved') {
                // Update subscription status
                $subscription->update(['status' => 'approved']);

                // Find user and update subscription fields
                $user = User::find($subscription->user_id);
                if ($user) {
                    $plan = $subscription->metadata ? json_decode($subscription->metadata, true)['plan'] : 'monthly';
                    
                    $endDate = now();
                    if ($plan === 'monthly') {
                        $endDate = $endDate->addMonth();
                    } elseif ($plan === 'halfyear') {
                        $endDate = $endDate->addMonths(6);
                    } elseif ($plan === 'yearly') {
                        $endDate = $endDate->addYear();
                    }

                    $user->update([
                        'is_subscribed' => 1,
                        'subscription_end_date' => $endDate
                    ]);

                    Log::info('User subscription activated', [
                        'user_id' => $user->id,
                        'plan' => $plan,
                        'end_date' => $endDate
                    ]);
                }
            }

            return response()->json([
                'status' => $status,
                'transaction_id' => $transactionId
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving transaction status', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error retrieving transaction status',
                'error' => $e->getMessage(),
                'status' => 'unknown'
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            Log::info('FedaPay Callback received', $request->all());

            $transactionId = $request->input('transaction.id');
            $status = $request->input('transaction.status');

            if (!$transactionId || !$status) {
                return response()->json([
                    'message' => 'Missing required fields'
                ], 400);
            }

            $subscription = Subscription::where('fedapay_transaction_id', $transactionId)->first();

            if (!$subscription) {
                Log::warning('Subscription not found for transaction', [
                    'transaction_id' => $transactionId
                ]);
                return response()->json([
                    'message' => 'Subscription not found'
                ], 404);
            }

            // Update subscription status
            $subscription->update(['status' => $status]);

            // If approved, activate user subscription
            if ($status === 'approved' && $subscription->purpose === 'subscription') {
                $user = User::find($subscription->user_id);
                if ($user) {
                    $plan = $subscription->metadata ? json_decode($subscription->metadata, true)['plan'] : 'monthly';
                    
                    $endDate = now();
                    if ($plan === 'monthly') {
                        $endDate = $endDate->addMonth();
                    } elseif ($plan === 'halfyear') {
                        $endDate = $endDate->addMonths(6);
                    } elseif ($plan === 'yearly') {
                        $endDate = $endDate->addYear();
                    }

                    $user->update([
                        'is_subscribed' => 1,
                        'subscription_end_date' => $endDate
                    ]);

                    Log::info('User subscription activated via webhook', [
                        'user_id' => $user->id,
                        'plan' => $plan,
                        'end_date' => $endDate
                    ]);
                }
            }

            return response()->json([
                'message' => 'Callback processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing callback', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error processing callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
