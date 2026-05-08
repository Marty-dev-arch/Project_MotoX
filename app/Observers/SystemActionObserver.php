<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Part;
use App\Models\StockMovement;
use App\Support\SystemLogger;
use Illuminate\Database\Eloquent\Model;

class SystemActionObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof StockMovement) {
            $this->logStockMovement($model);
            return;
        }

        SystemLogger::record(
            $this->actionName($model, 'created'),
            sprintf('%s created: %s.', $this->labelFor($model), $this->createdSummary($model)),
            $model,
            ['snapshot' => $this->snapshot($model)],
        );
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except(['updated_at'])
            ->all();

        if ($changes === []) {
            return;
        }

        $fieldChanges = collect($changes)
            ->mapWithKeys(fn ($value, string $field): array => [
                $field => [
                    'from' => $model->getOriginal($field),
                    'to' => $value,
                ],
            ])
            ->all();

        SystemLogger::record(
            $this->actionName($model, 'updated'),
            sprintf('%s updated: %s. %s', $this->labelFor($model), $this->displayName($model), $this->changeSummary($fieldChanges)),
            $model,
            ['changes' => $fieldChanges],
        );
    }

    public function deleted(Model $model): void
    {
        SystemLogger::record(
            $this->actionName($model, 'deleted'),
            sprintf('%s deleted: %s.', $this->labelFor($model), $this->deletedSummary($model)),
            $model,
            ['snapshot' => $this->snapshot($model)],
        );
    }

    private function logStockMovement(StockMovement $movement): void
    {
        $part = $movement->part;
        $type = match ($movement->type) {
            StockMovement::TYPE_OPENING => 'stock.opening',
            StockMovement::TYPE_IN => 'stock.in',
            StockMovement::TYPE_OUT => 'stock.out',
            default => 'stock.adjusted',
        };
        $label = match ($movement->type) {
            StockMovement::TYPE_OPENING => 'Opening stock',
            StockMovement::TYPE_IN => 'Stock in',
            StockMovement::TYPE_OUT => 'Stock out',
            default => 'Stock adjustment',
        };

        SystemLogger::record(
            $type,
            sprintf('%s recorded for %s: %s.', $label, $part?->name ?? 'part', (string) $movement->quantity),
            $movement,
            [
                'part_id' => $movement->part_id,
                'part_name' => $part?->name,
                'type' => $movement->type,
                'quantity' => (string) $movement->quantity,
                'reason' => $movement->reason,
                'reference' => $movement->reference,
            ],
            $part?->shop_id,
            $movement->user_id ?: null,
        );
    }

    private function actionName(Model $model, string $verb): string
    {
        return match (true) {
            $model instanceof Part => "part.{$verb}",
            $model instanceof Customer => "customer.{$verb}",
            $model instanceof JobOrder => "job_order.{$verb}",
            default => class_basename($model).".{$verb}",
        };
    }

    private function labelFor(Model $model): string
    {
        return match (true) {
            $model instanceof Part => 'Part',
            $model instanceof Customer => 'Customer',
            $model instanceof JobOrder => 'Job order',
            default => class_basename($model),
        };
    }

    private function displayName(Model $model): string
    {
        return match (true) {
            $model instanceof Part => (string) $model->name,
            $model instanceof Customer => (string) $model->name,
            $model instanceof JobOrder => (string) $model->order_number,
            default => '#'.$model->getKey(),
        };
    }

    private function createdSummary(Model $model): string
    {
        return match (true) {
            $model instanceof Part => sprintf('%s (%s) in %s', $model->name, $model->sku, $model->category),
            $model instanceof Customer => sprintf('%s, %s', $model->name, $model->email ?: 'no email'),
            $model instanceof JobOrder => sprintf('%s for %s - %s', $model->order_number, $model->customer?->name ?? 'Walk-in Customer', $model->vehicle),
            default => $this->displayName($model),
        };
    }

    private function deletedSummary(Model $model): string
    {
        return match (true) {
            $model instanceof Part => sprintf('%s (%s)', $model->name, $model->sku),
            $model instanceof Customer => sprintf('%s (%s)', $model->name, $model->email ?: 'no email'),
            $model instanceof JobOrder => sprintf('%s for %s', $model->order_number, $model->customer?->name ?? 'Walk-in Customer'),
            default => $this->displayName($model),
        };
    }

    /**
     * @param  array<string, array{from: mixed, to: mixed}>  $fieldChanges
     */
    private function changeSummary(array $fieldChanges): string
    {
        if ($fieldChanges === []) {
            return 'No visible fields changed.';
        }

        return collect($fieldChanges)
            ->map(function (array $change, string $field): string {
                return sprintf(
                    '%s changed from "%s" to "%s"',
                    str_replace('_', ' ', $field),
                    $this->formatValue($change['from'] ?? null),
                    $this->formatValue($change['to'] ?? null),
                );
            })
            ->implode('; ').'.';
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Model $model): array
    {
        return collect($model->getAttributes())
            ->except(['password', 'remember_token'])
            ->all();
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'blank';
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
