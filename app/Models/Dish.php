<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'category_id',
        'image_path',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'is_available',
        'preparation_time',
        'ingredients',
        'ingredients_ar',
        'allergens',
        'allergens_ar',
        'sort_order',
        'discount_percentage',
        'discount_start_date',
        'discount_end_date',
        'is_on_discount'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_gluten_free' => 'boolean',
        'is_available' => 'boolean',
        'preparation_time' => 'integer',
        'discount_percentage' => 'decimal:2',
        'discount_start_date' => 'datetime',
        'discount_end_date' => 'datetime',
        'is_on_discount' => 'boolean',
    ];

    /**
     * Get the category that owns the dish.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for this dish.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get users who favorited this dish.
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_dish_favorites')->withTimestamps();
    }

    /**
     * Get the reviews for this dish.
     */
    public function reviews()
    {
        return $this->hasMany(DishReview::class);
    }

    /**
     * Scope to get only available dishes.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to get vegetarian dishes.
     */
    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }

    /**
     * Scope to get vegan dishes.
     */
    public function scopeVegan($query)
    {
        return $query->query->where('is_vegan', true);
    }

    /**
     * Scope to get gluten-free dishes.
     */
    public function scopeGlutenFree($query)
    {
        return $query->where('is_gluten_free', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get preparation time in minutes.
     */
    public function getPreparationTimeMinutesAttribute()
    {
        return $this->preparation_time ? $this->preparation_time . ' min' : 'N/A';
    }

    /**
     * Scope to get dishes with active discounts.
     */
    public function scopeOnDiscount($query)
    {
        return $query->where('is_on_discount', true)
                    ->where('discount_start_date', '<=', now())
                    ->where('discount_end_date', '>=', now());
    }

    /**
     * Scope to get dishes ordered by average rating.
     */
    public function scopeByAverageRating($query, $direction = 'desc')
    {
        return $query->withAvg('reviews', 'rating')
                    ->orderBy('reviews_avg_rating', $direction);
    }

    /**
     * Get the average rating for this dish.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating');
    }

    /**
     * Get the total number of reviews for this dish.
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get the discounted price.
     */
    public function getDiscountedPriceAttribute()
    {
        if ($this->is_on_discount && $this->discount_percentage > 0) {
            $discountAmount = ($this->price * $this->discount_percentage) / 100;
            return $this->price - $discountAmount;
        }
        return $this->price;
    }

    /**
     * Check if the dish is currently on discount.
     */
    public function getIsCurrentlyOnDiscountAttribute()
    {
        return $this->is_on_discount 
            && $this->discount_start_date <= now() 
            && $this->discount_end_date >= now();
    }

    /**
     * Apply a one-day discount to this dish.
     */
    public function applyDiscount($percentage)
    {
        $this->update([
            'discount_percentage' => $percentage,
            'discount_start_date' => now(),
            'discount_end_date' => now()->addDay(),
            'is_on_discount' => true
        ]);
    }

    /**
     * Remove discount from this dish.
     */
    public function removeDiscount()
    {
        $this->update([
            'discount_percentage' => null,
            'discount_start_date' => null,
            'discount_end_date' => null,
            'is_on_discount' => false
        ]);
    }

    /**
     * Check if discount has expired and update status.
     */
    public function checkDiscountExpiry()
    {
        if ($this->is_on_discount && $this->discount_end_date < now()) {
            $this->removeDiscount();
            return true;
        }
        return false;
    }
}
