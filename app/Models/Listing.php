<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Listing extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'condition',
        'status',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'published_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->quality(85)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->fit(Fit::Contain, 1200, 1200)
            ->quality(90)
            ->nonQueued();
    }

    public function resetForModerationReview(): void
    {
        $this->moderation_status = 'pending';
        $this->moderation_reason = null;
        $this->moderated_at = null;

        if ($this->status === 'published') {
            $this->status = 'draft';
            $this->published_at = null;
        }
    }
}
