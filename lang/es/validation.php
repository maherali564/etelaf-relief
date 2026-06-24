<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El :attribute debe ser un correo electrónico válido.',
    'min' => [
        'numeric' => 'El :attribute debe ser al menos :min.',
        'string' => 'El :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'numeric' => 'El :attribute no debe exceder :max.',
        'string' => 'El :attribute no debe exceder :max caracteres.',
    ],
    'unique' => 'El :attribute ya está en uso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'numeric' => 'El :attribute debe ser un número.',
    'string' => 'El :attribute debe ser una cadena.',
    'image' => 'El :attribute debe ser una imagen.',
    'mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'size' => [
        'image' => 'El :attribute no debe exceder :size KB.',
    ],
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'phone' => 'teléfono',
        'amount' => 'monto',
        'message' => 'mensaje',
        'subject' => 'asunto',
        'password' => 'contraseña',
        'title' => 'título',
        'content' => 'contenido',
        'slug' => 'slug',
        'image' => 'imagen',
        'donor_name' => 'nombre del donante',
        'payment_method' => 'método de pago',
    ],
];
