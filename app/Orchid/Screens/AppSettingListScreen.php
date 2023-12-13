<?php

namespace App\Orchid\Screens;

use App\Models\NotificationEmail;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AppSettingListScreen extends Screen
{
    public $emails = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NotificationEmail::query();

        $this->emails = $query->where('is_active', true)->get();

        return [
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'System Settings';
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
            Layout::block([
                Layout::rows([
                    Select::make('notification_emails')
                        ->title('Notification Emails')
                        ->multiple()
                        ->allowAdd()
                        ->fromQuery(NotificationEmail::where('is_active', false), 'email', 'email')
                        ->value($this->emails)
                    ,

                ])
            ])
                ->title('Notification Emails')
                ->description('System users to be notified on system transactions, events and alert notifications')
                ->commands(Button::make(__('Save Settings'))
                    ->class('btn btn-success')
                    ->icon('bs.check-circle')
                    ->method('save'))
            ,

            Layout::block([

            ])
                ->title('System constants')
                ->description('Constants for pricing calculations used for NSIN & Exam Registration'),


        ];
    }

    public function save(Request $request)
    {
        if ($request->has('notification_emails')) {
            $emails = $request->get('notification_emails');
            foreach ($emails as $email) {
                // Check if the email exists or was added. If it does not exist, add it an activae
                $existing = NotificationEmail::where('email', $email)->first();
                dd($existing);
            }
        }
    }
}
