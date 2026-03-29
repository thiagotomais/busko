<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite used in tests treats columns as TEXT and has no ENUM concept;
        // the ALTER is only needed for MySQL/MariaDB in production.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('driver','guardian','admin','company_admin') NOT NULL DEFAULT 'driver'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('type', 'company_admin')->update(['type' => 'admin']);
            DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('driver','guardian','admin') NOT NULL DEFAULT 'driver'");
        }
    }
};
