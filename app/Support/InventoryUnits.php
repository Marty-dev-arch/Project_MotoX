<?php

namespace App\Support;

use App\Models\Part;
use Illuminate\Validation\ValidationException;

class InventoryUnits
{
    public const MODE_PIECE = 'piece';
    public const MODE_BOX_PIECE = 'box_piece';

    public static function toBaseQuantity(Part $part, float $quantity, ?string $quantityUnit = null): float
    {
        $normalizedUnit = strtolower(trim((string) $quantityUnit));

        if ($part->usesBoxConversion() && $normalizedUnit === 'box') {
            $piecesPerBox = max(1.0, (float) $part->pieces_per_box);
            return round($quantity * $piecesPerBox, 3);
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
            default => $part->defaultUnitLabel(),
        };
    }

    public static function priceForBaseQuantity(Part $part, float $baseQuantity): float
    {
        $unitPrice = (float) ($part->unit_price_per_piece ?? 0);

        if ($unitPrice <= 0) {
            $unitPrice = (float) $part->unit_price;

            if ($part->usesBoxConversion() && $part->unit_price_basis !== 'per_piece') {
                $unitPrice = $unitPrice / max(0.001, (float) $part->pieces_per_box);
            }
        }

        return round(abs($baseQuantity) * $unitPrice, 2);
    }

    /**
     * Calculate how many physical boxes are present based on total pieces.
     * Uses ceiling division so that a partially consumed box still counts as 1.
     */
    public static function boxCount(Part $part, float $totalPieces): int
    {
        if ($totalPieces <= 0) {
            return 0;
        }

        $piecesPerBox = (float) ($part->pieces_per_box ?? 1);
        if ($piecesPerBox <= 0) {
            return 0;
        }

        // Rule: Deduct the box only when all pieces inside are fully consumed.
        // Example: 4 pieces left in a 5-piece box = ceil(0.8) = 1 box.
        // Example: 6 pieces left (1 full box + 1 piece in next) = ceil(1.2) = 2 boxes.
        return (int) ceil($totalPieces / $piecesPerBox);
    }

    /**
     * Calculate the loose pieces remaining in the "open" container.
     * This represents the fractional part of a box.
     */
    public static function remainingPieces(Part $part, float $totalPieces): float
    {
        if ($totalPieces <= 0) {
            return 0;
        }

        $piecesPerBox = (float) ($part->pieces_per_box ?? 1);
        if ($piecesPerBox <= 0) {
            return 0;
        }

        // We use fmod to get the remainder of pieces that don't make up a full box.
        // If stock is 24 and box is 5, remainder is 4 pieces in the open box.
        // If stock is 25 and box is 5, remainder is 0 (all boxes are currently full).
        return round(fmod($totalPieces, $piecesPerBox), 3);
    }
}
