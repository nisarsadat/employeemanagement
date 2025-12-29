<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outgoing_incomings', function (Blueprint $table) {
            $table->id();

            /* ===============================
             |  TEXT FLAGS (your inputs are text)
             |===============================*/
            $table->string('outgoing_to_reyasat')->nullable();
            $table->string('out_going_to_others')->nullable();
            $table->string('incomming')->nullable();

            $table->string('ahkam_reyasat')->nullable();
            $table->string('ahkam_rad_shoda_reyasat')->nullable();
            $table->string('reyasat_na_direction')->nullable();

            $table->string('loy_reyasat_ahkam')->nullable();
            $table->string('loy_reyasat_ahakm_rad')->nullable();
            $table->string('loy_reyasat_direction')->nullable();

            /* ===============================
             |  IDENTIFIERS & DATES
             |===============================*/
            $table->unsignedBigInteger('number')->nullable(); // only numeric
            $table->date('sign_date')->nullable();
            $table->date('outgoing_incoming_date')->nullable();

            /* ===============================
             |  META INFO
             |===============================*/
            $table->string('marja')->nullable();
            $table->string('who_sign')->nullable();

            $table->string('numer_of_jold')->nullable();
            $table->string('list_number')->nullable();

            /* ===============================
             |  VALUES (keep as string to avoid DB errors)
             |===============================*/
            $table->string('naql_amount')->nullable();
            $table->string('pages')->nullable();

            /* ===============================
             |  TEXT
             |===============================*/
            $table->text('zamima')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outgoing_incomings');
    }
};
