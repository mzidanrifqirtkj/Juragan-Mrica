<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        DB::table('users')
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'username' => $this->generateUniqueUsername((string) $user->name, (string) $user->email),
                    ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    private function generateUniqueUsername(string $name, string $email): string
    {
        $base = Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();

        if ($base === '') {
            $base = Str::before($email, '@');
        }

        $base = $base !== '' ? $base : 'user';
        $username = $base;
        $suffix = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $suffix++;
            $username = $base . $suffix;
        }

        return $username;
    }
};
