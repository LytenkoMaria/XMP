<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmpTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xmp_tags', function (Blueprint $table) {
            $table->bigIncrements('tag_id');
            $table->string('name');
            $table->string('label');
            $table->string('prefix');
            $table->enum('type', ['XmpText', 'XmpBag', 'XmpSeq', 'LangAlt']);
            $table->enum('valueType', ['Text','Date','URL','ClosedChoice','OpenedChoice', 'Boolean'])->nullable();
            $table->enum('category', ['External', 'Internal'])->nullable();
            $table->text('description')->nullable();
            $table->string('bind')->nullable()->default(false);
            $table->boolean('readonly')->nullable()->default(false);
            $table->json('enumeration')->nullable();
            // $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xmp_tags');
    }
}
