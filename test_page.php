<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class TestGeneratePosterPage extends App\Filament\Pages\GeneratePosterPage {
    protected function currentUser(): App\Models\User {
        return App\Models\User::first();
    }
}

$page = new TestGeneratePosterPage();
$page->mount();
dump($page->data);
