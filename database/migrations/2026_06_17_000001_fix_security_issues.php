<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->timestamps();
        });

        DB::table('payment_gateways')->whereNotNull('config')->orderBy('id')->lazy()->each(function ($row) {
            DB::table('payment_gateways')->where('id', $row->id)->update([
                'config' => Crypt::encryptString($row->config),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');

        DB::table('payment_gateways')->whereNotNull('config')->orderBy('id')->lazy()->each(function ($row) {
            try {
                $decrypted = Crypt::decryptString($row->config);
                DB::table('payment_gateways')->where('id', $row->id)->update([
                    'config' => $decrypted,
                ]);
            } catch (\Exception $e) {
            }
        });
    }
};
