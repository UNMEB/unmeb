<?php

namespace App\Orchid\Layouts\User;

use App\Models\Institution;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserInstitutionLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Select::make('user.institution_id')
                ->fromModel(Institution::class, 'name')
                ->title('Institution')
                ->help('Select Institution user is attached to')
        ];
    }
}
