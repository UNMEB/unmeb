<?php

namespace App\Orchid\Screens;

use App\Models\NotificationEmail;
use App\Models\Settings;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AppSettingListScreen extends Screen
{
    public $activeEmails = [];

    public function __construct()
    {
        $notifEmails = NotificationEmail::query();

        $this->activeEmails = $notifEmails->where('is_active', true)->get();
        // dd($this->activeEmails);

        $settings = Settings::get();
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // dd(config('config.settings'));

        return [
            'email.smtp_host' => config('settings.email.smtp_host'),
            'email.smtp_port' => config('settings.email.smtp_port'),
            'email.smtp_username' => config('settings.email.smtp_username'),
            'email.smtp_password' => config('settings.email.smtp_password'),
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
                        ->value($this->activeEmails)
                    ,

                ])
            ])
                ->title('Notification Emails')
                ->description('System users to be notified on system transactions, events and alert notifications')
                ->commands(Button::make(__('Save'))
                    ->class('btn btn-success')
                    ->icon('bs.check-circle')
                    ->method('save', [
                        'active' => $this->activeEmails
                    ])),

            Layout::block([
                Layout::rows([
                    Input::make('email.smtp_host')->title('SMTP Host')->required(),
                    Input::make('email.smtp_port')->title('SMTP Port')->type('number')->required(),
                    Input::make('email.smtp_username')->title('SMTP Username')->required(),
                    Input::make('email.smtp_password')->title('SMTP Password')->required()->type('password')
                ])
            ])
                ->title('Email Configuration (SMTP Settings)')
                ->description('Setup email delivery settings for UNMEB')
                ->commands(
                    Button::make('Save')
                        ->method('save')
                        ->icon('bs.check-circle')
                        ->class('btn btn-success')
                ),

            Layout::block([
                Layout::rows([
                    Input::make('fees.nsin_registration')->title('NSIN Registration Fee')
                        ->type('number')
                        ->required(),
                    Input::make('fees.paper_registration')->title('Exam Registration Cost Per Paper')->type('number')->required(),
                    Input::make('finance.minimum_balance')->title('Minimum Institution Account Balance')->type('number')->required(),

                ])
            ])
                ->title('Administration Settings')
                ->description('Definition for fixed constants for various variables')
                ->commands(
                    Button::make('Save')
                        ->method('save')
                        ->icon('bs.check-circle')
                        ->class('btn btn-success')
                ),


        ];
    }

    public function save(Request $request)
    {
        $activeEmails = $request->input('active_emails');

        if ($request->has('notification_emails')) {
            $emails = $request->get('notification_emails');
            foreach ($emails as $email) {
                // Check if the email exists or was added. If it does not exist, add it and mark as active
                $existing = NotificationEmail::where('email', $email)->first();

                if ($existing != null) {
                    // If email exists, update the model
                    if (in_array($email, $activeEmails)) {
                        $existing->is_active = false;
                        $existing->save();
                    } else {
                        // If email exists but is not active, mark it as active
                        $existing->is_active = true;
                        $existing->save();
                    }
                } else {
                    // If email doesn't exist, create it and mark as active
                    $newEmail = new NotificationEmail();
                    $newEmail->email = $email;
                    $newEmail->is_active = true;
                    $newEmail->save();
                }
            }
        }
    }


}
