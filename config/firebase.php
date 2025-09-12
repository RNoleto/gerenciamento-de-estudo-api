<?php
return [
    'credentials' => env('FIREBASE_CREDENTIALS_JSON')
        ? json_decode(env('FIREBASE_CREDENTIALS_JSON'), true)
        : storage_path('app/firebase/firebase_credentials.json'),
];