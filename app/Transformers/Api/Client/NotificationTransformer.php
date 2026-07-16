<?php

namespace Pterodactyl\Transformers\Api\Client;

use Illuminate\Notifications\DatabaseNotification;

class NotificationTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return 'notification';
    }

    /**
     * Transform a stored database notification into a representation that can
     * be consumed by the client.
     */
    public function transform(DatabaseNotification $model): array
    {
        $data = $model->data ?? [];

        return [
            'id' => $model->id,
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? '',
            'level' => $data['level'] ?? 'info',
            'action_url' => $data['action_url'] ?? null,
            'read_at' => $model->read_at ? $model->read_at->toAtomString() : null,
            'created_at' => $model->created_at->toAtomString(),
        ];
    }
}
