<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Http\Resources\V1\AuthorResource;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): AnonymousResourceCollection
    {
        $authors = User::jsonPaginate();

        return AuthorResource::collection($authors);
    }

    /**
     * Display the specified resource.
     * @param  \App\Models\User  $author
     * @return JsonResource
     */
    public function show($author): JsonResource
    {
        $author = User::findOrFail($author);

        return AuthorResource::make($author);
    }
}
