<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Districts',
                'url' => route('administration.districts'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $districts = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('districts.index', [
            'districts' => $districts,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }
}
