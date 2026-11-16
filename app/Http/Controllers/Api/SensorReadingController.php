<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SensorReadingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => ['required', 'string', 'max:50'],
            'temperature' => ['required', 'numeric', 'between:-40,85'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        if (! empty($data['recorded_at'])) {
            $data['recorded_at'] = CarbonImmutable::parse($data['recorded_at'])->utc();
        }

        $reading = SensorReading::create($data);

        return response()->json([
            'message' => 'Sensor reading saved successfully.',
            'data' => $reading,
        ], 201);
    }

    public function index(): JsonResponse
    {
        return response()->json(SensorReading::latest('id')->limit(20)->get());
    }

    public function latest(): JsonResponse
    {
        $reading = SensorReading::latest('id')->first();

        if (! $reading) {
            return response()->json(['message' => 'No sensor readings found.'], 404);
        }

        return response()->json($reading);
    }
}
