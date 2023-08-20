<?php

namespace App\Http\Controllers;

use App\Models\NsinRegistration;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    public function nsinPayments(Request $request)
    {
        $breadcrumbItems = [
            [
                'name' => 'NSIN Payments',
                'url' => route('nsin-payments'),
                'active' => true
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $query = NsinRegistration::with(['course', 'institution', 'year'])->where('old', 1);

        $payments = QueryBuilder::for($query)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        // return response()->json($payments);

        return view('payments.nsin_payments', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbItems,
            'pageTitle' => 'NSIN Payments'
        ]);
    }
}
