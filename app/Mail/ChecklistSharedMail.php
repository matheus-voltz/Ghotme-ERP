<?php

namespace App\Mail;

use App\Models\VehicleInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChecklistSharedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inspection;

    public function __construct(VehicleInspection $inspection)
    {
        $this->inspection = $inspection;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Checklist de Entrada - Veículo ' . $this->inspection->veiculo->placa,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.checklist-shared',
        );
    }
}