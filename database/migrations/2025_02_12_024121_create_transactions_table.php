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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // Crea la columna 'id' como PK
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK hacia users
            $table->foreignId('special_section_id')->constrained('special_sections')->onDelete('cascade'); // FK hacia special_sections
            $table->string('transaction_type');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
