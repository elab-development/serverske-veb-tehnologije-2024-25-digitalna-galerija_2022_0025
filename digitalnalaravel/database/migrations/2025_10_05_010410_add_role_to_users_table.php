<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ovde ne dodajemo novu kolonu, samo osiguravamo vrednosti
        DB::table('users')->update([
            'role' => 'user', // svi postojeći korisnici dobijaju default 'user'
        ]);

        // Ako želiš, možeš ručno postaviti admin-a:
        DB::table('users')->where('email', 'admin@example.com')->update([
            'role' => 'admin',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opcionalno, rollback može ostaviti role nepromenjenu
    }
};
