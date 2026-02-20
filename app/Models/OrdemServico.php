<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;
use App\Traits\HasCustomFields;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

use Illuminate\Support\Str;

class OrdemServico extends Model implements Auditable
{
    use BelongsToCompany, AuditableTrait, HasCustomFields;

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

    public function inspection()
    {
        return $this->hasOne(VehicleInspection::class, 'ordem_servico_id');
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

    public function getPartsCostTotalAttribute()
    {
        return $this->parts->sum(function($p) {
            // Se a peça não tiver custo, assume 0
            return ($p->part->cost_price ?? 0) * $p->quantity;
        });
    }

    public function getGrossProfitAttribute()
    {
        return $this->total - $this->parts_cost_total;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->total <= 0) return 0;
        return ($this->gross_profit / $this->total) * 100;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
