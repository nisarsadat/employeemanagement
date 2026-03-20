<?php

namespace App\Exceptions;

use Exception;

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
            $message ?: 'This resource is already assigned to another single-format record.'
        );
    }
    public function register(): void
    {
        $this->renderable(function (ResourceAlreadyAssignedToFormatException $e, $request) {
            return response()->json([
                'message' => 'این ریسورس قبلاً در فورمت دیگری ثبت شده است.',
                'errors' => [
                    'resource_id' => [
                        'این ریسورس قبلاً در یک فورمت دیگر استفاده شده است.'
                    ],
                ],
                'resource_id' => $e->resourceId,
                'existing_record' => [
                    'model' => $e->existingModel,
                    'label' => $e->existingLabel,
                    'id'    => $e->existingRecordId,
                ],
            ], 422);
        });
    }
}