<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function to check if index exists using raw query
        $indexExists = function($table, $indexName) {
            $database = config('database.connections.mysql.database');
            $result = \Illuminate\Support\Facades\DB::select("
                SELECT COUNT(*) as count FROM information_schema.statistics
                WHERE table_schema = ? AND table_name = ? AND index_name = ?
            ", [$database, $table, $indexName]);
            return $result[0]->count > 0;
        };

        // Add indexes to subdomain_requests table
        if (!$indexExists('subdomain_requests', 'subdomain_requests_web_monitor_id_index')) {
            Schema::table('subdomain_requests', function (Blueprint $table) {
                $table->index('web_monitor_id', 'subdomain_requests_web_monitor_id_index');
            });
        }

        if (!$indexExists('subdomain_requests', 'subdomain_requests_status_index')) {
            Schema::table('subdomain_requests', function (Blueprint $table) {
                $table->index('status', 'subdomain_requests_status_index');
            });
        }

        if (!$indexExists('subdomain_requests', 'subdomain_requests_ticket_no_index')) {
            Schema::table('subdomain_requests', function (Blueprint $table) {
                $table->index('ticket_no', 'subdomain_requests_ticket_no_index');
            });
        }

        if (!$indexExists('subdomain_requests', 'subdomain_requests_status_submitted_at_index')) {
            Schema::table('subdomain_requests', function (Blueprint $table) {
                $table->index(['status', 'submitted_at'], 'subdomain_requests_status_submitted_at_index');
            });
        }

        // Add indexes to web_monitors table
        if (!$indexExists('web_monitors', 'web_monitors_subdomain_request_id_index')) {
            Schema::table('web_monitors', function (Blueprint $table) {
                $table->index('subdomain_request_id', 'web_monitors_subdomain_request_id_index');
            });
        }

        if (!$indexExists('web_monitors', 'web_monitors_status_index')) {
            Schema::table('web_monitors', function (Blueprint $table) {
                $table->index('status', 'web_monitors_status_index');
            });
        }

        if (!$indexExists('web_monitors', 'web_monitors_subdomain_index')) {
            Schema::table('web_monitors', function (Blueprint $table) {
                $table->index('subdomain', 'web_monitors_subdomain_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropIndex('subdomain_requests_web_monitor_id_index');
            $table->dropIndex('subdomain_requests_status_index');
            $table->dropIndex('subdomain_requests_ticket_no_index');
            $table->dropIndex('subdomain_requests_status_submitted_at_index');
        });

        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropIndex('web_monitors_subdomain_request_id_index');
            $table->dropIndex('web_monitors_status_index');
            $table->dropIndex('web_monitors_subdomain_index');
        });
    }
};
