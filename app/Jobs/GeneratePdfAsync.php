<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePdfAsync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $pdfType,
        private int $recordId,
        private int $userId
    ) {}

    public function handle(): void
    {
        try {
            $pdf = match($this->pdfType) {
                'ordem_servico' => $this->generateOSPdf(),
                'budget' => $this->generateBudgetPdf(),
                'invoice' => $this->generateInvoicePdf(),
                default => null
            };

            if ($pdf) {
                // Salvar em storage e notificar usuário
                \Illuminate\Support\Facades\Storage::put("pdfs/{$this->pdfType}_{$this->recordId}.pdf", $pdf);
                
                \App\Models\User::find($this->userId)?->notify(
                    new \App\Notifications\PdfReadyNotification($this->pdfType, $this->recordId)
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("PDF generation failed: {$e->getMessage()}");
        }
    }

    private function generateOSPdf() {}
    private function generateBudgetPdf() {}
    private function generateInvoicePdf() {}
}
