<?php

namespace App\Orchid\Screens;

use App\Models\NotificationEmail;
use App\Models\Settings;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use RealRashid\SweetAlert\Facades\Alert;

class AppSettingListScreen extends Screen
{
    public $activeEmails = [];
    public $settings = [];

    public function __construct()
    {
        $notifEmails = NotificationEmail::query();
        $this->activeEmails = $notifEmails->where('is_active', true)->get();
        $this->settings = Settings::all();
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
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
    public function commandBar(): array
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

        $settings = Config::get('settings');

        return [
            Layout::block([
                Layout::rows([
                    Select::make('notification_emails')
                        ->title('Notification Emails')
                        ->multiple()
                        ->allowAdd()
                        ->fromQuery(NotificationEmail::where('is_active', false), 'email', 'email')
                        ->value($this->activeEmails)
                        ->autocomplete(false)
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
                    Group::make(([
                        Picture::make('signature.registra_signature')
                            ->title('Registrar Signature')
                            ->value($settings['signature.registra_signature']),

                        Picture::make('signature.finance_signature')
                            ->title('Finance Signature')
                            ->value($settings['signature.finance_signature']),

                    ])),
                ])
            ])
                ->title('Administrative Signatures')
                ->description('Add current signatures for Accountant and Registrar')
                ->commands(
                    Button::make('Save')
                        ->method('save')
                        ->icon('bs.check-circle')
                        ->class('btn btn-success')
                ),

            Layout::block([
                Layout::rows([
                    Input::make('email.smtp_host')
                        ->title('SMTP Host')
                        ->value($settings['email.smtp_host'])
                        ->required(),

                    Input::make('email.smtp_port')
                        ->title('SMTP Port')
                        ->type('number')
                        ->value($settings['email.smtp_port'])
                        ->required(),

                    Input::make('email.smtp_username')
                        ->title('SMTP Username')
                        ->value($settings['email.smtp_username'])
                        ->required(),

                    Input::make('email.smtp_password')
                        ->title('SMTP Password')
                        ->value($settings['email.smtp_password'])
                        ->required(),
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

                    Group::make([
                        Input::make('fees.nsin_registration')->title('NSIN Registration Fee')
                            ->type('number')
                            ->min(1)
                            ->value($settings['fees.nsin_registration'])
                            ->required(),

                        Input::make('fees.paper_registration')->title('Exam Registration Cost Per Paper')->type('number')->required()->min(1)
                            ->value($settings['fees.paper_registration'] ?? null),
                    ]),

                    Group::make([
                        Input::make('fees.logbook_fee')->title('Logbook Fees')->type('number')->required()->min(1)
                            ->value($settings['fees.logbook_fee'] ?? null),

                        Input::make('fees.research_fee')->title('Research Fees')->type('number')->required()->min(1)
                            ->value($settings['fees.research_fee'] ?? null),
                    ]),

                    Input::make('finance.minimum_balance')->title('Minimum Institution Account Balance')
                        ->type('number')->required()->min(1)
                        ->value($settings['finance.minimum_balance']),

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

        $settings = $request->all();
        foreach ($settings as $category => $data) {
            // Check if $data is an array
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    Settings::updateOrCreate(
                        ['key' => "$category.$key"], // This will give you a unique key like "email.smtp_host"
                        ['value' => $value]
                    );
                }
            }
        }

        $this->settings = $settings;

        Alert::success('Action Complete', 'Settings have been updated');

        return redirect()->back();


    }


}
