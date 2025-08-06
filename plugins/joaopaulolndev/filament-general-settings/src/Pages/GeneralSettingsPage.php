<?php

namespace Joaopaulolndev\FilamentGeneralSettings\Pages;

use Exception;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Joaopaulolndev\FilamentGeneralSettings\Forms\AnalyticsFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\ApplicationFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\CustomForms;
use Joaopaulolndev\FilamentGeneralSettings\Forms\EmailFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\SeoFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\SocialNetworkFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Helpers\EmailDataHelper;
use Joaopaulolndev\FilamentGeneralSettings\Mail\TestMail;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Joaopaulolndev\FilamentGeneralSettings\Services\MailSettingsService;
use RuntimeException;

class GeneralSettingsPage extends Page
{
    protected static string $view = 'filament-general-settings::filament.pages.general-settings-page';

    /**
     * @throws \Exception
     */
    public static function getNavigationGroup(): ?string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getNavigationGroup();
    }

    /**
     * @throws \Exception
     */
    public static function getNavigationIcon(): ?string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getIcon();
    }

    public static function getNavigationSort(): ?int
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getSort();
    }

    public static function canAccess(): bool
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getCanAccess();
    }

    public function getTitle(): string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getTitle() ?? __('filament-general-settings::default.title');
    }

    public static function getNavigationLabel(): string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getNavigationLabel() ?? __('filament-general-settings::default.title');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = GeneralSetting::first()?->toArray() ?: [];

        $this->data['seo_description'] = $this->data['seo_description'] ?? '';
        $this->data['seo_preview'] = $this->data['seo_preview'] ?? '';
        $this->data['theme_color'] = $this->data['theme_color'] ?? '';
        $this->data['seo_metadata'] = $this->data['seo_metadata'] ?? [];
        $this->data = EmailDataHelper::getEmailConfigFromDatabase($this->data);

        if (isset($this->data['site_logo']) && is_string($this->data['site_logo'])) {
            $this->data['site_logo'] = [
                'name' => $this->data['site_logo'],
            ];
        }

        if (isset($this->data['site_favicon']) && is_string($this->data['site_favicon'])) {
            $this->data['site_favicon'] = [
                'name' => $this->data['site_favicon'],
            ];
        }
    }

    public function form(Form $form): Form
    {
        $arrTabs = [];

        if (config('filament-general-settings.show_application_tab')) {
            $arrTabs[] = Tabs\Tab::make('Application Tab')
                ->label(__('filament-general-settings::default.application'))
                ->icon('heroicon-o-tv')
                ->schema(ApplicationFieldsForm::get())
                ->columns(3);
        }

        if (config('filament-general-settings.show_analytics_tab')) {
            $arrTabs[] = Tabs\Tab::make('Analytics Tab')
                ->label(__('filament-general-settings::default.analytics'))
                ->icon('heroicon-o-globe-alt')
                ->schema(AnalyticsFieldsForm::get());
        }

        if (config('filament-general-settings.show_seo_tab')) {
            $arrTabs[] = Tabs\Tab::make('Seo Tab')
                ->label(__('filament-general-settings::default.seo'))
                ->icon('heroicon-o-window')
                ->schema(SeoFieldsForm::get($this->data))
                ->columns(1);
        }

        if (config('filament-general-settings.show_email_tab')) {
            $arrTabs[] = Tabs\Tab::make('Email Tab')
                ->label(__('filament-general-settings::default.email'))
                ->icon('heroicon-o-envelope')
                ->schema(EmailFieldsForm::get())
                ->columns(3);
        }

        if (config('filament-general-settings.show_social_networks_tab')) {
            $arrTabs[] = Tabs\Tab::make('Social Network Tab')
                ->label(__('filament-general-settings::default.social_networks'))
                ->icon('heroicon-o-heart')
                ->schema(SocialNetworkFieldsForm::get())
                ->columns(2)
                ->statePath('social_network');
        }

        if (config('filament-general-settings.show_custom_tabs')) {
            foreach (config('filament-general-settings.custom_tabs') as $key => $customTab) {
                $arrTabs[] = Tabs\Tab::make($customTab['label'])
                    ->label(__($customTab['label']))
                    ->icon($customTab['icon'])
                    ->schema(CustomForms::get($customTab['fields']))
                    ->columns($customTab['columns'])
                    ->statePath('more_configs');
            }
        }

        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs($arrTabs),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('Save')
                ->label(__('filament-general-settings::default.save'))
                ->color('primary')
                ->submit('Update'),
        ];
    }

    /**
     * @throws FileNotFoundException
     */
    public function update(): void
    {
        $data = $this->form->getState();
        if (config('filament-general-settings.show_email_tab')) {
            $this->updateSmtpEnvConfig($data);
            $data = EmailDataHelper::setEmailConfigToDatabase($data);
        }
        $data = $this->clearVariables($data);

        GeneralSetting::updateOrCreate([], $data);
        Cache::forget('general_settings');

        $this->successNotification(__('filament-general-settings::default.settings_saved'));
        redirect(request()?->header('Referer'));
    }

    private function clearVariables(array $data): array
    {
        unset(
            $data['seo_preview'],
            $data['seo_description'],
            $data['default_email_provider'],
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_encryption'],
            $data['smtp_timeout'],
            $data['smtp_username'],
            $data['smtp_password'],
            $data['mailgun_domain'],
            $data['mailgun_secret'],
            $data['mailgun_endpoint'],
            $data['postmark_token'],
            $data['amazon_ses_key'],
            $data['amazon_ses_secret'],
            $data['amazon_ses_region'],
            $data['mail_to'],
        );

        return $data;
    }

    public function sendTestMail(MailSettingsService $mailSettingsService): void
    {
        $data = $this->form->getState();
        $email = $data['mail_to'];

        $settings = $mailSettingsService->loadToConfig($data);

        try {
            Mail::mailer($settings['default_email_provider'])
                ->to($email)
                ->send(new TestMail([
                    'subject' => 'This is a test email to verify SMTP settings',
                    'body' => 'This is for testing email using smtp.',
                ]));
        } catch (\Exception $e) {
            $this->errorNotification(__('filament-general-settings::default.test_email_error'), $e->getMessage());

            return;
        }

        $this->successNotification(__('filament-general-settings::default.test_email_success') . $email);
    }

    private function successNotification(string $title): void
    {
        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    private function errorNotification(string $title, string $body): void
    {
        Log::error('[EMAIL] ' . $body);

        Notification::make()
            ->title($title)
            ->danger()
            ->body($body)
            ->send();
    }

    /**
     * @throws FileNotFoundException
     */
    private function updateSmtpEnvConfig(array $config): void
    {
        // Validate input configuration
        if (empty($config)) {
            throw new \InvalidArgumentException('SMTP configuration cannot be empty');
        }

        // Sanitize and validate input data
        $sanitizedConfig = $this->sanitizeSmtpConfig($config);

        try {
            // Get current .env content
            $envPath = base_path('.env');
            if (!File::exists($envPath)) {
                throw new \RuntimeException('Environment file not found');
            }

            $envContent = File::get($envPath);
            $lineBreak = PHP_EOL;

            // Prepare replacement values with additional sanitization
            $replacements = [
                'MAIL_MAILER' => 'smtp',
                'MAIL_HOST' => $sanitizedConfig['smtp_host'] ?? '',
                'MAIL_PORT' => $sanitizedConfig['smtp_port'] ?? '',
                'MAIL_USERNAME' => $sanitizedConfig['smtp_username'] ?? '',
                'MAIL_PASSWORD' => $sanitizedConfig['smtp_password'] ?? '',
                'MAIL_ENCRYPTION' => $sanitizedConfig['smtp_encryption'] ?? '',
                'MAIL_FROM_ADDRESS' => $sanitizedConfig['smtp_username'] ?? '',
                'MAIL_FROM_NAME' => $sanitizedConfig['email_from_name'] ?? 'System'
            ];

            // Perform replacements
            foreach ($replacements as $key => $value) {
                $formatted = $this->formatEnvValue($value);
                $pattern = "/^{$key}=.*$/m";

                // Clean replacement string with proper line ending
                $replacementLine = "{$key}={$formatted}";

                if (preg_match($pattern, $envContent)) {
                    // Match and replace exact line
                    $envContent = preg_replace($pattern, $replacementLine, $envContent);
                } else {
                    // Append with a newline
                    $envContent .= PHP_EOL . $replacementLine;
                }
            }

            // Ensure file ends with a newline
            $envContent = rtrim($envContent) . $lineBreak;

            // Write updated content back to .env file
            File::put($envPath, $envContent);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('SMTP ENV Update Failed: ' . $e->getMessage());

            // Optionally, you might want to rethrow the exception
            throw $e;
        }
    }

    /**
     * Format environment value with proper quoting
     *
     * @param string $value Raw value to be formatted
     * @return string Formatted value with quotes if needed
     */
    private function formatEnvValue(string $value): string
    {
        $trim = trim($value);

        if ($trim === '') {
            return '""';
        }

        // Match: smt.com.ar, user@domain.com, 127.0.0.1, my-app_123
        if (preg_match('/^[\w.@\-]+$/', $trim)) {
            return $trim;
        }

        // Otherwise, wrap in quotes and escape existing quotes
        $escaped = str_replace('"', '\\"', $trim);
        return "\"{$escaped}\"";
    }

    /**
     * Sanitize SMTP configuration input
     *
     * @param array $config Raw configuration input
     * @return array Sanitized configuration
     */
    private function    sanitizeSmtpConfig(array $config): array
    {
        return [
            'smtp_host' => filter_var($config['smtp_host'] ?? '', FILTER_SANITIZE_URL),
            'smtp_port' => filter_var($config['smtp_port'] ?? '', FILTER_SANITIZE_NUMBER_INT),
            'smtp_username' => filter_var($config['smtp_username'] ?? '', FILTER_SANITIZE_EMAIL),
            'smtp_password' => $config['smtp_password'] ?? '',
            'smtp_encryption' => strtolower(preg_replace('/[^a-zA-Z]/', '', $config['smtp_encryption'] ?? '')),
            'email_from_name' => substr(strip_tags($config['email_from_name'] ?? ''), 0, 255)
        ];
    }
}
