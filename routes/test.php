<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test-email', function () {
    try {
        Mail::raw('Hi, this is a test email!', function ($message) {
            $message->to('test@example.com')
                ->subject('Test Email from Laravel');
        });

        return 'Email sent successfully!';
    } catch (Exception $e) {
        return 'Error: '.$e->getMessage();
    }
});
