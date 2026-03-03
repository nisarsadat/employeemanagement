<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('information', function (Blueprint $table) {
            $table->id();

            // ===============================
            // Foreign keys
            // ===============================
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->unsignedBigInteger('office_employee_id')->nullable();       // کود کارمند مربوطه
            $table->unsignedBigInteger('office_employee_info_id')->nullable();  // کود کارمند اطلاع گیرنده
            $table->foreignId('account_id')->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // ===============================
            // Personal info
            // ===============================
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('grand_father_name')->nullable();
            $table->string('age')->nullable();
            $table->string('tahlil')->nullable();
            $table->string('job')->nullable();
            $table->string('madani_halat')->nullable();
            $table->string('estijalyat_degree')->nullable();
            $table->string('information_category')->nullable();
            $table->string('resource_lasrasy')->nullable();
            $table->string('message_to_finance')->nullable();
            $table->string('money_amount_words')->nullable();
            $table->string('finance_status')->default('pending');
            $table->timestamp('finance_paid_at')->nullable();

            // ===============================
            // Main address
            // ===============================
            $table->string('main_province')->nullable();
            $table->string('main_districts')->nullable();
            $table->string('main_village')->nullable();

            // ===============================
            // Current address
            // ===============================
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            $table->string('specefic_place')->nullable();
            $table->string('home_number')->nullable();
            $table->string('street_number')->nullable();

            // ===============================
            // Info types
            // ===============================
            $table->string('syasi_info_type')->nullable();
            $table->string('jenayee_info_type')->nullable();

            // ===============================
            // Info details
            // ===============================
            $table->string('information_saved_date')->nullable();

            // ✅ put these here in order (NO ->after() inside create)
            $table->string('hamahangi_date')->nullable();
            $table->string('maktop_number')->nullable();
            $table->string('organ')->nullable();
            $table->string('coordinates')->nullable();

            $table->string('information_saved_hour')->nullable();
            $table->string('information_degree')->nullable();
            $table->string('trust_percentage')->nullable();

            // ===============================
            // Text areas
            // ===============================
            $table->longText('tafseel')->nullable();
            $table->text('manager_comment')->nullable();
            $table->text('lead_drirection')->nullable();
            $table->text('talks')->nullable();
            $table->text('note')->nullable();
            $table->text('docs_note')->nullable();
            $table->text('result')->nullable();

            // ===============================
            // RESULT MISSING NOTIFICATION TRACKING 🔔
            // ===============================
            $table->timestamp('result_missing_first_notified_at')->nullable();
            $table->timestamp('result_missing_last_notified_at')->nullable();
            $table->unsignedTinyInteger('result_missing_notify_count')->default(0);
            // 0 = never notified
            // 1 = notified after 7 days
            // 2 = notified after 3 months (STOP)

            // ===============================
            // Extra
            // ===============================
            $table->string('Province_addr')->nullable();

            $table->timestamps();

            // ===============================
            // Foreign key constraints
            // ===============================
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->nullOnDelete();

            $table->foreign('office_employee_id')
                ->references('id')
                ->on('office_employees')
                ->nullOnDelete();

            // (Optional) If you want FK for office_employee_info_id too:
            // $table->foreign('office_employee_info_id')
            //     ->references('id')
            //     ->on('office_employees')
            //     ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
