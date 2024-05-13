<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class ArticleCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Article $article)
    {
        return CategoryResource::identifier( $article->category);
    }

    /**
     *  Display the specified resource.
     * @param Article $article
     * @return CategoryResource
     */
    public function show(Article $article)
    {
        return CategoryResource::make($article->category);
    }

    /**
     * Update the specified resource in storage.
     * @param \Illuminate\Http\Request $request
     * @param Article $article
     * @return array
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'data.id' => 'exists:categories,slug'
        ]);
        $categorySlug = $request->input('data.id');

        $category = Category::where('slug', $categorySlug)->first();

        $article->update(['category_id' => $category->id]);

        return CategoryResource::identifier($article->category);
    }
}
