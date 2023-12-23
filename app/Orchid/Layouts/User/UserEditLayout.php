<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\Institution;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class UserEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [

            Relation::make('user.institution_id')
                ->title('Insitution')
                ->placeholder('Select User Institution')
                ->help('Select institution user is assigned to')
                ->fromModel(Institution::class, 'institution_name', 'id')
                ->required(),

            Cropper::make('user.picture')
                ->title('User Picture')
                ->width(270)
                ->height(270)
                ->required(),

            Input::make('user.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('user.email')
                ->type('email')
                ->required()
                ->title(__('Email'))
                ->placeholder(__('Email')),
        ];
    }
}
