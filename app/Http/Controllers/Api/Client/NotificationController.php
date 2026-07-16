<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Transformers\Api\Client\NotificationTransformer;

class NotificationController extends ClientApiController
{
    /**
     * Returns all the notifications that exist for the authenticated user,
     * newest first.
     */
    public function index(ClientApiRequest $request): array
    {
        $notifications = $request->user()->notifications()->latest()->get();

        return $this->fractal->collection($notifications)
            ->transformWith($this->getTransformer(NotificationTransformer::class))
            ->addMeta([
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ])
            ->toArray();
    }

    /**
     * Marks a single notification as read.
     */
    public function read(ClientApiRequest $request, string $notification): JsonResponse
    {
        /** @var DatabaseNotification $model */
        $model = $request->user()->notifications()->findOrFail($notification);
        $model->markAsRead();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Marks every unread notification for the user as read.
     */
    public function readAll(ClientApiRequest $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a single notification belonging to the user.
     */
    public function delete(ClientApiRequest $request, string $notification): JsonResponse
    {
        $request->user()->notifications()->where('id', $notification)->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
