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
        Schema::create('daily_incomes', function (Blueprint $table) {
            $table->id(); // Crea la columna 'id' como PK
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK hacia users
            $table->decimal('income_amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('daily_income');
    }
    
};
