<?php

namespace Tests\Feature\V1\Articles;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaginateArticlesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_paginate_articles()
    {
        $articles = Article::factory()->count(6)->create();

        // articles?page[size]=2&page[number]=2

        $url = route('api.v1.articles.index', [
            'page' => [
                'size' => 2,
                'number' => 2
            ]
        ]);

        $response = $this->getJson($url);

        $response->assertSee([
            $articles[2]->title,
            $articles[3]->title
        ]);

        $response->assertDontSee([
            $articles[0]->title,
            $articles[1]->title,
            $articles[4]->title,
            $articles[5]->title
        ]);

        $response->assertJsonStructure([
            'links' => ['first', 'last', 'prev', 'next']
        ]);

        $firstLink = urldecode( $response->json('links.first') );
        $lastLink = urldecode( $response->json('links.last') );
        $prevLink = urldecode( $response->json('links.prev') );
        $nextLink = urldecode( $response->json('links.next') );

        $this->assertStringContainsString('page[number]=1', $firstLink);
        $this->assertStringContainsString('page[size]=2', $firstLink);

        $this->assertStringContainsString('page[number]=3', $lastLink);
        $this->assertStringContainsString('page[size]=2', $lastLink);

        $this->assertStringContainsString('page[number]=1', $prevLink);
        $this->assertStringContainsString('page[size]=2', $prevLink);

        $this->assertStringContainsString('page[number]=3', $nextLink);
        $this->assertStringContainsString('page[size]=2', $nextLink);

    }

    /** @test */
    public function can_paginate_sorted_articles()
    {
        Article::factory()->create([ 'title' => 'C title' ]);
        Article::factory()->create([ 'title' => 'A title' ]);
        Article::factory()->create([ 'title' => 'B title' ]);

        // articles?sort=title&page[size]=1&page[number]=2

        $url = route('api.v1.articles.index', [
            'sort' => 'title',
            'page' => [
                'size' => 1,
                'number' => 2
            ]
        ]);

        $response = $this->getJson($url);

        $response->assertSee([
            'B title'
        ]);

        $response->assertDontSee([
            'A title',
            'C title'
        ]);


        $firstLink = urldecode( $response->json('links.first') );
        $lastLink = urldecode( $response->json('links.last') );
        $prevLink = urldecode( $response->json('links.prev') );
        $nextLink = urldecode( $response->json('links.next') );

        $this->assertStringContainsString('sort=title', $firstLink);
        $this->assertStringContainsString('sort=title', $lastLink);
        $this->assertStringContainsString('sort=title', $prevLink);
        $this->assertStringContainsString('sort=title', $nextLink);

    }

    /** @test */
    public function can_paginate_filtered_articles()
    {
        Article::factory()->count(4)->create();
        Article::factory()->create([ 'title' => 'C Laravel' ]);
        Article::factory()->create([ 'title' => 'A Laravel' ]);
        Article::factory()->create([ 'title' => 'B Laravel' ]);

        // articles?filter[title]=Laravel&page[size]=1&page[number]=2

        $url = route('api.v1.articles.index', [
            'filter[title]' => 'Laravel',
            'page' => [
                'size' => 1,
                'number' => 2
            ]
        ]);

        $response = $this->getJson($url);

        $firstLink = urldecode( $response->json('links.first') );
        $lastLink = urldecode( $response->json('links.last') );
        $prevLink = urldecode( $response->json('links.prev') );
        $nextLink = urldecode( $response->json('links.next') );

        $this->assertStringContainsString('filter[title]=Laravel', $firstLink);
        $this->assertStringContainsString('filter[title]=Laravel', $lastLink);
        $this->assertStringContainsString('filter[title]=Laravel', $prevLink);
        $this->assertStringContainsString('filter[title]=Laravel', $nextLink);

    }
}
