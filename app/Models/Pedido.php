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
    ];

    protected function casts(): array
    {
        return [
            'cliente'  => 'array',
            'endereco' => 'array',
        ];
    }

    public const STATUS_PAGOS = ['PAID', 'APPROVED'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
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
            default                          => 'bg-gray-100 text-gray-600',
        };
    }
}
