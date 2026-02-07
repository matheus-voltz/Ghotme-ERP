<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

use Illuminate\Support\Str;

class OrdemServico extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'uuid',
        'client_id',
        'veiculo_id',
        'user_id',
        'status',
        'description',
        'scheduled_at',
        'km_entry',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Vehicles::class, 'veiculo_id');
    }

    public function history()
    {
        return $this->hasOne(VehicleHistory::class, 'ordem_servico_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrdemServicoItem::class);
    }

    public function parts()
    {
        return $this->hasMany(\App\Models\OrdemServicoPart::class);
    }

    public function getTotalAttribute()
    {
        $servicesTotal = $this->items->sum(fn($i) => $i->price * $i->quantity);
        $partsTotal = $this->parts->sum(fn($p) => $p->price * $p->quantity);
        return $servicesTotal + $partsTotal;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
