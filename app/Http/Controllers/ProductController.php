<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Schema;


class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        // Get search and sort parameters from the request
        $search = $request->get('search', ''); // Search term
        $sortColumn = $request->get('sort_column', 'id'); // Default sort column
        $sortDirection = $request->get('sort_direction', 'asc'); // Default sort direction (asc/desc)
        $perPage = $request->get('per_page', 10); // Items per page

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        // Query products
        $query = Product::with('updatedBy');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

    
        if ($sortColumn === 'updated_by_name') {
            // Sort by updated_by_name (user's name)
            $query->leftJoin('users as updated_by_users', 'products.updated_by', '=', 'updated_by_users.id')
                  ->orderBy('updated_by_users.name', $sortDirection)
                  ->select('products.*'); // Ensure all product fields are selected
        } elseif ($sortColumn === 'total') {
            // Sort by calculated Total (amount * unit)
            $query->select('*', \DB::raw('amount * unit AS total'))
                  ->orderBy('total', $sortDirection);
        } elseif (Schema::hasColumn('products', $sortColumn)) {
            // Sort by regular columns
            $query->orderBy($sortColumn, $sortDirection);
        }
        

        // Paginate results
        $products = $query->paginate($perPage);

        // Add updated_by_name to each product
        $products->getCollection()->transform(function ($product) {
            $product->updated_by_name = $product->updatedBy ? $product->updatedBy->name : 'Unknown';
            return $product;
        });

        // Return response
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'per_page' => $products->perPage(),
        ], 200);
    }




    public function show($id)
    {
        $id = intval($id); // Convert $id to an integer

        // Search for the product using a different field (e.g., product_code)
        $product = Product::where('id', $id)->first(); // Replace 'product_code' with your desired field

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['data' => $product], 200);
    }







    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request, $id = null)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'unit' => 'required|numeric|min:0',
            'color' => 'required|string|max:255',
        ]);

        try {
            // Get the user ID from the JWT token
            $user = JWTAuth::parseToken()->authenticate(); // This will authenticate the token and retrieve the user
            $userId = $user ? $user->id : null; // Get the user ID, or null if not authenticated

            // If user is not authenticated, throw an error
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: No valid token provided.'
                ], 401); // HTTP 401: Unauthorized
            }

            // If an ID is provided, update the existing product
            if ($id) {
                $product = Product::find($id); // Find the product by ID

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.'
                    ], 404); // HTTP 404: Not Found
                }

                // Update the product
                $product->update([
                    'name' => $request->name,
                    'amount' => $request->amount,
                    'unit' => $request->unit,
                    'color' => $request->color,
                    'updated_by' => $userId, // Set the user ID for 'updated_by'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully.',
                    'data' => $product
                ], 200); // HTTP 200: OK
            }

            // If no ID is provided, create a new product
            $product = Product::create([
                'name' => $request->name,
                'amount' => $request->amount,
                'unit' => $request->unit,
                'color' => $request->color,
                'updated_by' => $userId, // Set the user ID for 'updated_by'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product
            ], 201); // HTTP 201: Created

        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            // \Log::error('Error creating/updating product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating or updating the product.',
                'error' => $e->getMessage()
            ], 500); // HTTP 500: Internal Server Error
        }
    }



    /**
     * Remove the specified product from storage.
     */
    public function delete($id)
    {
        try {
            // Find the product by ID
            $product = Product::find($id);

            // If product does not exist, return an error message
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404); // HTTP 404: Not Found
            }

            // Delete the product
            $product->delete();

            // Return success message after deletion
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ], 200); // HTTP 200: OK
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            // \Log::error('Error deleting product: ' . $e->getMessage());

            // Return error message if something goes wrong
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500: Internal Server Error
        }
    }



    public function importCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file, 'r');
        $header = true;

        // Get the user ID from the JWT token
        $user = JWTAuth::parseToken()->authenticate(); // This will authenticate the token and retrieve the user
        $userId = $user ? $user->id : null; // Get the user ID, or null if not authenticated


        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if ($header) {
                $header = false; // Skip the header row
                continue;
            }
            Product::create([
                'created_at' => $row[0],
                'updated_at' => $row[0],
                'name' => $row[1],
                'color' => $row[2],
                'amount' => $row[3],
                'unit' => $row[4],
                'updated_by' => $userId

            ]);
        }
        fclose($handle);

        return response()->json(['message' => 'Data imported successfully'], 200);
    }
}
