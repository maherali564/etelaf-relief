<?php

return [
    'required' => 'Bidang :attribute wajib diisi.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'min' => [
        'numeric' => ':attribute harus minimal :min.',
        'string' => ':attribute harus minimal :min karakter.',
    ],
    'max' => [
        'numeric' => ':attribute tidak boleh melebihi :max.',
        'string' => ':attribute tidak boleh melebihi :max karakter.',
    ],
    'unique' => ':attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'numeric' => ':attribute harus berupa angka.',
    'string' => ':attribute harus berupa teks.',
    'image' => ':attribute harus berupa gambar.',
    'mimes' => ':attribute harus berupa file tipe: :values.',
    'size' => [
        'image' => ':attribute tidak boleh melebihi :size KB.',
    ],
    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'phone' => 'nomor telepon',
        'amount' => 'jumlah',
        'message' => 'pesan',
        'subject' => 'subjek',
        'password' => 'kata sandi',
        'title' => 'judul',
        'content' => 'konten',
        'slug' => 'slug',
        'image' => 'gambar',
        'donor_name' => 'nama donatur',
        'payment_method' => 'metode pembayaran',
    ],
];
