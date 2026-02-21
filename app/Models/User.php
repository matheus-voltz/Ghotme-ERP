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
use App\Traits\BelongsToCompany; // Import Trait
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes; // Use SoftDeletes

    const PLAN_STANDARD = 'padrao';
    const PLAN_ENTERPRISE = 'enterprise';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'parent_id',
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
        'billing_address',
        'city',
        'state',
        'zip_code',
        'niche',
        'chat_status',
        'expo_push_token',
        'telegram_chat_id',
        'telegram_username',
        'status',
        'deleted_reason',
        'notification_preferences',
    ];

    /**
     * Verifica se o período de teste expirou.
     */
    public function isTrialExpired()
    {
        // Se já tiver um plano ativo que não seja 'free', não expirou
        if (!empty($this->plan) && $this->plan !== self::PLAN_STANDARD && $this->plan !== 'free') {
            return false;
        }

        return $this->trial_ends_at && now()->greaterThan($this->trial_ends_at);
    }

    /**
     * Verifica se o usuário tem acesso a uma funcionalidade baseada no seu plano.
     */
    public function hasFeature($feature)
    {
        // Se o teste expirou, bloqueia todas as funcionalidades operacionais
        if ($this->isTrialExpired()) {
            return false;
        }

        $plan = strtolower($this->plan ?? self::PLAN_STANDARD);

        // Funcionalidades exclusivas do Enterprise
        $enterpriseFeatures = [
            'priority_support',
            'auto_supply',      // Robô de reposição
            'financial_bpo',    // BPO Financeiro
            'accountant_portal', // Portal do Contador
            'unlimited_invoices', // Emissão ilimitada
            'fleet_management',  // Gestão de frotas
            'advanced_b2b',      // B2B avançado
            'max_users_10'       // Até 10 usuários
        ];

        if (in_array($feature, $enterpriseFeatures)) {
            return $plan === self::PLAN_ENTERPRISE;
        }

        // Funcionalidades comuns a todos ou ao plano Padrão
        return true;
    }

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
        'two_factor_enabled',
    ];

    protected $attributes = [
        'role' => 'funcionario',
    ];

    public function getTwoFactorEnabledAttribute()
    {
        return ! is_null($this->two_factor_secret);
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return $this->defaultProfilePhotoUrl();
    }

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
            'notification_preferences' => 'json',
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
