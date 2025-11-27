<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Jobs\ProcessStatusFileJob;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSortColumns = ['id', 'product_id', 'types', 'brand', 'model', 'capacity', 'quantity'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'asc');
        }

        $perPage = $request->get('per_page', 10);
        return $query->paginate($perPage);
    }

    public function uploadStatusFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $path = $request->file('file')->store('uploads');
        ProcessStatusFileJob::dispatch($path);

        return response()->json(['message' => 'File uploaded. Processing started.'], 202);
    }
}
