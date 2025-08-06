<?php

namespace App\Http\Resources;

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the first attribute value if it exists
        $firstAttributeValue = $this->attributeValues->first();

        return [
            'id' => $this->id,
            'stock_quantity' => $this->stock_quantity,
            'price' => $this->price,
            'attributes' => $this->formatAttributes(),
        ];
    }

    /**
     * Format the attribute values for the variant.
     */
    private function formatAttributes()
    {
        // Group attribute values by attribute and map to a more readable structure
        return $this->attributeValues->groupBy('attribute.id')->map(function ($value, $attributeName) {
            return [
                'id' => $value->first()->attribute->id,
                'name' => $value->first()->attribute->name,
                'value' => new AttributeValueResource($value->first()),
            ];
        })->values();
    }

}
