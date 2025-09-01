<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\DishReview;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get the complete menu with categories and dishes.
     */
    public function getMenu()
    {
        $categories = Category::with(['dishes' => function ($query) {
            $query->available()->ordered();
        }])
        ->active()
        ->ordered()
        ->get();
        
        return response()->json([
            'menu' => $categories
        ]);
    }

    /**
     * Get dishes by category.
     */
    public function getDishesByCategory($categoryId)
    {
        $category = Category::with(['dishes' => function ($query) {
            $query->available()->ordered();
        }])
        ->active()
        ->findOrFail($categoryId);
        
        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * Search dishes by name or description.
     */
    public function searchDishes(Request $request)
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'vegetarian' => ['boolean'],
            'vegan' => ['boolean'],
            'gluten_free' => ['boolean'],
        ]);

        $query = Dish::with('category')->available();

        // Search in name and description
        $searchQuery = $request->query;
        $query->where(function ($q) use ($searchQuery) {
            $q->where('name', 'like', "%{$searchQuery}%")
              ->orWhere('description', 'like', "%{$searchQuery}%");
        });

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by dietary preferences
        if ($request->boolean('vegetarian')) {
            $query->vegetarian();
        }

        if ($request->boolean('vegan')) {
            $query->vegan();
        }

        if ($request->boolean('gluten_free')) {
            $query->glutenFree();
        }

        $dishes = $query->ordered()->paginate(15);

        return response()->json([
            'dishes' => $dishes
        ]);
    }

    /**
     * Get popular dishes (most ordered).
     */
    public function getPopularDishes()
    {
        $popularDishes = Dish::with('category')
            ->available()
            ->withCount(['orderItems as order_count' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->where('created_at', '>=', now()->subDays(30));
                });
            }])
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'popular_dishes' => $popularDishes
        ]);
    }

    /**
     * Get dishes by dietary preference.
     */
    public function getDishesByDietaryPreference(Request $request)
    {
        $request->validate([
            'preference' => ['required', 'in:vegetarian,vegan,gluten_free'],
        ]);

        $preference = $request->preference;
        $query = Dish::with('category')->available();

        switch ($preference) {
            case 'vegetarian':
                $query->vegetarian();
                break;
            case 'vegan':
                $query->vegan();
                break;
            case 'gluten_free':
                $query->glutenFree();
                break;
        }

        $dishes = $query->ordered()->get();

        return response()->json([
            'dietary_preference' => $preference,
            'dishes' => $dishes
        ]);
    }

    /**
     * Get menu categories.
     */
    public function getCategories()
    {
        $categories = Category::active()->ordered()->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Get dish details.
     */
    public function getDish($id)
    {
        $dish = Dish::with('category')->available()->findOrFail($id);

        return response()->json([
            'dish' => $dish
        ]);
    }

    /**
     * Get menu highlights (featured dishes).
     */
    public function getMenuHighlights()
    {
        $highlights = Dish::with('category')
            ->available()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return response()->json([
            'highlights' => $highlights
        ]);
    }

    /**
     * Get top 5 recommended dishes based on ratings.
     */
    public function getRecommendations()
    {
        $recommendations = Dish::with(['category', 'reviews'])
            ->available()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereHas('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($dish) {
                return [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'name_ar' => $dish->name_ar,
                    'description' => $dish->description,
                    'description_ar' => $dish->description_ar,
                    'price' => $dish->price,
                    'formatted_price' => $dish->formatted_price,
                    'image_path' => $dish->image_path,
                    'category' => $dish->category,
                    'average_rating' => round($dish->average_rating, 1),
                    'reviews_count' => $dish->reviews_count,
                    'is_vegetarian' => $dish->is_vegetarian,
                    'is_vegan' => $dish->is_vegan,
                    'is_gluten_free' => $dish->is_gluten_free,
                    'preparation_time' => $dish->preparation_time,
                ];
            });

        return response()->json([
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Get dishes with active discounts.
     */
    public function getDiscounts()
    {
        $discountedDishes = Dish::with('category')
            ->available()
            ->onDiscount()
            ->orderBy('discount_percentage', 'desc')
            ->get()
            ->map(function ($dish) {
                return [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'name_ar' => $dish->name_ar,
                    'description' => $dish->description,
                    'description_ar' => $dish->description_ar,
                    'original_price' => $dish->price,
                    'discounted_price' => $dish->discounted_price,
                    'discount_percentage' => $dish->discount_percentage,
                    'savings' => $dish->price - $dish->discounted_price,
                    'formatted_original_price' => '$' . number_format($dish->price, 2),
                    'formatted_discounted_price' => '$' . number_format($dish->discounted_price, 2),
                    'image_path' => $dish->image_path,
                    'category' => $dish->category,
                    'discount_end_date' => $dish->discount_end_date,
                    'is_vegetarian' => $dish->is_vegetarian,
                    'is_vegan' => $dish->is_vegan,
                    'is_gluten_free' => $dish->is_gluten_free,
                    'preparation_time' => $dish->preparation_time,
                ];
            });

        return response()->json([
            'discounted_dishes' => $discountedDishes
        ]);
    }

    /**
     * Submit a review for a dish.
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'dish_id' => ['required', 'exists:dishes,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000']
        ]);

        $dish = Dish::findOrFail($request->dish_id);
        
        // Check if user already reviewed this dish
        $existingReview = DishReview::where('dish_id', $request->dish_id)
                                   ->where('user_id', auth()->id())
                                   ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);
            $review = $existingReview;
        } else {
            // Create new review
            $review = DishReview::create([
                'dish_id' => $request->dish_id,
                'user_id' => auth()->id(),
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);
        }

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('user:id,name')
        ]);
    }

    /**
     * Get reviews for a specific dish.
     */
    public function getDishReviews($dishId)
    {
        $dish = Dish::findOrFail($dishId);
        
        $reviews = DishReview::with('user:id,name')
                            ->where('dish_id', $dishId)
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        $averageRating = $dish->average_rating;
        $totalReviews = $dish->reviews_count;

        return response()->json([
            'dish' => [
                'id' => $dish->id,
                'name' => $dish->name,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews
            ],
            'reviews' => $reviews
        ]);
    }
}
