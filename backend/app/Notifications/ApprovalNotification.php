<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use App\Support\RecordTypes;

// approval steps (ApprovalService): a request waits for you / yours was decided
class ApprovalNotification extends AppNotification
{
    public static function awaiting(ApprovalRequest $request): self
    {
        $record = RecordTypes::describe($request->approvable);

        return new self(
            'Approval needed',
            "{$record['label']} \"{$record['title']}\" is waiting for your decision (level {$request->current_level} of {$request->total_levels}).",
            "/approvals/{$request->id}",
            'warning',
        );
    }

    public static function decided(ApprovalRequest $request): self
    {
        $record = RecordTypes::describe($request->approvable);
        $approved = $request->status === ApprovalRequest::APPROVED;

        return new self(
            $approved ? 'Request approved' : 'Request rejected',
            "Your request for {$record['label']} \"{$record['title']}\" was {$request->status}.",
            "/approvals/{$request->id}",
            $approved ? 'success' : 'error',
        );
    }
}
