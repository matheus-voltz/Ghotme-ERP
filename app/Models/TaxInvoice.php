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
        'invoice_type',
        'status',
        'number',
        'series',
        'access_key',
        'total_amount',
        'tax_amount',
        'xml_url',
        'pdf_url',
        'error_message',
        'issued_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2'
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }
}
