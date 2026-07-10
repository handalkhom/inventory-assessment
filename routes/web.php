<?php

use App\Livewire\StockAdjustmentManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stock-adjustment', StockAdjustmentManager::class);
