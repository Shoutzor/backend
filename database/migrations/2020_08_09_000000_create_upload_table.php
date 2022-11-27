<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'uploads',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('source');
                $table->string('original_filename');
                $table->string('filename');
                $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->cascadeOnDelete();
                $table->timestamp('uploaded_at')->useCurrent();
                $table->smallInteger('status')->unsigned();
                $table->json('data');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploads');
    }
}
