<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Format each variant to include a single attribute with name and value
        return [
            'id' => $this->id,
            'stock_quantity' => $this->stock_quantity,
            'price' => $this->price,
            'attribute' => $this->formatSingleAttribute(),
        ];
    }

    /**
     * Format the attributes to return only one attribute with name and value.
     * Assuming that you're dealing with only one attribute for this resource.
     */
    private function formatSingleAttribute(): ?array
    {
        // Assume we're focusing on the first attribute value for simplicity
        $attributeValue = $this->attributeValues->first();

        if ($attributeValue) {
            return [
                'id' => $attributeValue->id,
                'name' => $attributeValue->attribute->name,
                'value' => $attributeValue->value
            ];
        }

        return null;
    }
}
