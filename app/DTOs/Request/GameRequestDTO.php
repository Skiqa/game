<?php

namespace app\DTOs\Request;

use WendellAdriel\ValidatedDTO\ValidatedDTO;

class GameRequestDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'id'       => ['required', 'string'],
            'title'    => ['required', 'string'],
            'category' => ['required', 'in:slots,live,table'],
            'active'   => ['required', 'boolean'],
            'rtp'      => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    protected function casts(): array
    {
        return [];
    }

    protected function defaults(): array
    {
        return [];
    }
}
