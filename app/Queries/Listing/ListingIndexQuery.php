<?php

namespace App\Queries\Listing;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListingIndexQuery
{
    public function build(Request $request): Builder
    {
        $query = Listing::query()
            ->with('category')
            ->where('status', 'published')
            ->where('moderation_status', 'approved')
            ->whereHas('category', function ($query) {
                $query
                    ->where('status', 'approved')
                    ->where('is_active', true);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('university_id'), function ($query) use ($request) {
                $query->whereHas('user.profile', function ($query) use ($request) {
                    $query->where('university_id', $request->integer('university_id'));
                });
            })
            ->when($request->filled('price_min'), function ($query) use ($request) {
                $query->where('price', '>=', $request->input('price_min'));
            })
            ->when($request->filled('price_max'), function ($query) use ($request) {
                $query->where('price', '<=', $request->input('price_max'));
            })
            ->when($request->filled('condition'), function ($query) use ($request) {
                $query->where('condition', $request->input('condition'));
            });

        $sort = $request->input('sort', 'newest');

        return match ($sort) {
            'oldest' => $query->oldest(),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }
}
