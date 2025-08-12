<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Letter Templates Table
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('name');
            $table->string('category', 100); // offer, experience, warning, etc.
            $table->text('description')->nullable();
            $table->longText('content'); // Main template content with variables
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['workspace_id', 'category']);
            $table->index(['workspace_id', 'is_active']);
        });

        // Letters Table
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('user_id'); // User receiving the letter (employee)
            $table->unsignedBigInteger('template_id')->nullable(); // Template used (if any)
            $table->string('title');
            $table->longText('content'); // Final letter content
            $table->date('letter_date'); // Date of the letter
            $table->json('metadata')->nullable(); // Additional data like variables used
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('letter_templates')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['workspace_id', 'user_id']);
            $table->index(['workspace_id', 'template_id']);
            $table->index(['workspace_id', 'letter_date']);
        });

        // Letter Variables Table
        Schema::create('letter_variables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('name'); // Variable name like 'company_address'
            $table->string('label'); // Display name like 'Company Address'
            $table->text('value'); // Variable value
            $table->string('type')->default('text'); // text, number, date, etc.
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['workspace_id', 'name']);
            $table->index(['workspace_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_variables');
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
    }
};
