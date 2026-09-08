<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'is_guest',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_guest' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Jika akun staf pertama kali dibuat (bukan guest dan belum punya role), otomatis beri role 'admin'
            if (! $user->is_guest && ! $user->customer_id) {
                $staffCount = static::where('is_guest', false)->whereNull('customer_id')->count();
                if ($staffCount <= 1 && $user->roles()->count() === 0) {
                    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
                    $user->roles()->syncWithoutDetaching([$adminRole->id]);
                }
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function activeShift(): ?Shift
    {
        return $this->shifts()
            ->whereNull('shift_close_time')
            ->latest('shift_open_time')
            ->first();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Jika user punya banyak role dan ingin cek beberapa sekaligus
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('kasir');
    }

    public function isKitchen(): bool
    {
        return $this->hasRole('kitchen');
    }

    public function scopeStaff($query)
    {
        return $query->where('is_guest', false)->whereNull('customer_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_guest || $this->customer_id !== null || $this->is_active === false) {
            return false;
        }

        return $this->hasAnyRole(['admin', 'owner', 'kasir', 'kitchen', 'superadmin']);
    }
}
