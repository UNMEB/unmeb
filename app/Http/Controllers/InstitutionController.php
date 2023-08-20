<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Instituitions',
                'url' => route('administration.institutions'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $institutions = QueryBuilder::for(Institution::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('institutions.index', [
            'institutions' => $institutions,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Institutions'
        ]);
    }
}
