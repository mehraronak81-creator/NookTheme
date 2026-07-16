<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Pterodactyl\Models\Announcement;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Transformers\Api\Client\AnnouncementTransformer;

class AnnouncementController extends ClientApiController
{
    /**
     * Returns the announcements that are currently visible to clients, ordered
     * by priority (highest first) then most recent.
     */
    public function index(ClientApiRequest $request): array
    {
        $announcements = Announcement::query()
            ->visible()
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();

        return $this->fractal->collection($announcements)
            ->transformWith($this->getTransformer(AnnouncementTransformer::class))
            ->toArray();
    }
}
