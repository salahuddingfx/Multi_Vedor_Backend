<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Site;
use App\Models\ReviewMedia;
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
                ->with(['product:id,name,slug', 'media'])
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
            'images.*'      => 'nullable|image|max:5120', // Max 5MB per image
            'videos.*'      => 'nullable|mimes:mp4,mov,avi,wmv|max:20480', // Max 20MB per video
        ]);

        $review = Review::create([
            'site_id'       => $site->id,
            'product_id'    => $request->product_id,
            'customer_name' => $request->customer_name,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
            'is_approved'   => false,
        ]);

        // Handle Image Uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews/images', 'public');
                ReviewMedia::create([
                    'review_id' => $review->id,
                    'file_path' => asset('storage/' . $path),
                    'type'      => 'image'
                ]);
            }
        }

        // Handle Video Uploads
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('reviews/videos', 'public');
                ReviewMedia::create([
                    'review_id' => $review->id,
                    'file_path' => asset('storage/' . $path),
                    'type'      => 'video'
                ]);
            }
        }

        return $this->sendResponse($review->load('media'), 'Review submitted successfully. Waiting for approval.');
    }

    // For Admin: Get all reviews
    public function getAdminReviews(Request $request)
    {
        $siteId = $request->site_id;
        $reviews = Review::where('site_id', $siteId)
            ->with(['product', 'media'])
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
