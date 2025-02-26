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
        Schema::create('investment_results', function (Blueprint $table) {
            $table->id(); // Crea la columna 'id' como PK
            $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade'); // FK hacia investments
            $table->decimal('result', 10, 2);
            $table->timestamp('date');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('investment_results');
    }
    
};
