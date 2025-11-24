<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'email_verified_at')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('usu_email');
            });
        }

        if (! Schema::hasColumn('usuarios', 'remember_token')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('remember_token', 100)->nullable()->after('email_verified_at');
            });
        }

        if (! Schema::hasColumn('usuarios', 'two_factor_secret')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->text('two_factor_secret')->nullable()->after('remember_token');
            });
        }

        if (! Schema::hasColumn('usuarios', 'two_factor_recovery_codes')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            });
        }

        if (! Schema::hasColumn('usuarios', 'two_factor_confirmed_at')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            });
        }

        if (! Schema::hasColumn('usuarios', 'created_at')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->after('two_factor_confirmed_at');
            });
        }

        if (! Schema::hasColumn('usuarios', 'updated_at')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'updated_at',
            'created_at',
            'two_factor_confirmed_at',
            'two_factor_recovery_codes',
            'two_factor_secret',
            'remember_token',
            'email_verified_at',
        ] as $column) {
            if (Schema::hasColumn('usuarios', $column)) {
                Schema::table('usuarios', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
