<?php

namespace App\Services\News;

use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class NewsService
{
    /**
     * Store a new news article, handling image upload if present.
     */
    public function storeNews(array $data, ?UploadedFile $image, int $authorId): News
    {
        $data['author_id'] = $authorId;

        // Sanitizar HTML para prevenir XSS antes de persistir en BD
        if (isset($data['content'])) {
            $data['content'] = clean($data['content']);
        }

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        if ($image) {
            $data['image_path'] = $this->uploadImage($image);
        }

        return News::create($data);
    }

    /**
     * Update an existing news article.
     */
    public function updateNews(News $news, array $data, ?UploadedFile $image): News
    {
        // Sanitizar HTML para prevenir XSS antes de persistir en BD
        if (isset($data['content'])) {
            $data['content'] = clean($data['content']);
        }

        if ($data['status'] === 'published' && !$news->published_at) {
            $data['published_at'] = now();
        } elseif ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        if ($image) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            $data['image_path'] = $this->uploadImage($image);
        }

        $news->update($data);

        return $news;
    }

    /**
     * Handle the image upload.
     */
    private function uploadImage(UploadedFile $image): string
    {
        return $image->store('news', 'public');
    }
}
