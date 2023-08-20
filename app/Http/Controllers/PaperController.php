<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaperController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Papers',
                'url' => route('administration.papers'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $papers = QueryBuilder::for(Paper::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('papers.index', [
            'papers' => $papers,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Papers'
        ]);
    }
}