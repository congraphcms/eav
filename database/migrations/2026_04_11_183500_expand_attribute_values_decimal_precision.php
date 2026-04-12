<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attribute_values_decimal')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE attribute_values_decimal ALTER COLUMN value TYPE numeric(20,2)');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE attribute_values_decimal MODIFY value DECIMAL(20,2) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('attribute_values_decimal')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE attribute_values_decimal ALTER COLUMN value TYPE numeric(8,2)');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE attribute_values_decimal MODIFY value DECIMAL(8,2) NULL');
        }
    }
};
