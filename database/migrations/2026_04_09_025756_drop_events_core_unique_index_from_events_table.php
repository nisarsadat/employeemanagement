<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_core_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unique(
                [
                    'event_type',
                    'event_specific_type',
                    'target_person',
                    'criminal',
                    'event_zone',
                    'event_hawza',
                    'event_specific_place',
                ],
                'events_core_unique'
            );
        });
    }
};