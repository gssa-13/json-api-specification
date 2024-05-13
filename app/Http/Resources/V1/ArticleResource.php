<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\JsonApi\Traits\JsonApiResource;

class ArticleResource extends JsonResource
{
    use JsonApiResource;

    public function toJsonApi(): array
    {
        return [
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'content' => $this->resource->content
        ];
    }

    public function getRelationshipLinks(): array
    {
        return ['category', 'author'];
    }

    public function getIncludes(): array
    {
        return [
            CategoryResource::make($this->whenLoaded('category')),
            AuthorResource::make($this->whenLoaded('author')),
        ];
    }
}
