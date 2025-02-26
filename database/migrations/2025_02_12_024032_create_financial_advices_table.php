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
        Schema::create('financial_advices', function (Blueprint $table) {
            $table->id(); // Crea la columna 'id' como PK
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK hacia users
            $table->text('advice');
            $table->string('advice_type');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('financial_advice');
    }
    
};
