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
        $this->ensureMemberAccess($request, $customer);

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

    public function challenges(Request $request, Customer $customer)
    {
        $this->ensureMemberAccess($request, $customer);

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

    public function transactions(Request $request, Customer $customer)
    {
        $this->ensureMemberAccess($request, $customer);

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

    public function rewards(Request $request, Customer $customer)
    {
        $this->ensureMemberAccess($request, $customer);

        $awards = $customer->challengeAwards()
            ->with('challenge')
            ->latest('awarded_at')
            ->get();

        $iconMap = [
            'badge_loyal_regular' => '🏆',
            'badge_menu_explorer' => '🧭',
        ];

        return response()->json([
            'data' => $awards->map(fn ($award) => [
                'id' => (string) $award->id,
                'program_id' => (string) ($award->loyalty_challenge_id ?? '2'),
                'name' => $award->badge_name ?? 'Reward',
                'description' => $award->challenge?->description ?? ('Diberikan pada ' . $award->awarded_at->translatedFormat('d M Y')),
                'points_required' => 0,
                'icon' => $iconMap[$award->badge_code] ?? ($award->badge_icon ?: '🏆'),
                'is_available' => true,
            ])
        ]);
    }

    protected function ensureMemberAccess(Request $request, Customer $customer): void
    {
        $this->ensureModuleEnabled();

        $user = $request->user();
        if (! $user || $user->is_guest) {
            abort(Response::HTTP_FORBIDDEN, 'Fitur loyalti hanya tersedia untuk Member terdaftar.');
        }

        $isStaff = $user->hasAnyRole(['admin', 'kasir', 'manajer', 'supervisor', 'barista', 'kitchen']);
        if (! $isStaff && (int) $user->customer_id !== (int) $customer->id) {
            abort(Response::HTTP_FORBIDDEN, 'Akses loyalti tidak sah.');
        }
    }

    protected function ensureModuleEnabled(): void
    {
        if (! Feature::enabled('loyalty')) {
            abort(Response::HTTP_NOT_FOUND, 'Loyalty module is disabled.');
        }
    }
}
