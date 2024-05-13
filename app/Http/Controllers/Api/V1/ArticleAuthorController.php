<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AuthorResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleAuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Article $article)
    {
        return AuthorResource::identifier( $article->author);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return AuthorResource::make($article->author);
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
            'data.id' => 'exists:users,id'
        ]);
        $userId = $request->input('data.id');

        $article->update(['user_id' => $userId]);

        return AuthorResource::identifier($article->author);
    }
}
