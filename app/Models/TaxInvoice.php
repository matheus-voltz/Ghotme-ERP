<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class TaxInvoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'ordem_servico_id',
        'invoice_number',
        'amount',
        'xml_path',
        'pdf_path',
        'status', // 'issued', 'cancelled', 'error'
        'issued_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}
