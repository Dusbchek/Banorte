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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK hacia users
            $table->foreignId('special_section_id')->constrained('special_sections')->onDelete('cascade'); // FK hacia special_sections
            $table->string('investment_type');
            $table->decimal('amount', 10, 2);
            $table->decimal('result', 10, 2)->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('investments');
    }
    
};
