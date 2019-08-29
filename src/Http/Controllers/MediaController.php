<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        $media_class = config('medialibrary.media_model');
        $media_class_is_searchable = method_exists($media_class, 'search');

        $search_text = $request->input('search_text') ?: null;
        $per_page = $request->input('per_page') ?: 18;
        $page = $request->input('page') ?: 1;

        $query = null;

        if ($search_text && $media_class_is_searchable) {
            $query = $media_class::search($search_text);
        } else if ($search_text && !$media_class_is_searchable) {
            $query = $media_class::query();
            $query->where(function ($query) use ($search_text) {
                $query->where('name', 'LIKE', '%' . $search_text . '%');
                $query->orWhere('file_name', 'LIKE', '%' . $search_text . '%');
            });
            $query->latest();
        } else {
            $query = $media_class::query();
            $query->latest();
        }

        $results = $query->paginate($per_page);

        return MediaResource::collection($results);
    }
}
