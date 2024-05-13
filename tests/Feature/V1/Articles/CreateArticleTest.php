<?php

namespace Tests\Feature\V1\Articles;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

use App\Models\Article;
use App\Models\Category;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_articles()
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        Sanctum::actingAs($author, ['article:create']);

        $response = $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'new-article',
            'content' => 'Article content',
            '_relationships' => [
                'category' => $category,
                'author' => $author
            ]
        ])->assertCreated();

        $article = Article::first();

        $response->assertJsonApiResource($article, [
            'title' => 'New Article',
            'slug' => 'new-article',
            'content' => 'Article content',
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'New Article',
            'user_id' => $author->id,
            'category_id' => $category->id
        ]);
    }

    /** @test */
    public function guest_cannot_create_articles()
    {
//        $this->withoutExceptionHandling();
        $this->postJson( route('api.v1.articles.store'))
            ->assertJsonApiError(
                title: 'Unauthenticated',
                detail: 'This action requires authentication.',
                status: '401'
            );

        $this->assertDatabaseCount('articles', 0);
    }

    /** @test */
    public function title_is_required()
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson( route('api.v1.articles.store'), [
            'slug' => 'new-article',
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('title');
    }

    /** @test */
    public function title_must_be_at_least_4_characters()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New',
            'slug' => 'new-article',
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('title');
    }

    /** @test */
    public function slug_is_required()
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_be_unique()
    {
        Sanctum::actingAs(User::factory()->create());

        $article = Article::factory()->create();

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => $article->slug,
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_only_contain_letters_numbers_and_dashes()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => '$%^&',
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_contain_underscores()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'with_underscore',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_underscore') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_start_with_dashes()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => '-start-with-dash',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_starting_dashes') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_end_with_dashes()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'end-with-dash-',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_ends_with_dash') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function content_is_required()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'new-article'
        ])->assertJsonApiValidationErrors('content');
    }

    /** @test */
    public function category_relationship_is_required()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'new-article',
            'content' => 'Article Content'
        ])->assertJsonApiValidationErrors('relationships.category');
    }

    /** @test */
    public function category_must_exist_in_database()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson( route('api.v1.articles.store'), [
            'title' => 'New Article',
            'slug' => 'new-article',
            'content' => 'Article Content',
            '_relationships' => [
                'category' => Category::factory()->make()
            ]
        ])->assertJsonApiValidationErrors('relationships.category');
    }
}
