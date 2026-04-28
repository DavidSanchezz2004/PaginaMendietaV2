<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ubigeos:import {file : Ruta del Excel/CSV con columnas IDDIST, NOMBDEP, NOMBPROV, NOMBDIST, NOM_CAPITAL}', function (string $file): int {
    $path = base_path($file);

    if (! is_file($path)) {
        $path = $file;
    }

    if (! is_file($path)) {
        $this->error("No se encontró el archivo: {$file}");
        return Command::FAILURE;
    }

    $spreadsheet = IOFactory::load($path);
    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    if (count($rows) < 2) {
        $this->error('El archivo no tiene filas para importar.');
        return Command::FAILURE;
    }

    $header = array_map(
        fn ($value) => strtoupper(trim((string) $value)),
        array_shift($rows)
    );

    $columnFor = function (array $names) use ($header): ?string {
        foreach ($header as $column => $label) {
            if (in_array($label, $names, true)) {
                return $column;
            }
        }

        return null;
    };

    $codeColumn = $columnFor(['IDDIST', 'UBIGEO', 'CODE', 'CODIGO']);
    $departmentColumn = $columnFor(['NOMBDEP', 'DEPARTAMENTO']);
    $provinceColumn = $columnFor(['NOMBPROV', 'PROVINCIA']);
    $districtColumn = $columnFor(['NOMBDIST', 'DISTRITO']);
    $capitalColumn = $columnFor(['NOM_CAPITAL (LEGAL)', 'NOM_CAPITAL', 'CAPITAL', 'NOM CAPITAL']);

    if (! $codeColumn || ! $departmentColumn || ! $provinceColumn || ! $districtColumn) {
        $this->error('El archivo debe tener columnas IDDIST, NOMBDEP, NOMBPROV y NOMBDIST.');
        return Command::FAILURE;
    }

    $payload = [];

    foreach ($rows as $row) {
        $code = preg_replace('/\D/', '', (string) ($row[$codeColumn] ?? ''));
        if ($code === '') {
            continue;
        }

        $payload[] = [
            'code' => str_pad(substr($code, 0, 6), 6, '0', STR_PAD_LEFT),
            'department' => trim((string) ($row[$departmentColumn] ?? '')),
            'province' => trim((string) ($row[$provinceColumn] ?? '')),
            'district' => trim((string) ($row[$districtColumn] ?? '')),
            'legal_capital' => $capitalColumn ? trim((string) ($row[$capitalColumn] ?? '')) ?: null : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    collect($payload)
        ->chunk(500)
        ->each(fn ($chunk) => DB::table('ubigeos')->upsert(
            $chunk->all(),
            ['code'],
            ['department', 'province', 'district', 'legal_capital', 'updated_at']
        ));

    $this->info('Ubigeos importados/actualizados: ' . count($payload));

    return Command::SUCCESS;
})->purpose('Importa catálogo de ubigeos desde Excel/CSV');
