<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('caption')->nullable();
            $table->json('platforms');
            $table->timestamp('scheduled_at')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'published','failed', 'partially_published'])->default('pending');
            $table->text('response_logs')->nullable();
            $table->timestamps();
        });

        // Insert permissions manually
        DB::table('permissions')->insert([
            ['name' => 'manage_posts', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create_posts', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_posts', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_posts', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_posts');

        //Remove permissions if migration is rolled back
        DB::table('permissions')->whereIn('name', [
            'manage_posts',
            'create_posts',
            'edit_posts',
            'delete_posts',
        ])->delete();
    }
};
