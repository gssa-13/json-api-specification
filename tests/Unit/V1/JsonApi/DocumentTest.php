<?php

namespace Tests\Unit\V1\JsonApi;

use Mockery;
use PHPUnit\Framework\TestCase;

use App\JsonApi\Document;

class DocumentTest extends TestCase
{

    /** @test */
    public function can_create_json_api_documents()
    {
        $category = Mockery::mock('Category', function ($mock) {
            $mock->shouldReceive('getResourceType')->andReturn('categories');
            $mock->shouldReceive('getRouteKey')->andReturn('category-id');
        });

        $document =  Document::type('articles')->id('article-id')
            ->attributes([
                'title' => 'Article title'
            ])->relationshipData([
                'category' => $category
            ])->toArray();

        $expected = array(
            'data' => array(
                'type' => 'articles',
                'id' => 'article-id',
                'attributes' => array(
                    'title' => 'Article title'
                ),
                'relationships' => array(
                    'category' => array(
                        'data' => array(
                            'type' => 'categories',
                            'id' => 'category-id'
                        )
                    )
                )
            )
        );

        $this->assertEquals($expected, $document);
    }
}
