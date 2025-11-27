<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class StatusImport implements ToCollection
{
    protected $updatedCount = 0;
    protected $createdCount = 0;
    protected $errors = [];
    protected $hasHeaders = false;
    protected $headerMap = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            Log::warning("StatusImport: Empty file");
            return;
        }

        $firstRow = $rows->first();
        if ($firstRow) {
            $firstRowArray = $firstRow->toArray();
            $firstRowValues = array_map(function($val) {
                return is_string($val) ? strtolower(trim($val)) : '';
            }, array_values($firstRowArray));

            $hasHeaderKeywords = false;
            foreach ($firstRowValues as $value) {
                if (is_string($value) && (
                    stripos($value, 'product') !== false ||
                    stripos($value, 'status') !== false ||
                    stripos($value, 'types') !== false ||
                    stripos($value, 'brand') !== false
                )) {
                    $hasHeaderKeywords = true;
                    break;
                }
            }

            $this->hasHeaders = $hasHeaderKeywords;

            if ($this->hasHeaders) {
                $this->headerMap = $this->createHeaderMap($firstRowArray);
                Log::info("StatusImport: Headers detected", [
                    'headers' => $this->headerMap,
                    'first_row' => $firstRowArray
                ]);
            }
        }

        $dataRows = $this->hasHeaders ? $rows->slice(1) : $rows;

        foreach ($dataRows as $rowIndex => $row) {
            try {
                $rowArray = $row->toArray();
                $rowValues = array_values($rowArray);

                $productId = null;
                $types = null;
                $brand = null;
                $model = null;
                $capacity = null;
                $status = null;

                if ($this->hasHeaders && !empty($this->headerMap)) {
                    $productIdCol = $this->headerMap['product_id'] ?? null;
                    $typesCol = $this->headerMap['types'] ?? null;
                    $brandCol = $this->headerMap['brand'] ?? null;
                    $modelCol = $this->headerMap['model'] ?? null;
                    $capacityCol = $this->headerMap['capacity'] ?? null;
                    $statusCol = $this->headerMap['status'] ?? null;

                    if ($productIdCol !== null && isset($rowValues[$productIdCol])) {
                        $productId = (int)$rowValues[$productIdCol];
                    }
                    if ($typesCol !== null && isset($rowValues[$typesCol])) {
                        $types = trim($rowValues[$typesCol] ?? '');
                    }
                    if ($brandCol !== null && isset($rowValues[$brandCol])) {
                        $brand = trim($rowValues[$brandCol] ?? '');
                    }
                    if ($modelCol !== null && isset($rowValues[$modelCol])) {
                        $model = trim($rowValues[$modelCol] ?? '');
                    }
                    if ($capacityCol !== null && isset($rowValues[$capacityCol])) {
                        $capacity = trim($rowValues[$capacityCol] ?? '');
                    }
                    if ($statusCol !== null && isset($rowValues[$statusCol])) {
                        $status = strtolower(trim($rowValues[$statusCol] ?? ''));
                    }

                    if (empty($types) || empty($brand) || empty($model) || empty($capacity)) {
                        if (empty($types) && isset($rowValues[1])) {
                            $types = trim($rowValues[1]);
                        }
                        if (empty($brand) && isset($rowValues[2])) {
                            $brand = trim($rowValues[2]);
                        }
                        if (empty($model) && isset($rowValues[3])) {
                            $model = trim($rowValues[3]);
                        }
                        if (empty($capacity) && isset($rowValues[4])) {
                            $capacity = trim($rowValues[4]);
                        }
                        if (empty($status) && isset($rowValues[5])) {
                            $status = strtolower(trim($rowValues[5]));
                        }
                    }
                } else {
                    $productId = (int)($rowValues[0] ?? 0);
                    $types = trim($rowValues[1] ?? '');
                    $brand = trim($rowValues[2] ?? '');
                    $model = trim($rowValues[3] ?? '');
                    $capacity = trim($rowValues[4] ?? '');
                    $status = strtolower(trim($rowValues[5] ?? ''));
                }

                if ($productId === 0) {
                    continue;
                }

                if (empty($status)) {
                    $this->errors[] = "Row with Product ID {$productId} has no status";
                    continue;
                }

                $qty = 1;
                $product = Product::where('product_id', $productId)->first();

                if (!$product) {
                    Log::info("Product ID {$productId} not found. Extracted data:", [
                        'product_id' => $productId,
                        'types' => $types,
                        'brand' => $brand,
                        'model' => $model,
                        'capacity' => $capacity,
                        'status' => $status,
                        'row_values' => $rowValues,
                        'header_map' => $this->headerMap
                    ]);

                    if (empty($types) || empty($brand) || empty($model) || empty($capacity)) {
                        $this->errors[] = "Product ID {$productId} not found and missing required details. Types: '{$types}', Brand: '{$brand}', Model: '{$model}', Capacity: '{$capacity}'";
                        continue;
                    }

                    try {
                        $product = Product::create([
                            'product_id' => $productId,
                            'types' => $types,
                            'brand' => $brand,
                            'model' => $model,
                            'capacity' => $capacity,
                            'quantity' => 0,
                        ]);

                        $this->createdCount++;
                        Log::info("Created new Product ID {$productId}: {$types}, {$brand}, {$model}, {$capacity}");
                    } catch (\Exception $e) {
                        $this->errors[] = "Failed to create Product ID {$productId}: " . $e->getMessage();
                        Log::error("Failed to create product", [
                            'product_id' => $productId,
                            'error' => $e->getMessage(),
                            'data' => ['types' => $types, 'brand' => $brand, 'model' => $model, 'capacity' => $capacity]
                        ]);
                        continue;
                    }
                }

                $oldQuantity = $product->quantity;

                if ($status === 'sold') {
                    $product->quantity = max(0, $product->quantity - $qty);
                } elseif ($status === 'buy') {
                    $product->quantity = $product->quantity + $qty;
                } else {
                    $this->errors[] = "Product ID {$productId} has invalid status: {$status}";
                    continue;
                }

                $product->save();
                $this->updatedCount++;

                Log::info("Updated Product ID {$productId}: {$oldQuantity} -> {$product->quantity} (Status: {$status}, Qty: {$qty})");
            } catch (\Exception $e) {
                $this->errors[] = "Error processing row: " . $e->getMessage();
                Log::error("StatusImport error: " . $e->getMessage(), ['row' => $row->toArray()]);
            }
        }

        Log::info("StatusImport completed. Created {$this->createdCount} new products. Updated {$this->updatedCount} products. Errors: " . count($this->errors));
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    public function getCreatedCount()
    {
        return $this->createdCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function createHeaderMap(array $headerRow)
    {
        $map = [];
        foreach ($headerRow as $index => $header) {
            $headerLower = strtolower(trim($header));
            if (stripos($headerLower, 'product') !== false && stripos($headerLower, 'id') !== false) {
                $map['product_id'] = $index;
            }
            if (stripos($headerLower, 'types') !== false || stripos($headerLower, 'type') !== false) {
                $map['types'] = $index;
            }
            if (stripos($headerLower, 'brand') !== false) {
                $map['brand'] = $index;
            }
            if (stripos($headerLower, 'model') !== false) {
                $map['model'] = $index;
            }
            if (stripos($headerLower, 'capacity') !== false) {
                $map['capacity'] = $index;
            }
            if (stripos($headerLower, 'status') !== false) {
                $map['status'] = $index;
            }
        }
        return $map;
    }
}

