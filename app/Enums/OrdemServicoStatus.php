<?php

namespace App\Enums;

enum OrdemServicoStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Testing = 'testing';
    case Cleaning = 'cleaning';
    case Completed = 'completed';
    case Paid = 'paid';
    case AwaitingApproval = 'awaiting_approval';
    case Running = 'running';
    case Finalized = 'finalized';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::InProgress => 'Em Manutenção',
            self::Testing => 'Em Teste',
            self::Cleaning => 'Em Limpeza',
            self::Completed => 'Pronto para Retirada',
            self::Paid => 'Finalizado / Pago',
            self::AwaitingApproval => 'Aguardando Aprovação',
            self::Running => 'Em Andamento',
            self::Finalized => 'Finalizado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Finalized, self::Completed, self::Cancelled]);
    }

    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
