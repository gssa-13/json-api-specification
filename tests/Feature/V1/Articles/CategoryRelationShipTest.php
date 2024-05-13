<?php

namespace Tests\Feature\V1\Articles;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryRelationShipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_fetch_the_associated_category_identifier()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.relationships.category', $article);

        $response = $this->getJson($url);

        $response->assertExactJson([
            'data' => [
                'id' => $article->category->getRouteKey(),
                'type' => 'categories'
            ]
        ]);
    }

    /** @test */
    public function can_fetch_the_associated_category_resource()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.category', $article);

        $response = $this->getJson($url);

        $response->assertJson([
            'data' => [
                'id' => $article->category->getRouteKey(),
                'type' => 'categories',
                'attributes' => [
                    'name' => $article->category->name
                ]
            ]
        ]);
    }

    /** @test */
    public function can_update_the_associate_category()
    {
        $article = Article::factory()->create();
        $category = Category::factory()->create();

        $url = route('api.v1.articles.relationships.category', $article);

        $response = $this->patchJson($url, [
            'data' => [
                'id' => $category->getRouteKey(),
                'type' => 'categories',
            ]
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $category->getRouteKey(),
                'type' => 'categories',
            ]
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'category_id' => $category->id
        ]);
    }

    /** @test */
    public function category_must_exist_in_database()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.relationships.category', $article);

        $this->patchJson($url, [
            'data' => [
                'id' => 'missing-key',
                'type' => 'categories',
            ]
        ])->assertJsonApiValidationErrors('data.id');

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'category_id' => $article->category_id
        ]);
    }
}
