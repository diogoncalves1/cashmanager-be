<?php
namespace Modules\Notification\Data\Notifications;

final class CreateNotificationDto
{
    public function __construct(public readonly array $data, public readonly string $type_code)
    {
    }
}
