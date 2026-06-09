<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'numeric' => 'The :attribute must not exceed :max.',
        'string' => 'The :attribute must not exceed :max characters.',
    ],
    'unique' => 'The :attribute has already been taken.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'numeric' => 'The :attribute must be a number.',
    'string' => 'The :attribute must be a string.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'size' => [
        'image' => 'The :attribute must not exceed :size KB.',
    ],
    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone number',
        'amount' => 'amount',
        'message' => 'message',
        'subject' => 'subject',
        'password' => 'password',
        'title' => 'title',
        'content' => 'content',
        'slug' => 'slug',
        'image' => 'image',
        'donor_name' => 'donor name',
        'payment_method' => 'payment method',
    ],
];
