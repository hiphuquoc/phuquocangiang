<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$langs = App\Models\Language::active();
echo 'count=' . $langs->count() . PHP_EOL;
$def = App\Models\Language::default();
echo 'default=' . ($def?->code ?? 'null') . PHP_EOL;
$en = App\Models\Language::byCode('en');
echo 'en=' . ($en?->code ?? 'null') . PHP_EOL;
echo 'OK';