<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Reservation;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class ManagerController extends Controller
{
    /**
     * Get all tables.
     */
    public function getTables()
    {
        $tables = Table::with(['activeReservations:id,table_id,user_id,start_date,end_date'])
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'name_ar' => $table->name_ar,
                    'capacity' => $table->capacity,
                    'type' => $table->type,
                    'status' => $table->status,
                    'description' => $table->description,
                    'description_ar' => $table->description_ar,
                    'is_active' => $table->is_active,
                    'image_path' => $table->image_path, // Always included, even if null
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                    'active_reservations' => $table->activeReservations,
                    'reservations_list' => $table->reservations_list,
                ];
            });
        
        return response()->json([
            'tables' => $tables
        ]);
    }

    /**
     * Upload a file to Cloudinary and return the secure URL or null on failure.
     *
     * This method attempts to use the Cloudinary SDK if installed. If not,
     * it falls back to a direct HTTP upload using Guzzle and the Cloudinary
     * REST API. Configure using one of:
     * - CLOUDINARY_URL (preferred), or
     * - CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET, and optionally CLOUDINARY_UPLOAD_PRESET for unsigned uploads.
     */
    private function uploadToCloudinary($file, $folder = null)
    {
        // If Cloudinary SDK is available, prefer it.
        if (class_exists(\Cloudinary\Cloudinary::class)) {
            try {
                $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                $uploader = $cloudinary->uploadApi();

                $options = [];
                if ($folder) {
                    $options['folder'] = $folder;
                }

                // If unsigned preset is set, use it
                if (env('CLOUDINARY_UPLOAD_PRESET')) {
                    $options['upload_preset'] = env('CLOUDINARY_UPLOAD_PRESET');
                }

                $result = $uploader->upload($file->getPathname(), $options);
                return $result['secure_url'] ?? null;
            } catch (\Throwable $e) {
                logger()->error('Cloudinary SDK upload failed: ' . $e->getMessage());
                // Fall through to HTTP fallback
            }
        }

        // HTTP fallback using Guzzle
        try {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $apiKey = env('CLOUDINARY_API_KEY');
            $apiSecret = env('CLOUDINARY_API_SECRET');
            $uploadPreset = env('CLOUDINARY_UPLOAD_PRESET');

            if (empty($cloudName)) {
                // Can't proceed without cloud name
                return null;
            }

            $client = new Client(['timeout' => 30]);

            $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

            $multipart = [
                [
                    'name' => 'file',
                    'contents' => fopen($file->getPathname(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ],
            ];

            if ($uploadPreset) {
                $multipart[] = ['name' => 'upload_preset', 'contents' => $uploadPreset];
                if ($folder) {
                    $multipart[] = ['name' => 'folder', 'contents' => $folder];
                }
            } elseif ($apiKey && $apiSecret) {
                // Signed upload: add timestamp and signature
                $timestamp = time();
                $paramsToSign = [];
                if ($folder) $paramsToSign['folder'] = $folder;
                $paramsToSign['timestamp'] = $timestamp;

                // Create signature string (sorted by key, concatenated as key=value&key2=value2)
                ksort($paramsToSign);
                $pairs = [];
                foreach ($paramsToSign as $k => $v) {
                    $pairs[] = $k . '=' . $v;
                }
                $toSign = implode('&', $pairs);
                $signature = hash('sha1', $toSign . $apiSecret);

                $multipart[] = ['name' => 'api_key', 'contents' => $apiKey];
                $multipart[] = ['name' => 'timestamp', 'contents' => $timestamp];
                $multipart[] = ['name' => 'signature', 'contents' => $signature];
                if ($folder) {
                    $multipart[] = ['name' => 'folder', 'contents' => $folder];
                }
            } else {
                // No valid upload method configured
                logger()->error('Cloudinary upload: no cloud name or credentials configured.');
                return null;
            }

            $response = $client->post($url, [
                'multipart' => $multipart,
            ]);

            $body = json_decode((string) $response->getBody(), true);
            return $body['secure_url'] ?? null;
        } catch (\Throwable $e) {
            logger()->error('Cloudinary HTTP upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new table.
     */
    public function createTable(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'type' => ['nullable', 'in:single,double,family,special,custom'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'tables');
            if ($uploaded) {
                $data['image_path'] = $uploaded;
            }
        }

        $table = Table::create($data);

        return response()->json([
            'message' => 'Table created successfully.',
            'table' => $table
        ], 201);
    }

    /**
     * Update a table.
     */
    public function updateTable(Request $request, $id)
    {
        $table = Table::findOrFail($id);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'type' => ['sometimes', 'in:single,double,family,special,custom'],
            'status' => ['sometimes', 'in:available,occupied,reserved,maintenance'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // If the old image is a Cloudinary URL we can't delete it via Storage; skip deletion.
            // If you store Cloudinary public_id in the DB, implement deletion via Cloudinary API here.
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'tables');
            if (!$uploaded) {
                return response()->json(['message' => 'Image upload failed.'], 500);
            }
            $data['image_path'] = $uploaded;
        }

        $table->update($data);

        return response()->json([
            'message' => 'Table updated successfully.',
            'table' => $table
        ]);
    }

    /**
     * Delete a table.
     */
    public function deleteTable($id)
    {
        $table = Table::findOrFail($id);
        
        // Check if table has active reservations or orders
        if ($table->reservations()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return response()->json([
                'message' => 'Cannot delete table with active reservations.'
            ], 400);
        }

        if ($table->orders()->whereIn('status', ['pending', 'processing'])->exists()) {
            return response()->json([
                'message' => 'Cannot delete table with active orders.'
            ], 400);
        }

        $table->delete();

        return response()->json([
            'message' => 'Table deleted successfully.'
        ]);
    }

    /**
     * Get all reservations.
     */
    public function getReservations(Request $request)
    {
        $query = Reservation::with(['user', 'table']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date
        if ($request->has('date')) {
            $date = $request->date;
            $query->where(function ($q) use ($date) {
                $q->whereDate('start_date', $date)
                    ->orWhereDate('reservation_date', $date);
            });
        }
        
        $reservations = $query->orderByRaw('COALESCE(start_date, reservation_date) ASC')->paginate(500);
        
        return response()->json([
            'reservations' => $reservations
        ]);
    }

    /**
     * Update reservation status.
     */
    public function updateReservationStatus(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ]);

        $reservation->update($data);

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $reservation->load(['user', 'table'])
        ]);
    }

    /**
     * Get all categories.
     */
    public function getCategories()
    {
        $categories = Category::with('dishes')->ordered()->get();
        
        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Create a new category.
     */
    public function createCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'categories');
            if ($uploaded) {
                $data['image_path'] = $uploaded;
            }
        }

        $category = Category::create($data);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category
        ], 201);
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            // If the old image is a Cloudinary URL we can't delete it via Storage; skip deletion.
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'categories');
            if (!$uploaded) {
                return response()->json(['message' => 'Image upload failed.'], 500);
            }
            $data['image_path'] = $uploaded;
        }

        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ]);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has dishes
        if ($category->dishes()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing dishes.'
            ], 400);
        }

        // Delete image
        if ($category->image_path) {
            // Only delete from local storage if the path is a relative/local path
            if (!Str::startsWith($category->image_path, ['http://', 'https://'])) {
                Storage::disk('public')->delete($category->image_path);
            }
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }

    /**
     * Get all dishes.
     */
    public function getDishes(Request $request)
    {
        $query = Dish::with('category');
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }
        
        $dishes = $query->ordered()->paginate(500);
        
        return response()->json([
            'dishes' => $dishes
        ]);
    }

    /**
     * Create a new dish.
     */
    public function createDish(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_vegetarian' => ['boolean'],
            'is_vegan' => ['boolean'],
            'is_gluten_free' => ['boolean'],
            'is_available' => ['boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'ingredients' => ['nullable', 'string'],
            'ingredients_ar' => ['nullable', 'string'],
            'allergens' => ['nullable', 'string'],
            'allergens_ar' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'dishes');
            if ($uploaded) {
                $data['image_path'] = $uploaded;
            }
        }

        $dish = Dish::create($data);

        return response()->json([
            'message' => 'Dish created successfully.',
            'dish' => $dish->load('category')
        ], 201);
    }

    /**
     * Update a dish.
     */
    public function updateDish(Request $request, $id)
    {
        $dish = Dish::findOrFail($id);
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_vegetarian' => ['sometimes', 'boolean'],
            'is_vegan' => ['sometimes', 'boolean'],
            'is_gluten_free' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'ingredients' => ['nullable', 'string'],
            'ingredients_ar' => ['nullable', 'string'],
            'allergens' => ['nullable', 'string'],
            'allergens_ar' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {
            // If the old image is a Cloudinary URL we can't delete it via Storage; skip deletion.
            $uploaded = $this->uploadToCloudinary($request->file('image'), 'dishes');
            if (!$uploaded) {
                return response()->json(['message' => 'Image upload failed.'], 500);
            }
            $data['image_path'] = $uploaded;
        }

        $dish->update($data);

        return response()->json([
            'message' => 'Dish updated successfully.',
            'dish' => $dish->load('category')
        ]);
    }

    /**
     * Delete a dish.
     */
    public function deleteDish($id)
    {
        $dish = Dish::findOrFail($id);
        
        // Check if dish has active orders
        if ($dish->orderItems()->whereHas('order', function ($query) {
            $query->whereIn('status', ['pending', 'processing']);
        })->exists()) {
            return response()->json([
                'message' => 'Cannot delete dish with active orders.'
            ], 400);
        }

        // Delete image
        if ($dish->image_path) {
            if (!Str::startsWith($dish->image_path, ['http://', 'https://'])) {
                Storage::disk('public')->delete($dish->image_path);
            }
        }

        $dish->delete();

        return response()->json([
            'message' => 'Dish deleted successfully.'
        ]);
    }

    /**
     * Apply discount to a dish.
     */
    public function applyDishDiscount(Request $request, $dishId)
    {
        $request->validate([
            'discount_percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $dish = Dish::findOrFail($dishId);
        
        // Apply one-day discount as requested
        $dish->update([
            'discount_percentage' => $request->discount_percentage,
            'discount_start_date' => now(),
            'discount_end_date' => now()->addDay(),
            'is_on_discount' => true
        ]);

        return response()->json([
            'message' => 'Discount applied successfully',
            'dish' => [
                'id' => $dish->id,
                'name' => $dish->name,
                'original_price' => $dish->price,
                'discounted_price' => $dish->discounted_price,
                'discount_percentage' => $dish->discount_percentage,
                'discount_start_date' => $dish->discount_start_date,
                'discount_end_date' => $dish->discount_end_date,
                'savings' => $dish->price - $dish->discounted_price
            ]
        ]);
    }

    /**
     * Remove discount from a dish.
     */
    public function removeDishDiscount($dishId)
    {
        $dish = Dish::findOrFail($dishId);
        
        $dish->update([
            'discount_percentage' => null,
            'discount_start_date' => null,
            'discount_end_date' => null,
            'is_on_discount' => false
        ]);

        return response()->json([
            'message' => 'Discount removed successfully',
            'dish' => [
                'id' => $dish->id,
                'name' => $dish->name,
                'price' => $dish->price,
                'is_on_discount' => false
            ]
        ]);
    }

    /**
     * Get all dishes with their discount status for management.
     */
    public function getDishesWithDiscounts()
    {
        $dishes = Dish::with('category')
            ->select(['id', 'name', 'name_ar', 'price', 'category_id', 'discount_percentage', 
                     'discount_start_date', 'discount_end_date', 'is_on_discount', 'is_available'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->map(function ($dish) {
                return [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'name_ar' => $dish->name_ar,
                    'category' => $dish->category->name,
                    'original_price' => $dish->price,
                    'discounted_price' => $dish->is_on_discount ? $dish->discounted_price : null,
                    'discount_percentage' => $dish->discount_percentage,
                    'discount_start_date' => $dish->discount_start_date,
                    'discount_end_date' => $dish->discount_end_date,
                    'is_on_discount' => $dish->is_on_discount,
                    'is_currently_on_discount' => $dish->is_currently_on_discount,
                    'is_available' => $dish->is_available,
                    'savings' => $dish->is_on_discount ? ($dish->price - $dish->discounted_price) : 0
                ];
            });

        return response()->json([
            'dishes' => $dishes
        ]);
    }
}
