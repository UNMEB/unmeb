<?php

namespace App\Http\Controllers;

use App\Models\Surcharge;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SurchargeController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Surcharge',
                'url' => route('surcharge.index'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $surcharges = QueryBuilder::for(Surcharge::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('surcharge.index', [
            'surcharges' => $surcharges,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Surcharge'
        ]);
    }
}
