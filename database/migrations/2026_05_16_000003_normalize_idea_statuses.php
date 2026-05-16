<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ideas MODIFY status ENUM('available','in progress','done','open','in_progress','completed','cancelled') NOT NULL DEFAULT 'open'");

        DB::table('ideas')->where('status', 'available')->update(['status' => 'open']);
        DB::table('ideas')->where('status', 'in progress')->update(['status' => 'in_progress']);
        DB::table('ideas')->where('status', 'done')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE ideas MODIFY status ENUM('open','in_progress','completed','cancelled') NOT NULL DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ideas MODIFY status ENUM('available','in progress','done','open','in_progress','completed','cancelled') NOT NULL DEFAULT 'available'");

        DB::table('ideas')->where('status', 'open')->update(['status' => 'available']);
        DB::table('ideas')->where('status', 'in_progress')->update(['status' => 'in progress']);
        DB::table('ideas')->where('status', 'completed')->update(['status' => 'done']);
        DB::table('ideas')->where('status', 'cancelled')->update(['status' => 'available']);

        DB::statement("ALTER TABLE ideas MODIFY status ENUM('available','in progress','done') NOT NULL DEFAULT 'available'");
    }
};
