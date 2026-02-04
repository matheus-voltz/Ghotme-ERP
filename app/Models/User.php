<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordCustom;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailCustom;


class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf_cnpj',
        'password',
        'company',
        'country',
        'plan',
        'plan_type',
        'selected_plan',
        'trial_ends_at',
        'role',
        'contact_number',
        'billing_address',
        'city',
        'state',
        'zip_code',
    ];

    public function billingHistory()
    {
        return $this->hasMany(BillingHistory::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

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
            'trial_ends_at' => 'datetime',
        ];
    }

     public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordCustom($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom());
    }
}
