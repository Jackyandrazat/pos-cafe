<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ToppingResource;
use App\Models\Topping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ToppingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Topping::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_available')) {
            $isAvailable = filter_var(
                $request->input('is_available'),
                FILTER_VALIDATE_BOOL
            );

            $query->where('status_enabled', $isAvailable ? 1 : 0);
        }

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            50
        );

        return ToppingResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(Topping $topping): ToppingResource
    {
        return new ToppingResource($topping);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topping $topping)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topping $topping)
    {
        //
    }
}
