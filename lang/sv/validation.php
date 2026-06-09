<?php

return [
    'required' => ':attribute måste fyllas i.',
    'email' => ':attribute måste vara en giltig e-postadress.',
    'min' => [
        'numeric' => ':attribute måste vara minst :min.',
        'string' => ':attribute måste vara minst :min tecken.',
    ],
    'max' => [
        'numeric' => ':attribute får inte överstiga :max.',
        'string' => ':attribute får inte överstiga :max tecken.',
    ],
    'unique' => ':attribute används redan.',
    'confirmed' => ':attribute bekräftelsen matchar inte.',
    'numeric' => ':attribute måste vara ett nummer.',
    'string' => ':attribute måste vara en text.',
    'image' => ':attribute måste vara en bild.',
    'mimes' => ':attribute måste vara en fil av typen: :values.',
    'size' => [
        'image' => ':attribute får inte överstiga :size KB.',
    ],
    'attributes' => [
        'name' => 'namn',
        'email' => 'e-post',
        'phone' => 'telefonnummer',
        'amount' => 'belopp',
        'message' => 'meddelande',
        'subject' => 'ämne',
        'password' => 'lösenord',
        'title' => 'titel',
        'content' => 'innehåll',
        'slug' => 'slugg',
        'image' => 'bild',
        'donor_name' => 'donatorsnamn',
        'payment_method' => 'betalningsmetod',
    ],
];
