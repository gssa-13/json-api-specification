<?php

namespace Tests\Feature\V1\Articles;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorRelationShipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_fetch_the_associated_author_identifier()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.relationships.author', $article);

        $response = $this->getJson($url);

        $response->assertExactJson([
            'data' => [
                'id' => $article->author->getRouteKey(),
                'type' => 'authors'
            ]
        ]);
    }

    /** @test */
    public function can_fetch_the_associated_author_resource()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.author', $article);

        $response = $this->getJson($url);

        $response->assertJson([
            'data' => [
                'id' => $article->author->getRouteKey(),
                'type' => 'authors',
                'attributes' => [
                    'name' => $article->author->name
                ]
            ]
        ]);
    }

    /** @test */
    public function can_update_the_associate_author()
    {
        $article = Article::factory()->create();
        $author = User::factory()->create();

        $url = route('api.v1.articles.relationships.author', $article);

        $response = $this->patchJson($url, [
            'data' => [
                'id' => $author->getRouteKey(),
                'type' => 'authors',
            ]
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $author->getRouteKey(),
                'type' => 'authors',
            ]
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'user_id' => $author->id
        ]);
    }

    /** @test */
    public function author_must_exist_in_database()
    {
        $article = Article::factory()->create();

        $url = route('api.v1.articles.relationships.author', $article);

        $this->patchJson($url, [
            'data' => [
                'id' => 'missing-autor',
                'type' => 'authors',
            ]
        ])->assertJsonApiValidationErrors('data.id');


        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'user_id' => $article->user_id
        ]);
    }
}
