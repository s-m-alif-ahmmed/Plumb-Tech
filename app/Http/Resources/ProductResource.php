<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public string $formatStyle;
    public string $type;


    public function __construct($resource, $formatStyle = 'default',$type= 'web')
    {
        parent::__construct($resource);
        $this->formatStyle = $formatStyle;
        $this->type = $type;
    }
    public function toArray(Request $request): array
    {
        if ($this->formatStyle == 'default') {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'gallery_images' => $this->gallery_images,
                'thumbnail' => $this->thumbnail,
                'description' => $this->description,
                'category' => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'thumbnail' => $this->category->thumbnail,
                ],
                'variants' => $this->type == 'api' ? ApiVariantResource::collection($this->getUniqueVariants()) : VariantResource::collection($this->variants),
            ];
        }else{
            return [
                'id' => $this->id,
                'name' => $this->name,
                'thumbnail' => $this->thumbnail,
                'description' => $this->description,
                'start_price' => isset($this->variants[0]) ? $this->variants[0]->price : '0.00',
            ];
        }
    }

    private function getUniqueVariants()
    {
        // Using collection to filter out duplicates based on the first attribute value
        return $this->variants->unique(function ($variant) {
            return optional($variant->attributeValues->first())->value;
        });
    }
}
