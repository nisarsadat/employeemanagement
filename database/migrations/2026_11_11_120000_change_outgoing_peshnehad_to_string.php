<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE outgoing_incomings MODIFY peshnehad VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE outgoing_incomings MODIFY peshnehad DATE NULL');
    }
};
