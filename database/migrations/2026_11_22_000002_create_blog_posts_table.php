<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the current public guide payloads so the articles are present after
     * a production migration, while the config remains a safe pre-migration fallback.
     */
    public function up(): void
    {
        if (! Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->string('slug')->primary();
                $table->json('payload');
                $table->date('published_at')->nullable()->index();
                $table->timestamps();
            });
        }

        $posts = config('blog.posts', []);
        $slugs = [
            'lovable-website-to-mobile-app',
            'base44-website-to-mobile-app',
        ];

        foreach ($slugs as $slug) {
            if (! isset($posts[$slug])) {
                throw new RuntimeException("The blog post [{$slug}] is missing from config/blog.php.");
            }

            $post = $posts[$slug];
            $now = now();

            DB::table('blog_posts')->updateOrInsert(
                ['slug' => $slug],
                [
                    'payload' => json_encode($post, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'published_at' => $post['published_at'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
