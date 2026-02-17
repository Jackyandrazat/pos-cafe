<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MenuResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()->with(['category','toppings']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_available')) {
            $isAvailable = filter_var($request->input('is_available'), FILTER_VALIDATE_BOOL);
            $query->where('status_enabled', $isAvailable ? 1 : 0);
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return MenuResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(Product $product): MenuResource
    {
        $product->load('category');

        return new MenuResource($product);
    }
}
