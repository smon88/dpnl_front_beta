<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Agregar campo status (active, inactive, maintenance)
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE'])
                ->default('ACTIVE')
                ->after('is_active');
        });

        // Migrar datos de is_active a status
        DB::table('projects')->where('is_active', true)->update(['status' => 'ACTIVE']);
        DB::table('projects')->where('is_active', false)->update(['status' => 'INACTIVE']);

        // Eliminar columna is_active
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('backend_uid');
        });

        // Migrar datos de status a is_active
        DB::table('projects')->where('status', 'ACTIVE')->update(['is_active' => true]);
        DB::table('projects')->whereIn('status', ['INACTIVE', 'MAINTENANCE'])->update(['is_active' => false]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
