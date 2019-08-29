<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        $mediaClass = config('medialibrary.media_model');
        $mediaClassIsSearchable = method_exists($mediaClass, 'search');

        $searchText = $request->input('search_text') ?: null;
        $perPage = $request->input('per_page') ?: 18;
        $page = $request->input('page') ?: 1;

        $query = null;

        if ($searchText && $mediaClassIsSearchable) {
            $query = $mediaClass::search($searchText);
        } else if ($searchText && !$mediaClassIsSearchable) {
            $query = $mediaClass::query();
            $query->where(function ($query) use ($search_text) {
                $query->where('name', 'LIKE', '%' . $search_text . '%');
                $query->orWhere('file_name', 'LIKE', '%' . $search_text . '%');
            });
            $query->latest();
        } else {
            $query = $mediaClass::query();
            $query->latest();
        }

        $results = $query->paginate($perPage);

        return MediaResource::collection($results);
    }
}
