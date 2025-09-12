<?php

namespace App\Dto;

class TaskDTO
{
    public ?int $id = null;

    public ?string $description = null;

    public ?string $date = null;

    public ?bool $checked = null;

    public ?array $missingFields = [];

}