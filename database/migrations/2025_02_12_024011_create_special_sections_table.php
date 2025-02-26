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
    Schema::create('special_sections', function (Blueprint $table) {
        $table->id(); // Crea la columna 'id' como PK
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK hacia users
        $table->string('name')->nullable();
        $table->text('description')->nullable();
        $table->decimal('balance', 10, 2)->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('special_sections');
}

};
