<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        $media_class = config('medialibrary.media_model');

        $search_text = $request->input('search_text') ?: null;
        $per_page = $request->input('per_page') ?: 18;
        $page = $request->input('page') ?: 1;

        $query = $media_class::query();

        if ($search_text) {
            $query->where(function ($query) use($search_text) {
                $query->where('name', 'LIKE', '%'.$search_text.'%');
                $query->orWhere('file_name', 'LIKE', '%'.$search_text.'%');
            });
        }

        $query->latest();

        $results = $query->paginate($per_page, ['*'], 'page', $page);

        return MediaResource::collection($results);
    }
}
