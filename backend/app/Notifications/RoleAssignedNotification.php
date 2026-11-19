<?php

namespace App\Notifications;

// Example notification: the user was given a role (UserService / RoleService).
class RoleAssignedNotification extends AppNotification
{
    public function __construct(string $roleName)
    {
        parent::__construct(
            title: 'New role assigned',
            body: "You were given the {$roleName} role. Your menu may show new pages.",
            link: '/profile',
            type: 'success',
        );
    }
}
