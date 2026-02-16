<?php

namespace App\Domain\Page\DTOs;

class PageData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
