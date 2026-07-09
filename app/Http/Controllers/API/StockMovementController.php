<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Services\StockMovementService;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    /**
     * Record a new stock movement.
     */
    public function store(StoreStockMovementRequest $request, StockMovementService $service)
    {
        try {
            $movement = $service->createMovement($request->validated());

            return response()->json([
                'success' => true,
                'data' => new StockMovementResource($movement),
            ], 201);
                
        } catch (ValidationException $e) {
            // Laravel's Exception Handler will catch this and format it automatically
            // to a 422 JSON response if the request accepts JSON.
            // But to be explicit and ensure consistency as per plan:
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
