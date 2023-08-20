<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Surcharge;
use App\Models\SurchargeFee;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Fees',
                'url' => route('administration.fees'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $query = SurchargeFee::with(['surcharge', 'course']);

        $fees = QueryBuilder::for($query)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        // return response()->json($fees);

        return view('fees.index', [
            'fees' => $fees,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Fees'
        ]);
    }
}
