<?php

namespace App\Console\Commands;

use App\Models\AppVersion;
use Illuminate\Console\Command;

class UpdateAppVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:update-version
                            {app_type : customer_app or delivery_app}
                            {platform : android or ios}
                            {--code= : Version code (integer)}
                            {--name= : Version name (e.g. 1.0.0)}
                            {--required : Update is required}
                            {--notes= : Release notes}
                            {--link= : App store link}';

    /**
     * The console command description.
     */
    protected $description = 'Update app version for customer_app or delivery_app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appType = $this->argument('app_type');
        $platform = $this->argument('platform');

        // Validate app_type
        if (!in_array($appType, ['customer_app', 'delivery_app'])) {
            $this->error('app_type must be customer_app or delivery_app');
            return 1;
        }

        // Validate platform
        if (!in_array($platform, ['android', 'ios'])) {
            $this->error('platform must be android or ios');
            return 1;
        }

        $version = AppVersion::where('app_type', $appType)
            ->where('platform', $platform)
            ->first();

        if (!$version) {
            $this->error("Version not found for {$appType} - {$platform}");
            return 1;
        }

        $updateData = [];

        if ($this->option('code')) {
            $updateData['version_code'] = (int) $this->option('code');
        }

        if ($this->option('name')) {
            $updateData['version_name'] = $this->option('name');
        }

        if ($this->option('required') !== null) {
            $updateData['update_required'] = true;
        }

        if ($this->option('notes')) {
            $updateData['release_notes'] = $this->option('notes');
        }

        if ($this->option('link')) {
            $updateData['app_link'] = $this->option('link');
        }

        if (empty($updateData)) {
            $this->warn('No updates specified. Use --code, --name, --required, --notes, or --link');
            return 1;
        }

        $version->update($updateData);

        $this->info("✅ Version updated successfully!");
        $this->table(
            ['App Type', 'Platform', 'Code', 'Name', 'Required', 'Notes', 'App Link'],
            [[
                $version->app_type,
                $version->platform,
                $version->version_code,
                $version->version_name,
                $version->update_required ? 'Yes' : 'No',
                $version->release_notes,
                $version->app_link,
            ]]
        );

        return 0;
    }
}
