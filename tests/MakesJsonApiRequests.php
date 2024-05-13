<?php

namespace Tests;

use App\JsonApi\Document;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\ExpectationFailedException;

trait MakesJsonApiRequests
{
    protected bool $formatJsonApiDocument = true;
    protected bool $addJsonApiHeaders = true;

    public function withoutJsonApiDocumentFormatting(): self
    {
        $this->formatJsonApiDocument = false;

        return $this;
    }

    public function withoutJsonApiHeaders(): self
    {
        $this->addJsonApiHeaders = false;

        return $this;
    }

    public function withoutJsonApiHeadersAndDocumentFormatting(): self
    {
        $this->addJsonApiHeaders = false;
        $this->formatJsonApiDocument = false;

        return $this;
    }

    /**
     * Call the given URI with a JSON request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = []): TestResponse
    {
        if ($this->addJsonApiHeaders) {
            $headers['accept'] = 'application/vnd.api+json';

            if ($method === 'POST' || $method === 'PATCH') {
                $headers['content-type'] = 'application/vnd.api+json';
            }
        }

        if ($this->formatJsonApiDocument) {
            if (! isset($data['data']) ) {
                $formattedData = $this->getFormattedData($uri, $data);
            }
        }

        return parent::json($method, $uri, $formattedData ?? $data, $headers);
    }

    public function getFormattedData($uri, $data)
    {
        $path = parse_url($uri)['path'];
        $type = (string) Str::of($path)->after('api/v1/')->before('/');
        $id = (string) Str::of($path)->after($type)->replace('/', '');

        return Document::type($type)->id($id)->attributes($data)
                ->relationshipData($data['_relationships'] ?? [])
                ->toArray();
    }
}
