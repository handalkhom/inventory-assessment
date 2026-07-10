<?php

use App\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces SKU uniqueness', function () {
    Product::factory()->create(['sku' => 'UNIQUE-SKU']);

    expect(fn () => Product::factory()->create(['sku' => 'UNIQUE-SKU']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('enforces SKU immutability after creation', function () {
    $product = Product::factory()->create(['sku' => 'ORIGINAL-SKU']);

    expect(fn () => $product->update(['sku' => 'NEW-SKU']))
        ->toThrow(InvalidArgumentException::class, 'SKU is immutable and cannot be changed.');
});
