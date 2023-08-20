<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbsItems = [
            [
                'name' => 'Comments',
                'url' => '/comments',
                'active' => true
            ],
        ];

        $q = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort');

        $comments = QueryBuilder::for(Comment::class)
            ->allowedSorts(['created_at'])
            ->where('comment', 'like', "%$q%")
            ->latest()
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('institutions', 'users.institution_id', '=', 'institutions.id')
            ->orderBy('comments.date_submitted')
            ->select('comments.*') // Select specific columns from comments table
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'q' => $q, 'sort' => $sort]);


        return view('comments.index', [
            'pageTitle' => 'Comments',
            'breadcrumbItems' => $breadcrumbsItems,
            'comments' => $comments
        ]);
    }
}
