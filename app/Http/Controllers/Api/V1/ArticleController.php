<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\Category;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use App\Http\Requests\V1\SaveArticleRequest;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;

class ArticleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', [
            'only' => [/*'store',*/ 'update', 'destroy']
        ]);
    }

    public function index(): JsonResource
    {
        $articles = Article::query()
            ->allowedIncludes(['category', 'author'])
            ->allowedFilters(['title', 'content', 'month', 'year', 'categories'])
            ->allowedSorts(['title', 'content'])
            ->sparseFieldset()
            ->jsonPaginate();

        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created resource in storage.
     * @param SaveArticleRequest $request
     * @return ArticleResource
     */
    public function store(SaveArticleRequest $request): ArticleResource
    {
        $this->authorize('create', new Article);

        $data = $request->validated()['data'];
        $articleData = $data['attributes'];

        $articleData['user_id'] = $data['relationships']['author']['data']['id'];

        $categorySlug = $data['relationships']['category']['data']['id'];
        $category = Category::whereSlug($categorySlug)->first();

        $articleData['category_id'] = $category->id;

        $article = Article::create($articleData);
        return ArticleResource::make($article);
    }

    /**
     * Display the specified resource.
     *
     * @param Article $article
     * @return ArticleResource
     */
    public function show($article): JsonResource
    {
        $article = Article::where('slug', $article)
            ->allowedIncludes(['category', 'author'])
            ->sparseFieldset()
            ->firstOrFail();

        return ArticleResource::make($article);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SaveArticleRequest $request
     * @param Article $article
     * @return ArticleResource
     */
    public function update(SaveArticleRequest $request, Article $article): ArticleResource
    {
        $this->authorize('update', $article);

        $data = $request->validated()['data'];
        $articleData = $data['attributes'];

        if (isset($articleData['relationships'])) {
            if (isset($articleData['relationships']['author'])) {
                $articleData['user_id'] = $data['relationships']['author']['data']['id'];
            }

            if (isset($articleData['relationships']['category'])) {
                $categorySlug = $data['relationships']['category']['data']['id'];
                $category = Category::whereSlug($categorySlug)->first();

                $articleData['category_id'] = $category->id;
            }
        }

        $article->update($articleData);

        return ArticleResource::make($article);
    }

    /**
     * @param Article $article
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article, Request $request): Response
    {
        $this->authorize('delete', $article);

        $article->delete();
        return response()->noContent();
    }
}
