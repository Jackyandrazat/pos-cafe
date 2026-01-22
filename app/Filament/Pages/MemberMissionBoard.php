<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\LoyaltyChallenge;
use App\Support\Feature;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MemberMissionBoard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?string $title = 'Board Misi Member';

    protected static string $view = 'filament.pages.member-mission-board';

    public ?array $filters = [
        'customer_id' => null,
    ];

    public ?array $selectedMember = null;

    public array $challengeCards = [];

    public array $recentBadges = [];

    public function mount(): void
    {
        abort_unless(Feature::enabled('loyalty'), 403);

        $this->filters ??= ['customer_id' => null];
        $this->loadMemberData($this->filters['customer_id']);
        $this->form->fill($this->filters);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::enabled('loyalty');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['admin', 'owner', 'superadmin', 'kasir']) ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Member')
                    ->searchable()
                    ->placeholder('Cari nama / nomor member')
                    ->options(fn () => $this->defaultCustomerOptions())
                    ->getSearchResultsUsing(fn (string $search) => $this->searchCustomers($search))
                    ->getOptionLabelUsing(fn ($value): ?string => $this->resolveCustomerLabel($value))
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadMemberData($state)),
            ])
            ->statePath('filters');
    }

    public function loadMemberData($customerId = null): void
    {
        $customerId = $customerId ?: ($this->filters['customer_id'] ?? null);

        $this->selectedMember = null;
        $this->challengeCards = [];
        $this->recentBadges = [];

        if (! $customerId) {
            $this->filters['customer_id'] = null;
            $this->form->fill($this->filters);

            return;
        }

        $customer = Customer::query()
            ->withCount('orders')
            ->with(['challengeAwards' => fn ($query) => $query->latest('awarded_at')->with('challenge')->limit(6)])
            ->find($customerId);

        if (! $customer) {
            $this->filters['customer_id'] = null;
            $this->form->fill($this->filters);

            return;
        }

        $this->filters['customer_id'] = $customer->id;
        $this->form->fill($this->filters);

        $this->selectedMember = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'points' => (int) $customer->points,
            'lifetime_value' => (float) $customer->lifetime_value,
            'orders_count' => (int) $customer->orders_count,
            'last_order_at' => optional($customer->last_order_at)->toIso8601String(),
        ];

        $challenges = LoyaltyChallenge::active()
            ->with(['progresses' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->orderBy('name')
            ->get();

        $this->challengeCards = $challenges->map(function (LoyaltyChallenge $challenge) {
            $progress = $challenge->progresses->first();
            $target = max((int) $challenge->target_value, 1);
            $current = (int) ($progress?->current_value ?? 0);
            $percentage = (int) min(100, round(($current / $target) * 100));

            return [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'description' => $challenge->description,
                'slug' => $challenge->slug,
                'target' => $target,
                'current' => $current,
                'percentage' => $percentage,
                'status' => $progress?->status ?? 'available',
                'badge' => [
                    'name' => $challenge->badge_name,
                    'color' => $challenge->badge_color ?? '#f97316',
                    'icon' => $challenge->badge_icon,
                    'points' => (int) $challenge->bonus_points,
                ],
                'window' => $progress?->window_start
                    ? sprintf('%s - %s',
                        $progress->window_start?->format('d M'),
                        $progress->window_end?->format('d M'))
                    : null,
                'completed_at' => optional($progress?->completed_at)->toIso8601String(),
                'rewarded_at' => optional($progress?->rewarded_at)->toIso8601String(),
            ];
        })->values()->toArray();

        $this->recentBadges = $customer->challengeAwards->map(fn ($award) => [
            'badge_name' => $award->badge_name,
            'badge_code' => $award->badge_code,
            'points_awarded' => (int) $award->points_awarded,
            'awarded_at' => optional($award->awarded_at)->toIso8601String(),
            'challenge' => $award->challenge?->name,
        ])->values()->toArray();
    }

    protected function defaultCustomerOptions(): array
    {
        return Customer::query()
            ->orderByDesc('last_order_at')
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (Customer $customer) => [$customer->id => $this->formatCustomerLabel($customer)])
            ->toArray();
    }

    protected function searchCustomers(string $search): array
    {
        return Customer::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (Customer $customer) => [$customer->id => $this->formatCustomerLabel($customer)])
            ->toArray();
    }

    protected function resolveCustomerLabel($value): ?string
    {
        if (! $value) {
            return null;
        }

        $customer = Customer::find($value);

        return $customer ? $this->formatCustomerLabel($customer) : null;
    }

    protected function formatCustomerLabel(Customer $customer): string
    {
        $parts = array_filter([$customer->name, $customer->phone, $customer->email]);

        return implode(' • ', array_slice($parts, 0, 2));
    }

    public function getMemberDetailUrlProperty(): ?string
    {
        if (! $this->selectedMember) {
            return null;
        }

        return CustomerResource::getUrl('edit', ['record' => $this->selectedMember['id']]);
    }
}
