<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixServiceSlugs extends Command
{
    protected $signature = 'services:fix-slugs {--force : Rebuild all slugs even if already set}';
    protected $description = 'Generate SEO-friendly unique slugs for services from title_en/title_ar';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $total = DB::table('services')->count();
        $this->info("Services: {$total}");

        $query = DB::table('services')->select('id', 'title_en', 'title_ar', 'slug')->orderBy('id');

        $updated = 0;

        $query->chunkById(200, function ($rows) use ($force, &$updated) {
            foreach ($rows as $r) {
                if (! $force && ! empty($r->slug) && $r->slug !== "service-{$r->id}") {
                    continue; // already good
                }

                $base = trim($r->title_en ?: $r->title_ar ?: "service-{$r->id}");
                $slugBase = Str::slug($base, '-');

                if ($slugBase === '') {
                    $slugBase = "service";
                }

                // Always make it unique and stable with id
                $final = "{$slugBase}-{$r->id}";

                DB::table('services')->where('id', $r->id)->update(['slug' => $final]);
                $updated++;
            }
        });

        $this->info("Updated: {$updated}");

        $missing = DB::table('services')->whereNull('slug')->orWhere('slug', '')->count();
        $dups = DB::table('services')
            ->select('slug')
            ->whereNotNull('slug')->where('slug', '!=', '')
            ->groupBy('slug')->havingRaw('COUNT(*) > 1')
            ->count();

        $this->info("Missing: {$missing}, Duplicates: {$dups}");

        return self::SUCCESS;
    }
}
