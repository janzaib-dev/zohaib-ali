<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            // Return Authorization Workflow
            $table->enum('return_status', ['pending', 'approved', 'rejected', 'completed'])
                ->default('pending')->after('return_note');
            $table->unsignedBigInteger('approved_by')->nullable()->after('return_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Quality Check
            $table->enum('quality_status', ['good', 'damaged', 'defective', 'pending_inspection'])
                ->default('pending_inspection')->after('rejection_reason');
            $table->unsignedBigInteger('inspected_by')->nullable()->after('quality_status');
            $table->text('inspection_notes')->nullable()->after('inspected_by');

            // Return Deadline Tracking
            $table->date('return_deadline')->nullable()->after('inspection_notes');
            $table->boolean('is_within_deadline')->default(true)->after('return_deadline');

            // Foreign keys
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('inspected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['inspected_by']);

            $table->dropColumn([
                'return_status', 'approved_by', 'approved_at', 'rejection_reason',
                'quality_status', 'inspected_by', 'inspection_notes',
                'return_deadline', 'is_within_deadline',
            ]);
        });
    }
};
