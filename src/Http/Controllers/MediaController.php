<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        $media_class = config('medialibrary.media_model');

        $search_text = $request->input('search_text');

        $query = $media_class::query();

        if ($search_text) {
            $query->where(function ($query) use($search_text) {
                $query->where('name', 'LIKE', '%'.$search_text.'%');
                $query->orWhere('file_name', 'LIKE', '%'.$search_text.'%');
            });
        }

        $query->latest();

        $results = $query->simplePaginate(16);

        return MediaResource::collection($results);
    }
}
