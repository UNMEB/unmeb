<?php

namespace App\Http\Controllers;

use App\Models\Year;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class YearController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Years',
                'url' => route('administration.years'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $years = QueryBuilder::for(Year::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('years.index', [
            'years' => $years,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Years'
        ]);
    }
}
