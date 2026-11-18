<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Decides which fields of a service leave the server
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name_en'    => $this->name_en,
            'name_ar'    => $this->name_ar,
            'unit_price' => (float) $this->unit_price,
            'is_active'  => (bool) $this->is_active,
            // created_at and updated_at are not needed by the website
        ];
    }
}
