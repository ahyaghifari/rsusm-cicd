<?php

namespace App\Jobs;

use App\Models\SesiKonsultasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        private readonly int    $sesiId,
        private readonly string $title,
        private readonly string $body,
        private readonly string $url,
        private readonly string $token,
    ) {}

    public function handle(): void
    {
        $sesi = SesiKonsultasi::find($this->sesiId);

        if (! $sesi?->push_subscription) {
            return;
        }

        $auth = [
            'VAPID' => [
                'subject'    => config('webpush.vapid.subject'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush     = new WebPush($auth);
        $subscription = Subscription::create(json_decode($sesi->push_subscription, true));

        $webPush->queueNotification(
            $subscription,
            json_encode([
                'title' => $this->title,
                'body'  => $this->body,
                'url'   => $this->url,
                'token' => $this->token,
            ])
        );

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $sesi->update(['push_subscription' => null]);
            }
        }
    }
}
