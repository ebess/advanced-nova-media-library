<?php

namespace Ebess\AdvancedNovaMediaLibrary\Http\Controllers;

use Ebess\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Ebess\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;
use Exception;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        if (!config('nova-media-library.enable-existing-media')) {
            throw new Exception('You need to enable the `existing media` feature via config.');
        }

        $mediaClass = config('media-library.media_model');
        $mediaClassIsSearchable = method_exists($mediaClass, 'search');

        $searchText = $request->input('search_text') ?: null;
        $perPage = $request->input('per_page') ?: 18;
        $collection = $request->input('collection') ?: null;

        $query = null;

        if ($searchText && $mediaClassIsSearchable) {
            $query = $mediaClass::search($searchText);
        } else {
            $query = $mediaClass::query();

            if ($searchText) {
                $query->where(function ($query) use ($searchText) {
                    $query->where('name', 'LIKE', '%' . $searchText . '%');
                    $query->orWhere('file_name', 'LIKE', '%' . $searchText . '%');
                });
            }

            $query->latest();
        }

        if($collection){
            $query->where('collection_name', $collection);
        }

        $results = $query->paginate($perPage);

        return MediaResource::collection($results);
    }
}
