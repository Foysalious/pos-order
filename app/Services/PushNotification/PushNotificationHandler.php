<?php namespace App\Services\PushNotification;

class PushNotificationHandler
{
    public function send($topic, $notification = null, $data = null, $priority = null)
    {
        /** @var PushNotificationClient $client */
        $client = app(PushNotificationClient::class);
        $data = [
            "topic" => $topic,
            "data" => json_encode($data),
            'channel' => 'firebase'
        ];
        if ($notification && $notification['title']) $data['title'] = $notification['title'];
        if ($notification && $notification['body']) $data['body'] = $notification['body'];
        if ($priority) $data['priority'] = $priority;
        $url = 'api/vendors/' . config('notification.sheba_services_vendor_id') . '/notification/send';
        $client->post($url, $data);
    }
}
