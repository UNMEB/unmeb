<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ExamController extends Controller
{
    public function payments(Request $request)
    {

        $breadcrumbsItems = [
            [
                'name' => 'Exam Payments',
                'url' => route('exam.payments'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $query = Registration::with(['course', 'institution', 'registrationPeriod']);

        $payments = QueryBuilder::for($query)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        // return response()->json($payments);

        return view('exam.payments', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Exam Registration Payments'
        ]);
    }

    public function registrations(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Districts',
                'url' => route('administration.payments'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $payments = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('exam.registrations', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }

    public function approval(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Districts',
                'url' => route('administration.payments'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $payments = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('exam.approval', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }

    public function approved(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Districts',
                'url' => route('administration.payments'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $payments = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('exam.approved', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }

    public function rejected(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Districts',
                'url' => route('administration.payments'),
                'active' => true,
            ]
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $payments = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('exam.rejected', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }

    public function rejectionReasons(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Exams',
                'url' => 'exams',
                'active' => false,
            ],
            [
                'name' => 'Rejection Reasons',
                'url' => route('exam.rejection-reasons'),
                'active' => true,
            ],
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $payments = QueryBuilder::for(District::class)
            ->latest()
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);

        return view('exam.rejection-reasons', [
            'payments' => $payments,
            'breadcrumbItems' => $breadcrumbsItems,
            'pageTitle' => 'Districts'
        ]);
    }
}
