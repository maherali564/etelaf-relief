<?php

return [
    'required' => ':attribute alanı zorunludur.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'min' => [
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'max' => [
        'numeric' => ':attribute en fazla :max olmalıdır.',
        'string' => ':attribute en fazla :max karakter olmalıdır.',
    ],
    'unique' => ':attribute zaten kullanılıyor.',
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'string' => ':attribute bir metin olmalıdır.',
    'image' => ':attribute bir resim olmalıdır.',
    'mimes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'size' => [
        'image' => ':attribute boyutu :size KB\'ı geçmemelidir.',
    ],
    'attributes' => [
        'name' => 'ad',
        'email' => 'e-posta',
        'phone' => 'telefon',
        'amount' => 'tutar',
        'message' => 'mesaj',
        'subject' => 'konu',
        'password' => 'şifre',
        'title' => 'başlık',
        'content' => 'içerik',
        'slug' => 'slug',
        'image' => 'resim',
        'donor_name' => 'bağışçı adı',
        'payment_method' => 'ödeme yöntemi',
    ],
];
