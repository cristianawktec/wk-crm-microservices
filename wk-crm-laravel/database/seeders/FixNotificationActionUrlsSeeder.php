<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;

class FixNotificationActionUrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Fixes old notifications with incorrect action_url patterns like:
     * - /customer-opportunities/show/{id}
     * - /opportunities/show/{id}
     * 
     * And converts them to correct format:
     * - /opportunities/{id}
     */
    public function run(): void
    {
        // Fix notifications with /show/ pattern
        Notification::where('action_url', 'like', '%/show/%')
            ->get()
            ->each(function ($notification) {
                // Extract ID from the action_url
                if (preg_match('/\/show\/(.+)$/', $notification->action_url, $matches)) {
                    $id = $matches[1];
                    $notification->action_url = "/opportunities/{$id}";
                    $notification->save();
                    echo "Fixed notification {$notification->id}: {$notification->action_url}\n";
                }
            });

        // Fix notifications with customer-opportunities pattern
        Notification::where('action_url', 'like', '%/customer-opportunities/%')
            ->where('action_url', 'not like', '%/show/%')
            ->get()
            ->each(function ($notification) {
                // Extract ID from /customer-opportunities/{id}
                if (preg_match('/\/customer-opportunities\/([a-f0-9\-]+)/', $notification->action_url, $matches)) {
                    $id = $matches[1];
                    $notification->action_url = "/opportunities/{$id}";
                    $notification->save();
                    echo "Fixed notification {$notification->id}: {$notification->action_url}\n";
                }
            });

        echo "\n✅ Notification action URLs fixed!\n";
    }
}
