<?php

namespace App\Models;

use App\ImageCreditType;
use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property ImageCreditType|null $credit_type
 * @property string|null $credit_author
 * @property string|null $credit_service
 * @property string|null $credit_service_url
 * @property string $file_path
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'credit_type', 'credit_author', 'credit_service', 'credit_service_url', 'file_path', 'user_id'])]
class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_type' => ImageCreditType::class,
        ];
    }

    /**
     * The admin who uploaded this image.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The stories written for this image.
     *
     * @return HasMany<Story, $this>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Whether public credit attribution should be shown.
     */
    public function hasCredit(): bool
    {
        return $this->creditLine() !== null;
    }

    /**
     * Public credit line shown under the title, when attribution is set.
     */
    public function creditLine(): ?string
    {
        if ($this->credit_type === null || $this->credit_author === null || $this->credit_service === null) {
            return null;
        }

        $author = trim($this->credit_author);
        $service = trim($this->credit_service);

        if ($author === '' || $service === '') {
            return null;
        }

        return $this->credit_type->creditPrefix().$author.'　'.$service;
    }

    /**
     * Normalized external URL for the credit service, if set.
     */
    public function creditServiceUrl(): ?string
    {
        if ($this->credit_service_url === null) {
            return null;
        }

        $url = trim($this->credit_service_url);

        return $url === '' ? null : $url;
    }

    /**
     * Get the public URL for this image, if the file exists.
     */
    public function url(): ?string
    {
        if ($this->file_path === null) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->file_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }
}
