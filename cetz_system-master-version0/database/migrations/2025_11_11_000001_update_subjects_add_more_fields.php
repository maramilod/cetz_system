<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('number')->nullable()->after('id');
            $table->string('code')->nullable()->after('number');
            $table->unsignedTinyInteger('units')->nullable()->after('name');
            $table->unsignedTinyInteger('hours')->nullable()->after('units');
            $table->string('depends_on')->nullable()->after('hours');
            $table->string('alternative_for')->nullable()->after('depends_on');
            $table->string('user_name')->nullable()->after('alternative_for');
            $table->index('code');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['number']);
            $table->dropColumn(['number','code','units','hours','depends_on','alternative_for','user_name']);
        });
    }
};

