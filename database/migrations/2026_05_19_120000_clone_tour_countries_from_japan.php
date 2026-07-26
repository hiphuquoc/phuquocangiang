<?php

use App\Models\Language;
use App\Models\Seo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Clone trang Tour quốc gia mẫu (Nhật Bản) sang các quốc gia theo châu lục.
 *
 * Nguồn: tour_country slug `tour-du-lich-nhat-ban` (slug_full: tour-du-lich-chau-a/tour-du-lich-nhat-ban).
 * Không copy: gallery (system_file), FAQ, liên kết guide/service/air.
 */
return new class extends Migration
{
    private const SOURCE_SLUG = 'tour-du-lich-nhat-ban';

    private const SOURCE_SLUG_FULL = 'tour-du-lich-chau-a/tour-du-lich-nhat-ban';

    /** @var array<string, string> continent key => seo slug châu lục */
    private const CONTINENT_SLUGS = [
        'asia'     => 'tour-du-lich-chau-a',
        'europe'   => 'tour-du-lich-chau-au',
        'americas' => 'tour-du-lich-chau-my',
        'oceania'  => 'tour-du-lich-chau-uc',
    ];

    private const PLACEHOLDER_IMAGE = '/storage/images/placeholder-tour-country.jpg';

    private const PLACEHOLDER_IMAGE_SMALL = '/storage/images/placeholder-tour-country-sm.jpg';

    /** @var list<string> Slugs created by this migration (for rollback). */
    private array $createdSlugs = [];

    /**
     * @var list<array{
     *   continent: string,
     *   name: string,
     *   slug: string,
     *   en?: string
     * }>
     */
    private const TARGETS = [
        // Châu Á (parent: tour-du-lich-chau-a)
        ['continent' => 'asia', 'name' => 'Thái Lan', 'slug' => 'tour-du-lich-thai-lan', 'en' => 'Thailand'],
        ['continent' => 'asia', 'name' => 'Đài Loan', 'slug' => 'tour-du-lich-dai-loan', 'en' => 'Taiwan'],
        ['continent' => 'asia', 'name' => 'Trung Quốc', 'slug' => 'tour-du-lich-trung-quoc', 'en' => 'China'],
        ['continent' => 'asia', 'name' => 'Malaysia', 'slug' => 'tour-du-lich-malaysia', 'en' => 'Malaysia'],
        ['continent' => 'asia', 'name' => 'Indonesia', 'slug' => 'tour-du-lich-indonesia', 'en' => 'Indonesia'],
        ['continent' => 'asia', 'name' => 'Philippines', 'slug' => 'tour-du-lich-philippines', 'en' => 'Philippines'],
        // Châu Âu (parent: tour-du-lich-chau-au)
        ['continent' => 'europe', 'name' => 'Pháp', 'slug' => 'tour-du-lich-phap', 'en' => 'France'],
        ['continent' => 'europe', 'name' => 'Ý', 'slug' => 'tour-du-lich-y', 'en' => 'Italy'],
        ['continent' => 'europe', 'name' => 'Thụy Sĩ', 'slug' => 'tour-du-lich-thuy-si', 'en' => 'Switzerland'],
        ['continent' => 'europe', 'name' => 'Đức', 'slug' => 'tour-du-lich-duc', 'en' => 'Germany'],
        ['continent' => 'europe', 'name' => 'Hà Lan', 'slug' => 'tour-du-lich-ha-lan', 'en' => 'Netherlands'],
        ['continent' => 'europe', 'name' => 'Tây Ban Nha', 'slug' => 'tour-du-lich-tay-ban-nha', 'en' => 'Spain'],
        ['continent' => 'europe', 'name' => 'Anh', 'slug' => 'tour-du-lich-anh', 'en' => 'United Kingdom'],
        ['continent' => 'europe', 'name' => 'Bỉ', 'slug' => 'tour-du-lich-bi', 'en' => 'Belgium'],
        ['continent' => 'europe', 'name' => 'Áo', 'slug' => 'tour-du-lich-ao', 'en' => 'Austria'],
        // Châu Mỹ (parent: tour-du-lich-chau-my)
        ['continent' => 'americas', 'name' => 'Mỹ', 'slug' => 'tour-du-lich-my', 'en' => 'United States'],
        ['continent' => 'americas', 'name' => 'Canada', 'slug' => 'tour-du-lich-canada', 'en' => 'Canada'],
        ['continent' => 'americas', 'name' => 'Brazil', 'slug' => 'tour-du-lich-brazil', 'en' => 'Brazil'],
        ['continent' => 'americas', 'name' => 'Mexico', 'slug' => 'tour-du-lich-mexico', 'en' => 'Mexico'],
        // Châu Úc (parent: tour-du-lich-chau-uc)
        ['continent' => 'oceania', 'name' => 'Úc', 'slug' => 'tour-du-lich-uc', 'en' => 'Australia'],
        ['continent' => 'oceania', 'name' => 'New Zealand', 'slug' => 'tour-du-lich-new-zealand', 'en' => 'New Zealand'],
    ];

    public function up(): void
    {
        $source = $this->loadSourceJapan();
        $continents = $this->loadContinentsByKey();
        $defaultLangId = $this->defaultLanguageId();

        $sourceContent = $this->loadSourceContent((int) $source->seo_id, $source->slug);
        $sourceSeoTranslations = $this->loadSeoTranslations((int) $source->seo_id);
        $sourceEntityTranslations = $this->loadEntityTranslations('tour_country_translations', 'tour_country_id', (int) $source->id);

        DB::transaction(function () use ($source, $continents, $defaultLangId, $sourceContent, $sourceSeoTranslations, $sourceEntityTranslations) {
            $orderingCursor = [];

            foreach (self::TARGETS as $target) {
                if ($this->slugExists($target['slug'])) {
                    continue;
                }

                $continent = $continents[$target['continent']] ?? null;
                if (!$continent) {
                    throw new \RuntimeException("Không tìm thấy châu lục: {$target['continent']}");
                }

                $parentSeoId = (int) $continent->seo_id;
                $slugFull = Seo::buildFullUrl($target['slug'], $parentSeoId);
                $replacements = $this->buildReplacements($source, $target, $slugFull);
                $ratingCount = random_int(48, 386);
                $now = now();

                if (!isset($orderingCursor[$parentSeoId])) {
                    $orderingCursor[$parentSeoId] = (int) (DB::table('seo')->where('parent', $parentSeoId)->max('ordering') ?? 0);
                }
                $orderingCursor[$parentSeoId]++;

                $seoPayload = $this->mapSeoRow($source, $replacements, $target, $parentSeoId, $slugFull, $ratingCount, $orderingCursor[$parentSeoId], $now);
                $newSeoId = (int) DB::table('seo')->insertGetId($seoPayload);

                $countryPayload = $this->mapTourCountryRow($source, $replacements, $target, (int) $continent->id, $newSeoId, $now);
                $newCountryId = (int) DB::table('tour_country')->insertGetId($countryPayload);

                $this->syncSeoTranslations($sourceSeoTranslations, $newSeoId, $replacements, $parentSeoId, $target['slug'], $defaultLangId, $now);
                $this->syncEntityTranslations($sourceEntityTranslations, 'tour_country_translations', 'tour_country_id', $newCountryId, $replacements, $defaultLangId, $now);
                $this->writeContent($sourceContent, $newSeoId, $target['slug'], $replacements, $defaultLangId, $now);

                $this->createdSlugs[] = $target['slug'];
            }
        });

        if (class_exists(Language::class)) {
            try {
                Language::flushCache();
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        $slugs = array_merge($this->createdSlugs, array_column(self::TARGETS, 'slug'));
        $slugs = array_values(array_unique($slugs));

        DB::transaction(function () use ($slugs) {
            foreach ($slugs as $slug) {
                $seo = DB::table('seo')->where('type', 'tour_country')->where('slug', $slug)->first();
                if (!$seo) {
                    continue;
                }

                $country = DB::table('tour_country')->where('seo_id', $seo->id)->first();

                if ($country) {
                    if (Schema::hasTable('relation_tour_country_guide_info')) {
                        DB::table('relation_tour_country_guide_info')->where('tour_country_id', $country->id)->delete();
                    }
                    if (Schema::hasTable('relation_tour_country_service_location')) {
                        DB::table('relation_tour_country_service_location')->where('tour_country_id', $country->id)->delete();
                    }
                    if (Schema::hasTable('relation_tour_country_air_location')) {
                        DB::table('relation_tour_country_air_location')->where('tour_country_id', $country->id)->delete();
                    }
                    if (Schema::hasTable('question_answer_info')) {
                        DB::table('question_answer_info')
                            ->where('relation_table', 'tour_country')
                            ->where('reference_id', $country->id)
                            ->delete();
                    }
                    if (Schema::hasTable('system_file')) {
                        DB::table('system_file')
                            ->where('relation_table', 'tour_country')
                            ->where('attachment_id', $country->id)
                            ->delete();
                    }
                    if (Schema::hasTable('tour_country_translations')) {
                        DB::table('tour_country_translations')->where('tour_country_id', $country->id)->delete();
                    }
                    DB::table('tour_country')->where('id', $country->id)->delete();
                }

                if (Schema::hasTable('seo_content_translations')) {
                    DB::table('seo_content_translations')->where('seo_id', $seo->id)->delete();
                }
                if (Schema::hasTable('seo_translations')) {
                    DB::table('seo_translations')->where('seo_id', $seo->id)->delete();
                }
                DB::table('seo')->where('id', $seo->id)->delete();

                $this->deleteContentFile($slug);
            }
        });
    }

    private function loadSourceJapan(): object
    {
        $row = DB::table('tour_country as tc')
            ->join('seo as s', 's.id', '=', 'tc.seo_id')
            ->where('s.type', 'tour_country')
            ->where('s.slug', self::SOURCE_SLUG)
            ->select('tc.*', 's.slug', 's.slug_full as source_slug_full')
            ->first();

        if (!$row) {
            throw new \RuntimeException('Không tìm thấy trang nguồn tour_country slug: ' . self::SOURCE_SLUG);
        }

        return $row;
    }

    /**
     * @return array<string, object{ id: int, seo_id: int, name: string, slug: string, slug_full: string }>
     */
    private function loadContinentsByKey(): array
    {
        $map = [];

        foreach (self::CONTINENT_SLUGS as $key => $continentSlug) {
            $row = DB::table('tour_continent as t')
                ->join('seo as s', 's.id', '=', 't.seo_id')
                ->where('s.slug', $continentSlug)
                ->select('t.id', 't.name', 't.display_name', 's.id as seo_id', 's.slug', 's.slug_full')
                ->first();

            if (!$row) {
                throw new \RuntimeException("Không tìm thấy châu lục slug: {$continentSlug}");
            }

            $map[$key] = $row;
        }

        return $map;
    }

    private function defaultLanguageId(): ?int
    {
        if (!Schema::hasTable('languages')) {
            return null;
        }

        $code = config('language.default_code', 'vi');
        $row = DB::table('languages')->where('code', $code)->first();

        return $row ? (int) $row->id : null;
    }

    private function slugExists(string $slug): bool
    {
        return DB::table('seo')->where('type', 'tour_country')->where('slug', $slug)->exists();
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacements(object $source, array $target, string $slugFull): array
    {
        $sourceName = (string) ($source->name ?? 'Nhật Bản');
        $sourceSlug = (string) ($source->slug ?? self::SOURCE_SLUG);
        $targetName = $target['name'];
        $targetSlug = $target['slug'];
        $targetEn = $target['en'] ?? $targetName;

        $sourceSlugFull = (string) ($source->source_slug_full ?? self::SOURCE_SLUG_FULL);
        $targetContinentSlug = self::CONTINENT_SLUGS[$target['continent']] ?? self::CONTINENT_SLUGS['asia'];

        $pairs = [
            $sourceName => $targetName,
            mb_strtoupper($sourceName, 'UTF-8') => mb_strtoupper($targetName, 'UTF-8'),
            mb_strtolower($sourceName, 'UTF-8') => mb_strtolower($targetName, 'UTF-8'),
            $sourceSlug => $targetSlug,
            self::SOURCE_SLUG => $targetSlug,
            'nhat-ban' => str_replace('tour-du-lich-', '', $targetSlug),
            str_replace('-', ' ', $sourceSlug) => str_replace('-', ' ', $targetSlug),
            $sourceSlugFull => $slugFull,
            self::SOURCE_SLUG_FULL => $slugFull,
            'Nhật Bản' => $targetName,
            'NHẬT BẢN' => mb_strtoupper($targetName, 'UTF-8'),
            'nhật bản' => mb_strtolower($targetName, 'UTF-8'),
            'Nhat Ban' => $targetName,
            'Japan' => $targetEn,
            'japan' => strtolower($targetEn),
            'JAPAN' => strtoupper($targetEn),
        ];

        $continentSwaps = [
            'europe' => [
                'Châu Á' => 'Châu Âu',
                'châu á' => 'châu âu',
                'CHÂU Á' => 'CHÂU ÂU',
                'Asia' => 'Europe',
                self::CONTINENT_SLUGS['asia'] => self::CONTINENT_SLUGS['europe'],
            ],
            'americas' => [
                'Châu Á' => 'Châu Mỹ',
                'châu á' => 'châu mỹ',
                'CHÂU Á' => 'CHÂU MỸ',
                'Asia' => 'Americas',
                self::CONTINENT_SLUGS['asia'] => self::CONTINENT_SLUGS['americas'],
            ],
            'oceania' => [
                'Châu Á' => 'Châu Úc',
                'châu á' => 'châu úc',
                'CHÂU Á' => 'CHÂU ÚC',
                'Asia' => 'Oceania',
                self::CONTINENT_SLUGS['asia'] => self::CONTINENT_SLUGS['oceania'],
            ],
        ];

        if ($target['continent'] !== 'asia') {
            $pairs[self::CONTINENT_SLUGS['asia']] = $targetContinentSlug;
            $pairs[self::CONTINENT_SLUGS['asia'] . '/' . self::SOURCE_SLUG] = $slugFull;
        }

        if (!empty($continentSwaps[$target['continent']])) {
            $pairs = array_merge($pairs, $continentSwaps[$target['continent']]);
        }

        uksort($pairs, static fn ($a, $b) => strlen($b) <=> strlen($a));

        return $pairs;
    }

    private function applyReplacements(?string $text, array $replacements): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private function mapSeoRow(
        object $source,
        array $replacements,
        array $target,
        int $parentSeoId,
        string $slugFull,
        int $ratingCount,
        int $ordering,
        $now
    ): array {
        $seo = DB::table('seo')->where('id', $source->seo_id)->first();
        if (!$seo) {
            throw new \RuntimeException('SEO nguồn không tồn tại: ' . $source->seo_id);
        }

        $title = $this->applyReplacements((string) $seo->title, $replacements) ?: ('Tour ' . $target['name']);
        $description = $this->applyReplacements((string) $seo->description, $replacements);
        $seoTitle = $this->applyReplacements((string) ($seo->seo_title ?? $seo->title), $replacements) ?: $title;
        $seoDescription = $this->applyReplacements((string) ($seo->seo_description ?? $seo->description), $replacements);

        return [
            'title' => $title,
            'description' => $description,
            'image' => self::PLACEHOLDER_IMAGE,
            'image_small' => self::PLACEHOLDER_IMAGE_SMALL,
            'level' => (int) ($seo->level ?? 3),
            'parent' => $parentSeoId,
            'ordering' => $ordering,
            'topic' => $seo->topic,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'slug' => $target['slug'],
            'slug_full' => $slugFull,
            'link_canonical' => $slugFull,
            'type' => 'tour_country',
            'rating_author_name' => $seo->rating_author_name ?? '1',
            'rating_author_star' => $seo->rating_author_star ?? '5',
            'rating_aggregate_count' => $ratingCount,
            'rating_aggregate_star' => '4.8',
            'video' => null,
            'auto_post' => $seo->auto_post ?? 0,
            'created_by' => $seo->created_by ?? 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function mapTourCountryRow(
        object $source,
        array $replacements,
        array $target,
        int $continentId,
        int $seoId,
        $now
    ): array {
        return [
            'tour_continent_id' => $continentId,
            'name' => $target['name'],
            'display_name' => $this->applyReplacements((string) ($source->display_name ?? $source->name), $replacements) ?: $target['name'],
            'description' => $this->applyReplacements((string) $source->description, $replacements),
            'seo_id' => $seoId,
            'island' => (int) ($source->island ?? 0),
            'note' => $this->applyReplacements($source->note ?? null, $replacements),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function loadSourceContent(int $seoId, string $slug): ?string
    {
        if (Schema::hasTable('seo_content_translations')) {
            $row = DB::table('seo_content_translations')->where('seo_id', $seoId)->orderBy('id')->first();
            if ($row && !empty($row->content)) {
                return (string) $row->content;
            }
        }

        foreach ([
            $slug . '.blade.php',
            'nhat-ban.blade.php',
            'tour-du-lich-nhat-ban.blade.php',
        ] as $filename) {
            $path = base_path('public/contents/tourCountries/' . $filename);
            if (File::isFile($path)) {
                return File::get($path);
            }
        }

        return null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function loadSeoTranslations(int $seoId)
    {
        if (!Schema::hasTable('seo_translations')) {
            return collect();
        }

        return DB::table('seo_translations')->where('seo_id', $seoId)->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function loadEntityTranslations(string $table, string $fk, int $entityId)
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }

        return DB::table($table)->where($fk, $entityId)->get();
    }

    private function syncSeoTranslations(
        $rows,
        int $newSeoId,
        array $replacements,
        int $parentSeoId,
        string $slug,
        ?int $defaultLangId,
        $now
    ): void {
        if (!Schema::hasTable('seo_translations') || $rows->isEmpty()) {
            $this->insertDefaultSeoTranslation($newSeoId, $replacements, $parentSeoId, $slug, $defaultLangId, $now);

            return;
        }

        foreach ($rows as $row) {
            $rowSlug = $slug;
            $rowSlugFull = Seo::buildFullUrl($rowSlug, $parentSeoId);
            DB::table('seo_translations')->insert([
                'seo_id' => $newSeoId,
                'language_id' => $row->language_id,
                'title' => $this->applyReplacements($row->title ?? null, $replacements),
                'description' => $this->applyReplacements($row->description ?? null, $replacements),
                'seo_title' => $this->applyReplacements($row->seo_title ?? null, $replacements),
                'seo_description' => $this->applyReplacements($row->seo_description ?? null, $replacements),
                'slug' => $rowSlug,
                'slug_full' => $rowSlugFull,
                'link_canonical' => $rowSlugFull,
                'status' => $row->status ?? 'published',
                'translated_by' => 'clone-migration',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function insertDefaultSeoTranslation(
        int $seoId,
        array $replacements,
        int $parentSeoId,
        string $slug,
        ?int $defaultLangId,
        $now
    ): void {
        if (!$defaultLangId || !Schema::hasTable('seo_translations')) {
            return;
        }

        $seo = DB::table('seo')->where('id', $seoId)->first();
        if (!$seo) {
            return;
        }

        $slugFull = Seo::buildFullUrl($slug, $parentSeoId);
        DB::table('seo_translations')->insertOrIgnore([
            'seo_id' => $seoId,
            'language_id' => $defaultLangId,
            'title' => $seo->title,
            'description' => $seo->description,
            'seo_title' => $seo->seo_title,
            'seo_description' => $seo->seo_description,
            'slug' => $slug,
            'slug_full' => $slugFull,
            'link_canonical' => $slugFull,
            'status' => 'published',
            'translated_by' => 'clone-migration',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function syncEntityTranslations(
        $rows,
        string $table,
        string $fk,
        int $newEntityId,
        array $replacements,
        ?int $defaultLangId,
        $now
    ): void {
        if (!Schema::hasTable($table)) {
            return;
        }

        if ($rows->isEmpty() && $defaultLangId) {
            $source = DB::table('tour_country')->where('id', $newEntityId)->first();
            if (!$source) {
                return;
            }
            DB::table($table)->insertOrIgnore([
                $fk => $newEntityId,
                'language_id' => $defaultLangId,
                'name' => $source->name,
                'description' => $source->description,
                'status' => 'published',
                'translated_by' => 'clone-migration',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        foreach ($rows as $row) {
            $payload = (array) $row;
            unset($payload['id']);
            $payload[$fk] = $newEntityId;
            if (isset($payload['name'])) {
                $payload['name'] = $this->applyReplacements((string) $payload['name'], $replacements);
            }
            if (isset($payload['description'])) {
                $payload['description'] = $this->applyReplacements((string) $payload['description'], $replacements);
            }
            $payload['translated_by'] = 'clone-migration';
            $payload['created_at'] = $now;
            $payload['updated_at'] = $now;
            DB::table($table)->insert($payload);
        }
    }

    private function writeContent(
        ?string $sourceContent,
        int $newSeoId,
        string $slug,
        array $replacements,
        ?int $defaultLangId,
        $now
    ): void {
        if ($sourceContent === null || $sourceContent === '') {
            return;
        }

        $content = $this->applyReplacements($sourceContent, $replacements);

        if (Schema::hasTable('seo_content_translations') && $defaultLangId) {
            DB::table('seo_content_translations')->insertOrIgnore([
                'seo_id' => $newSeoId,
                'language_id' => $defaultLangId,
                'content' => $content,
                'status' => 'published',
                'translated_by' => 'clone-migration',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $dir = base_path('public/contents/tourCountries');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::put($dir . '/' . $slug . '.blade.php', $content);
    }

    private function deleteContentFile(string $slug): void
    {
        $path = base_path('public/contents/tourCountries/' . $slug . '.blade.php');
        if (File::isFile($path)) {
            File::delete($path);
        }
    }
};
