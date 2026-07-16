<?php

namespace Pterodactyl\Transformers\Api\Client;

use Pterodactyl\Models\Announcement;

class AnnouncementTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return Announcement::RESOURCE_NAME;
    }

    /**
     * Transform an announcement into a representation that can be consumed by
     * the client.
     */
    public function transform(Announcement $model): array
    {
        return [
            'id' => $model->id,
            'title' => $model->title,
            'body' => $model->body,
            'level' => $model->level,
            'priority' => $model->priority,
            'created_at' => $model->created_at->toAtomString(),
        ];
    }
}
