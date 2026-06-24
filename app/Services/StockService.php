<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function createMutation(array $payload): StockMutation
    {
        return DB::transaction(function () use ($payload): StockMutation {
            $type = (string) $payload['type'];

            match ($type) {
                'in' => $this->increase((int) $payload['product_id'], (int) $payload['to_warehouse_id'], (string) $payload['qty']),
                'out' => $this->decrease((int) $payload['product_id'], (int) $payload['from_warehouse_id'], (string) $payload['qty']),
                'transfer' => $this->transfer($payload),
                'adjustment' => $this->adjust((int) $payload['product_id'], $payload),
                default => throw ValidationException::withMessages(['type' => 'Unsupported stock mutation type.']),
            };

            $payload['mutation_number'] = $this->generateMutationNumber();

            return StockMutation::create($payload);
        });
    }

    private function transfer(array $payload): void
    {
        $productId = (int) $payload['product_id'];
        $fromWarehouseId = (int) $payload['from_warehouse_id'];
        $toWarehouseId = (int) $payload['to_warehouse_id'];
        $qty = (string) $payload['qty'];

        $this->decrease($productId, $fromWarehouseId, $qty);
        $this->increase($productId, $toWarehouseId, $qty);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function adjust(int $productId, array $payload): void
    {
        if (! empty($payload['to_warehouse_id'])) {
            $this->increase($productId, (int) $payload['to_warehouse_id'], (string) $payload['qty']);

            return;
        }

        $this->decrease($productId, (int) $payload['from_warehouse_id'], (string) $payload['qty']);
    }

    private function increase(int $productId, int $warehouseId, string $qty): Stock
    {
        $stock = Stock::firstOrCreate([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ], [
            'qty' => 0,
        ]);

        $stock = Stock::query()
            ->where('id', $stock->id)
            ->lockForUpdate()
            ->first();

        $stock->qty = $this->decimal((float) $stock->qty + (float) $qty);
        $stock->save();

        return $stock;
    }

    private function decrease(int $productId, int $warehouseId, string $qty): Stock
    {
        $stock = $this->lockedStock($productId, $warehouseId);

        if (! $stock || (float) $stock->qty < (float) $qty) {
            throw ValidationException::withMessages([
                'qty' => 'Stock is not enough.',
            ]);
        }

        $stock->qty = $this->decimal((float) $stock->qty - (float) $qty);
        $stock->save();

        return $stock;
    }

    private function lockedStock(int $productId, int $warehouseId): ?Stock
    {
        return Stock::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();
    }

    private function generateMutationNumber(): string
    {
        return 'MTN-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function decimal(float $value): string
    {
        return number_format($value, 4, '.', '');
    }
}
