<?php namespace App\Services\PushNotification;

class PushNotificationHandler
{
    /**
     * @throws Exceptions\PushNotificationServerError
     */
    public function send($notification_data, $topic)
    {
        $topicResponse = null;
        if (config('notification.send_push_notifications')) {
            /** @var PushNotificationClient $client */
            $client = app(PushNotificationClient::class);
            $data = [
                "topic" => $topic,
                "title" => $notification_data["title"],
                "body" => $notification_data["message"],
                "data" => $notification_data,
                "account_id" => config('notification.sheba_push_notifications_account_id')
            ];
            $url = 'api/vendors/' . config('notification.sheba_services_vendor_id') . '/notification/send';
            $topicResponse = $client->post($url, $data);
        }
        return $topicResponse;
    }
}
