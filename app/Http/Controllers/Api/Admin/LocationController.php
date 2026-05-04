<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Location::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'short' => 'required|string|unique:locations,short',
            'long' => 'required|string',
        ]);

        $location = Location::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Location created successfully.',
            'data' => $location,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return response()->json([
            'status' => 'success',
            'data' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'short' => 'sometimes|string|unique:locations,short,'.$location->id,
            'long' => 'sometimes|string',
        ]);

        $location->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully.',
            'data' => $location,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        if ($location->nodes()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete location that has nodes assigned to it.',
            ], 422);
        }

        $location->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Location deleted successfully.',
        ]);
    }
}
