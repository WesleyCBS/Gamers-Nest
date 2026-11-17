<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'user_id',
        'metodo',
        'status',
        'total',
        'parcelas',
        'cartao_bandeira',
        'cartao_final',
        'gateway_id',
        'cliente',
        'endereco',
        'cobranca',
        'estoque_reservado',
        'cancelamento',
        'cancelamento_motivo',
        'cancelamento_solicitado_em',
        'confirmado_em',
        'cancelado_por',
        'cancelado_por_nome',
        'cancelado_em',
    ];

    protected function casts(): array
    {
        return [
            'cliente'  => 'array',
            'endereco' => 'array',
            'cobranca' => 'array',
            'estoque_reservado' => 'boolean',
            'cancelamento_solicitado_em' => 'datetime',
            'confirmado_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }

    public const STATUS_PAGOS = ['PAID', 'APPROVED'];

    public const STATUS_CANCELADOS = ['CANCELLED', 'REFUNDED', 'EXPIRED'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function devolverEstoque(): void
    {
        if (!$this->estoque_reservado) {
            return;
        }

        foreach ($this->itens as $item) {
            if ($item->produto_id) {
                Produto::whereKey($item->produto_id)->increment('quantidade', $item->quantidade);
            }
        }

        $this->update(['estoque_reservado' => false]);
    }

    public function getPodeCancelarAttribute(): bool
    {
        return $this->status === 'PENDING';
    }

    public function scopeAConfirmar($query)
    {
        return $query->whereIn('status', self::STATUS_PAGOS)
            ->whereNull('confirmado_em')
            ->where(fn ($q) => $q->whereNull('cancelamento')->orWhere('cancelamento', 'recusado'));
    }

    public function scopeConcluidas($query)
    {
        return $query->whereIn('status', self::STATUS_PAGOS)
            ->whereNotNull('confirmado_em')
            ->where(fn ($q) => $q->whereNull('cancelamento')->orWhere('cancelamento', 'recusado'));
    }

    public function scopeCancelados($query)
    {
        return $query->whereIn('status', self::STATUS_CANCELADOS);
    }

    public function getCanceladoAttribute(): bool
    {
        return in_array($this->status, self::STATUS_CANCELADOS);
    }

    public function getPodeCancelarAdminAttribute(): bool
    {
        return in_array($this->status, self::STATUS_PAGOS) && $this->cancelamento !== 'solicitado';
    }

    public function getCanceladoPorLabelAttribute(): string
    {
        return match (true) {
            $this->cancelado_por === 'cliente' => 'Cliente',
            $this->cancelado_por === 'admin'   => 'Loja (admin)',
            $this->status === 'EXPIRED'        => 'Sistema (pagamento expirou)',
            default                            => 'Não registrado',
        };
    }

    public function scopeCancelamentoSolicitado($query)
    {
        return $query->where('cancelamento', 'solicitado');
    }

    public function getPodeConfirmarAttribute(): bool
    {
        return in_array($this->status, self::STATUS_PAGOS)
            && $this->confirmado_em === null
            && $this->cancelamento !== 'solicitado';
    }

    public function getPodeSolicitarCancelamentoAttribute(): bool
    {
        return in_array($this->status, self::STATUS_PAGOS) && $this->cancelamento === null;
    }

    public function getPodePagarAttribute(): bool
    {
        return $this->status === 'PENDING' && in_array($this->metodo, ['pix', 'boleto']);
    }

    public function getPagoAttribute(): bool
    {
        return in_array($this->status, self::STATUS_PAGOS);
    }

    public function getTotalReaisAttribute(): float
    {
        return $this->total / 100;
    }

    public function getMetodoLabelAttribute(): string
    {
        return match ($this->metodo) {
            'pix'     => 'PIX',
            'credito' => 'Cartão de Crédito',
            'boleto'  => 'Boleto',
            default   => ucfirst($this->metodo),
        };
    }

    public function getMetodoIconeAttribute(): string
    {
        return match ($this->metodo) {
            'pix'     => 'fa-qrcode text-emerald-500',
            'credito' => 'fa-credit-card text-blue-500',
            'boleto'  => 'fa-barcode text-orange-500',
            default   => 'fa-wallet text-gray-500',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'PAID', 'APPROVED' => 'Pago',
            'PENDING'          => 'Aguardando pagamento',
            'EXPIRED'          => 'Expirado',
            'CANCELLED'        => 'Cancelado',
            'FAILED'           => 'Recusado',
            'REFUNDED'         => 'Reembolsado',
            default            => $this->status,
        };
    }

    public function getStatusCorAttribute(): string
    {
        return match ($this->status) {
            'PAID', 'APPROVED'               => 'bg-green-100 text-green-700',
            'PENDING'                        => 'bg-amber-100 text-amber-700',
            'EXPIRED', 'CANCELLED', 'FAILED' => 'bg-red-100 text-red-700',
            'REFUNDED'                       => 'bg-purple-100 text-purple-700',
            default                          => 'bg-gray-100 text-gray-600',
        };
    }
}
