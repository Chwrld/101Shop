<?php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',  // SMTP server address
        'username' => 'your-email@gmail.com', // Your Gmail address
        'password' => 'your-16-char-app-password', // Gmail App Password
        'port' => 587, // SMTP port (usually 587 for TLS)
        'from_email' => 'your-email@gmail.com', // Sender email address
        'from_name' => 'Repair System' // Sender name
    ],
    'options' => [
        'base_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/RepairSystem',
        'token_expiry' => 3600, // 1 hour in seconds
        'reset_link_expiry' => '+1 hour' // 1 hour from now
    ]
];
