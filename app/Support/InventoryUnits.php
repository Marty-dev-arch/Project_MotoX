<?php

namespace App\Support;

use App\Models\Part;
use Illuminate\Validation\ValidationException;

class InventoryUnits
{
    public const MODE_PIECE = 'piece';
    public const MODE_BOX_PIECE = 'box_piece';
    public const MODE_LIQUID = 'liquid';

    public static function toBaseQuantity(Part $part, float $quantity, ?string $quantityUnit = null): float
    {
        $normalizedUnit = strtolower(trim((string) $quantityUnit));

        if ($part->usesBoxConversion() && $normalizedUnit === 'box') {
            $piecesPerBox = max(1.0, (float) $part->pieces_per_box);
            return round($quantity * $piecesPerBox, 3);
        }

        if ($part->usesLiquidMode() && in_array($normalizedUnit, ['ml', 'milliliter', 'milliliters'], true)) {
            return round($quantity / 1000, 3);
        }

        return round($quantity, 3);
    }

    public static function assertValidSellQuantity(Part $part, float $quantity, string $field = 'quantity'): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                $field => 'Quantity must be greater than zero.',
            ]);
        }

        if ($part->usesLiquidMode()) {
            return;
        }

        $isWhole = abs($quantity - round($quantity)) < 0.00001;

        if (! $part->allow_fractional_quantity && ! $isWhole) {
            throw ValidationException::withMessages([
                $field => 'This item only accepts whole-number quantities.',
            ]);
        }
    }

    public static function unitHint(Part $part): string
    {
        return match ($part->stock_mode) {
            self::MODE_BOX_PIECE => sprintf(
                '%s (1 box = %s %s)',
                $part->defaultUnitLabel(),
                rtrim(rtrim(number_format((float) ($part->pieces_per_box ?? 0), 3, '.', ''), '0'), '.'),
                $part->defaultUnitLabel(),
            ),
            self::MODE_LIQUID => $part->defaultUnitLabel(),
            default => $part->defaultUnitLabel(),
        };
    }

    public static function priceForBaseQuantity(Part $part, float $baseQuantity): float
    {
        $unitPrice = (float) $part->unit_price;

        if ($part->usesBoxConversion()) {
            $unitPrice = $unitPrice / max(0.001, (float) $part->pieces_per_box);
        }

        return round(abs($baseQuantity) * $unitPrice, 2);
    }
}
