<?php

// File attachments (AttachmentController). Change via .env, not code.
return [
    // a disk from config/filesystems.php: `local` (storage/app/private) or `s3`
    'disk' => env('ATTACHMENTS_DISK', 'local'),

    'max_kb' => (int) env('ATTACHMENTS_MAX_KB', 10240),   // 10 MB

    'mimes' => array_filter(array_map('trim', explode(',', env(
        'ATTACHMENTS_MIMES',
        'pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip',
    )))),
];
