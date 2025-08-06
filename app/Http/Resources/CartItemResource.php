<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id' => $this->id,
          'name'=> $this->product_variant?->product?->name,
          'thumbnail' => $this->product_variant?->product?->thumbnail,
          'quantity' => $this->quantity,
           'variant' => new VariantResource($this->product_variant)
        ];
    }
}
