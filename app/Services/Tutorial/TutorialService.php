<?php

namespace App\Services\Tutorial;

use App\Models\Tutorial;

class TutorialService
{
    /**
     * Store a new tutorial.
     */
    public function storeTutorial(array $data, int $authorId): Tutorial
    {
        $data['author_id'] = $authorId;
        
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        // Convert common YouTube URLs to embed URLs if needed
        $data['video_url'] = $this->parseVideoUrl($data['video_url']);

        return Tutorial::create($data);
    }

    /**
     * Update an existing tutorial.
     */
    public function updateTutorial(Tutorial $tutorial, array $data): Tutorial
    {
        if ($data['status'] === 'published' && !$tutorial->published_at) {
            $data['published_at'] = now();
        } elseif ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $data['video_url'] = $this->parseVideoUrl($data['video_url']);

        $tutorial->update($data);

        return $tutorial;
    }

    /**
     * Parse video URL (basic youtube conversion for embed).
     */
    private function parseVideoUrl(string $url): string
    {
        if (str_contains($url, 'youtube.com/watch?v=')) {
            $videoId = explode('v=', $url)[1];
            $ampersandPosition = strpos($videoId, '&');
            if ($ampersandPosition !== false) {
                $videoId = substr($videoId, 0, $ampersandPosition);
            }
            return 'https://www.youtube.com/embed/' . $videoId;
        }
        
        if (str_contains($url, 'youtu.be/')) {
            $videoId = explode('youtu.be/', $url)[1];
            $queryPosition = strpos($videoId, '?');
            if ($queryPosition !== false) {
                $videoId = substr($videoId, 0, $queryPosition);
            }
            return 'https://www.youtube.com/embed/' . $videoId;
        }

        return $url;
    }
}
