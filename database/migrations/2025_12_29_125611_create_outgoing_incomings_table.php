<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outgoing_incomings', function (Blueprint $table) {
            $table->id();

            $table->text('docs_type')->nullable(); // ✅ textarea text

            $table->date('office_outgoing_date')->nullable();
            $table->string('office_outgoing_page')->nullable();

            $table->date('office_incoming_date')->nullable();
            $table->string('office_incoming_page')->nullable();

            $table->date('reyasat_outgoing_date')->nullable();
            $table->string('reyasat_outgoing_page')->nullable();

            $table->date('loy_reyasat_outgoing_date')->nullable();
            $table->string('loy_reyasat_outgoing_page')->nullable();

            $table->date('answer_date')->nullable();
            $table->string('answer_page')->nullable();

            $table->date('docs_sign_date')->nullable();
            $table->date('outgoing_incoming_date')->nullable();

            $table->string('who_sighn')->nullable();

            $table->string('marja')->nullable();
            $table->text('tafseel')->nullable();

            $table->string('page')->nullable();
            $table->string('dusya')->nullable();
            $table->string('fihrist_page')->nullable();

            $table->unsignedInteger('naqal_count')->nullable();
            $table->unsignedInteger('awraq')->nullable();
            $table->unsignedInteger('zamima')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outgoing_incomings');
    }
};
