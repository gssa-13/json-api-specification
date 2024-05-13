<?php

namespace Tests\Feature\V1\Articles;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_owned_articles()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author, ['article:update']);

        $response = $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'Updated Article',
            'slug' => $article->slug,
            'content' => 'Updated content'
        ])->assertOk();

        $response->assertJsonApiResource( $article, [
            'title' => 'Updated Article',
            'slug' => $article->slug,
            'content' => 'Updated content'
        ]);
    }

    /** @test */
    public function cannot_update_articles_owned_by_other_users()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'Updated Article',
            'slug' => $article->slug,
            'content' => 'Updated content'
        ])->assertForbidden();
    }

    /** @test */
    public function guest_cannot_update_articles()
    {
        $article = Article::factory()->create();

        $this->patchJson( route('api.v1.articles.update', $article))
            ->assertJsonApiError(
                title: 'Unauthenticated',
                detail: 'This action requires authentication.',
                status: '401'
            );
    }

    /** @test */
    public function title_is_required()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'slug' => 'updated-article',
            'content' => 'Updated article content'
        ])->assertJsonApiValidationErrors('title');
    }

    /** @test */
    public function title_must_be_at_least_4_characters()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'Upd',
            'slug' => 'updated-article',
            'content' => 'Updated article content'
        ])->assertJsonApiValidationErrors('title');
    }

    /** @test */
    public function slug_is_required()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'Updated Article',
            'content' => 'Updated article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_be_unique()
    {
        $article1 = Article::factory()->create();
        $article2 = Article::factory()->create();

        Sanctum::actingAs($article1->author);

        $this->patchJson( route('api.v1.articles.update', $article2), [
            'title' => 'New Article',
            'slug' => $article1->slug,
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_only_contain_letters_numbers_and_dashes()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'New Article',
            'slug' => '$%^&',
            'content' => 'Article content'
        ])->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_contain_underscores()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'New Article',
            'slug' => 'with_underscore',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_underscore') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_start_with_dashes()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'New Article',
            'slug' => '-start-with-dash',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_starting_dashes') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function slug_must_not_end_with_dashes()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'New Article',
            'slug' => 'end-with-dash-',
            'content' => 'Article content'
        ])->assertSee( __('validation.no_ends_with_dash') )
            ->assertJsonApiValidationErrors('slug');
    }

    /** @test */
    public function content_is_required()
    {
        $article = Article::factory()->create();

        Sanctum::actingAs($article->author);

        $this->patchJson( route('api.v1.articles.update', $article), [
            'title' => 'New Article',
            'slug' => 'updated-article'
        ])->assertJsonApiValidationErrors('content');
    }
}
