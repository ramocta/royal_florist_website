<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    // Ini adalah perintah yang akan diketik di CMD
    protected $signature = 'make:service {name : Nama kelas service yang ingin dibuat}';

    protected $description = 'Membuat kelas Service baru untuk logika bisnis';

    public function handle()
    {
        $name = $this->argument('name');

        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $path = app_path("Services/{$name}.php");

        if (File::exists($path)) {
            $this->error("Service {$name} sudah ada!");
            return;
        }

        File::ensureDirectoryExists(dirname($path));

        $template = "<?php\n\nnamespace App\Services;\n\nclass {$name}\n{\n    // Tulis logika bisnis Anda di sini\n}\n";

        File::put($path, $template);

        $this->info("Service {$name} berhasil dibuat di app/Services/{$name}.php");
    }
}
