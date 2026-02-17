<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Support\Feature;
use Illuminate\Http\Request;
use App\Models\LoyaltyChallenge;
use App\Http\Controllers\Controller;
use App\Models\CustomerPointTransaction;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Api\V1\LoyaltyBadgeResource;
use App\Http\Resources\Api\V1\LoyaltyChallengeResource;
use App\Http\Resources\Api\V1\CustomerLoyaltySummaryResource;

class CustomerLoyaltyController extends Controller
{
    public function summary(Request $request, Customer $customer): CustomerLoyaltySummaryResource
    {
        $this->ensureModuleEnabled();

        $customer->loadCount('orders');
        $customer->load(['challengeAwards' => fn ($query) => $query->latest('awarded_at')->with('challenge')->take(5)]);

        $challenges = LoyaltyChallenge::active()
            ->with(['progresses' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->orderBy('name')
            ->get();

        return new CustomerLoyaltySummaryResource([
            'customer' => [
                'id' => (string) $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'orders_count' => (int) $customer->orders_count,
                'last_order_at' => optional($customer->last_order_at)->toIso8601String(),
            ],
            'points' => [
                'balance' => (int) $customer->points,
                'lifetime_value' => (float) $customer->lifetime_value,
            ],
            'challenges' => LoyaltyChallengeResource::collection($challenges),
            'recent_badges' => LoyaltyBadgeResource::collection($customer->challengeAwards),
        ]);
    }

    public function challenges(Customer $customer)
    {
        $challenges = LoyaltyChallenge::active()
            ->with([
                'progresses' => fn ($q) =>
                    $q->where('customer_id', $customer->id),
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => LoyaltyChallengeResource::collection($challenges),
        ]);
    }

    public function transactions(Customer $customer)
    {
        return response()->json([
            'data' => CustomerPointTransaction::where('customer_id', $customer->id)
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn ($t) => [
                    'id' => (string) $t->id,
                    'type' => $t->points > 0 ? 'earn' : 'redeem',
                    'points' => abs($t->points),
                    'description' => $t->description,
                    'created_at' => $t->created_at->toIso8601String(),
                ]),
        ]);
    }



    protected function ensureModuleEnabled(): void
    {
        if (! Feature::enabled('loyalty')) {
            abort(Response::HTTP_NOT_FOUND, 'Loyalty module is disabled.');
        }
    }
}
