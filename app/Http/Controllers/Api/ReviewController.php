<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Site;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    // For Storefront: Get approved reviews
    public function index(Request $request, $site_slug)
    {
        try {
            $site = Site::where('slug', $site_slug)->first();
            if (!$site) return $this->sendError('Site not found.');

            $productId = $request->input('product_id');
            $limit     = (int) $request->input('limit', 10);

            $query = Review::where('site_id', $site->id)
                           ->where('is_approved', true);

            if ($productId) {
                $query->where('product_id', $productId);
            }

            $reviews = $query
                ->with('product:id,name,slug')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $this->sendResponse($reviews, 'Reviews retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve reviews: ' . $e->getMessage(), [], 500);
        }
    }

    // For Storefront: Submit a new review
    public function store(Request $request, $site_slug)
    {
        $site = Site::where('slug', $site_slug)->first();
        if (!$site) return $this->sendError('Site not found.');

        $request->validate([
            'product_id'    => 'nullable|exists:products,id',
            'customer_name' => 'required|string|max:255',
            'rating'        => 'required|numeric|min:1|max:5',
            'comment'       => 'nullable|string',
        ]);

        $review = Review::create([
            'site_id'       => $site->id,
            'product_id'    => $request->product_id,
            'customer_name' => $request->customer_name,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
            'is_approved'   => false,
        ]);

        return $this->sendResponse($review, 'Review submitted successfully. Waiting for approval.');
    }

    // For Admin: Get all reviews
    public function getAdminReviews(Request $request)
    {
        $siteId = $request->site_id;
        $reviews = Review::where('site_id', $siteId)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();
        return $this->sendResponse($reviews, 'Admin reviews retrieved successfully.');
    }

    // For Admin: Approve/Update a review
    public function updateAdminReview(Request $request, $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return $this->sendError('Review not found.');
        }

        $review->update($request->only(['is_approved', 'admin_reply']));
        return $this->sendResponse($review, 'Review updated successfully.');
    }

    // For Admin: Delete a review
    public function deleteAdminReview($id)
    {
        $review = Review::find($id);
        if (!$review) {
            return $this->sendError('Review not found.');
        }

        $review->delete();
        return $this->sendResponse([], 'Review deleted successfully.');
    }
}
