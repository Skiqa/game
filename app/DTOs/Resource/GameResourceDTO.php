<?php

namespace app\DTOs\Resource;

use WendellAdriel\ValidatedDTO\ResourceDTO;

class GameResourceDTO extends ResourceDTO
{

    public string $provider;
    public string $external_id;
    public string $title;
    public string $category;
    public ?float $rtp;
    public string $created_at;


    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
       return [];
    }
}
