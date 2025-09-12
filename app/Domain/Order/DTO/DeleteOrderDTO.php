<?php

namespace App\Domain\Order\DTO;

use App\Domain\Order\Exception\OrderException;
use Illuminate\Support\Facades\Validator;

class DeleteOrderDTO
{
    public function __construct(
        public readonly int $id,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $validator = Validator::make([
            'id' => $this->id,
        ], [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw OrderException::validationFailed($validator->errors());
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
