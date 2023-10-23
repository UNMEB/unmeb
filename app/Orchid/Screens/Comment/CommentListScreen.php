<?php

namespace App\Orchid\Screens\Comment;

use App\Models\Comment;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;
use Orchid\Screen\Components\Cells\DateTimeSplit;

class CommentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $comments = Comment::paginate();
        return [
            'comments' => $comments
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Comments';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('comments', [
                TD::make('id', 'ID')
                    ->width(100),

                TD::make('comment', 'Comment')
                    ->width(500)
                    ->render(function ($row) {
                        $x = Str::limit($row->comment, 200, '...');
                        return '<p>' . $x . '</p>';
                    }),

                TD::make('user', 'User')
                    ->defaultHidden()
                    ->render(function (Comment $comment) {
                        return optional($comment->user)->name;
                    }),

                TD::make('institution', 'Institution')
                    ->defaultHidden()
                    ->render(function (Comment $comment) {
                        $institution = optional($comment->user)->institution;
                        return optional($institution)->institution_name;
                    }),

                TD::make('email', 'Email Address'),

                TD::make('created_at', 'Created')
                    ->width('120')

                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT),

                TD::make('updated_at', 'Last Updated')
                    ->width('120')
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT),
            ])
        ];
    }
}
