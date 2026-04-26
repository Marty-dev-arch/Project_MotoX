<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['part_id', 'user_id', 'type', 'quantity', 'reason', 'reference', 'moved_at'])]
class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUST = 'adjust';

    
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delta(): int
    {
        return match ($this->type) {
            self::TYPE_IN => abs($this->quantity),
            self::TYPE_OUT => abs($this->quantity) * -1,
            default => $this->quantity,
        };
    }
}

