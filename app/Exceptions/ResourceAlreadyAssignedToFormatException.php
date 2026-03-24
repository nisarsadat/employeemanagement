<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceAlreadyAssignedToFormatException extends Exception
{
    public function __construct(
        public readonly int|string $resourceId,
        public readonly ?string $existingModel,
        public readonly int|string|null $existingRecordId,
        public readonly ?string $existingLabel = null,
        ?string $message = null
    ) {
        parent::__construct(
            $message ?: 'دا منبع مخکې له مخکې په بل فورمټ کې استفاده شوی دی.'
        );
    }

    public function render(Request $request): JsonResponse
    {
        $message = "دا منبع مخکې له مخکې په بل فورمټ کې استفاده شوی دی.|";

        if ($this->existingLabel) {
            $message .= "د فورمټ نوم: {$this->existingLabel}.|";
        }

        if ($this->existingRecordId) {
            $message .= " د ثبت شوې فورمټ آی‌ډي: {$this->existingRecordId}.|";
        }

        if ($this->resourceId) {
            $message .= " د منبع آی‌ډي: {$this->resourceId}.|";
        }

        return response()->json([
            'message' => $message,
            'errors' => [
                'resource_id' => [$message],
            ],
            'resource_id' => $this->resourceId,
            'existing_record' => [
                'label' => $this->existingLabel,
                'id'    => $this->existingRecordId,
            ],
        ], 422);
    }
}