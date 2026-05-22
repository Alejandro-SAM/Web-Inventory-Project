<?php

namespace App\Services;

use App\Models\Inventory;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InventoryImportNormalizer
{
    /**
     * Normalize and validate one Excel row.
     */
    public function normalize(array $row, array $seenItNumbers = []): array
    {
        $errors = [];

        $data = [
            'it_internal_number' => $this->cleanText($this->getValue($row, [
                'it_internal_number',
                'it internal number',
                'it number',
            ])),

            'serial_number' => $this->cleanText($this->getValue($row, [
                'serial_number',
                'serial number',
                'serial',
            ])),

            'asset_number' => $this->cleanText($this->getValue($row, [
                'asset_number',
                'asset number',
                'asset',
            ])),

            'description' => $this->cleanText($this->getValue($row, [
                'description',
                'desc',
            ])),

            'model' => $this->cleanText($this->getValue($row, [
                'model',
            ])),

            'brand' => $this->cleanText($this->getValue($row, [
                'brand',
                'marca',
                'manufacturer',
            ])),

            'category' => $this->cleanText($this->getValue($row, [
                'category',
                'categoria',
                'categoría',
            ])),

            'department' => $this->cleanText($this->getValue($row, [
                'department',
                'departamento',
            ])),

            'location' => $this->cleanText($this->getValue($row, [
                'location',
                'ubicacion',
                'ubicación',
            ])),

            'business_unit' => $this->cleanBusinessUnit($this->getValue($row, [
                'bu',
                'business_unit',
                'business unit',
            ])),

            'plant' => $this->cleanPlant($this->getValue($row, [
                'plant',
                'planta',
            ])),

            'end_user' => $this->cleanText($this->getValue($row, [
                'end_user',
                'end user',
                'user',
                'usuario',
            ])),

            'employee_id' => $this->cleanText($this->getValue($row, [
                'employee_id',
                'id_employee',
                'id employee',
                'employee id',
                'employee',
            ])),

            'comments' => $this->cleanText($this->getValue($row, [
                'comments',
                'comment',
                'notes',
                'comentarios',
            ])),

            'operating_system' => $this->cleanText($this->getValue($row, [
                'operating_system',
                'operating system',
                'operation_system',
                'operation system',
                'os',
            ])),

            'classification' => $this->cleanText($this->getValue($row, [
                'classification',
                'clasificacion',
                'clasificación',
            ])),
        ];

        /*
            Require at least one main identifier.
            This prevents importing completely untraceable assets.
        */
        if (
            empty($data['it_internal_number']) &&
            empty($data['serial_number']) &&
            empty($data['asset_number'])
        ) {
            $errors[] = 'At least one identifier is required: IT Internal Number, Serial Number or Asset Number.';
        }

        /*
            Check duplicated IT Internal Number in database and current file.
        */
        if (!empty($data['it_internal_number'])) {
            if (Inventory::where('it_internal_number', $data['it_internal_number'])->exists()) {
                $errors[] = 'IT Internal Number already exists in inventory.';
            }

            if (in_array($data['it_internal_number'], $seenItNumbers, true)) {
                $errors[] = 'Duplicated IT Internal Number inside the uploaded file.';
            }
        }

        /*
            Normalize responsive value.
        */
        $responsiveResult = $this->normalizeBoolean($this->getValue($row, [
            'responsive',
            'responsiva',
            'has responsive',
            'has_responsive',
        ]));

        $data['responsive'] = $responsiveResult['value'];

        if ($responsiveResult['error']) {
            $errors[] = 'Responsive value is not valid.';
        }

        /*
            Normalize next maintenance date.
        */
        $dateResult = $this->normalizeDate($this->getValue($row, [
            'next_maintenance',
            'next maintenance',
            'next_maintenance_preventive',
            'next maintenance preventive',
            'next maintenance preventive date',
            'maintenance',
        ]));

        $data['next_maintenance'] = $dateResult['value'];

        if ($dateResult['error']) {
            $errors[] = 'Next Maintenance date is not valid.';
        }

        /*
            Normalize CIA values.
        */
        foreach (['confidentiality', 'integrity', 'availability'] as $field) {
            $ciaResult = $this->normalizeCiaValue($this->getValue($row, [
                $field,
                str_replace('_', ' ', $field),
            ]));

            $data[$field] = $ciaResult['value'];

            if ($ciaResult['error']) {
                $errors[] = ucfirst($field) . ' must be between 0 and 3.';
            }
        }

        /*
            Normalize state value.
        */
        $stateResult = $this->normalizeState($this->getValue($row, [
            'state',
            'status',
            'estado',
        ]));

        $data['state'] = $stateResult['value'];

        if ($stateResult['error']) {
            $errors[] = 'State is not valid.';
        }

        return [
            'data' => $data,
            'errors' => $errors,
            'status' => count($errors) === 0 ? 'valid' : 'invalid',
        ];
    }

    /**
     * Get value from row using different possible header names.
     */
    private function getValue(array $row, array $possibleKeys): mixed
    {
        $normalizedRow = [];

        foreach ($row as $key => $value) {
            if ($key === null) {
                continue;
            }

            $normalizedKey = $this->normalizeKey($key);
            $normalizedRow[$normalizedKey] = $value;
        }

        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);

            if (array_key_exists($normalizedKey, $normalizedRow)) {
                return $normalizedRow[$normalizedKey];
            }
        }

        return null;
    }

    /**
     * Normalize header names to improve matching.
     */
    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = str_replace(["\n", "\r", "\t"], ' ', $key);
        $key = preg_replace('/\s+/', ' ', $key);

        return str_replace(' ', '_', $key);
    }

    /**
     * Clean text values and convert empty placeholders to null.
     */
    private function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $nullValues = [
            'n/a',
            'na',
            'none',
            'null',
            '-',
        ];

        if (in_array(strtolower($value), $nullValues, true)) {
            return null;
        }

        return $value;
    }

    /**
     * Normalize common BU variants from the Excel file.
     */
    private function cleanBusinessUnit($value): ?string
    {
        $value = $this->cleanText($value);

        if ($value === null) {
            return null;
        }

        $normalized = strtolower(str_replace([' ', '&', '/', '-'], '', $value));

        $map = [
            'funcionaldep' => 'Functional Dep',
            'funcionaldept' => 'Functional Dep',
            'functionaldep' => 'Functional Dep',
            'functionaldept' => 'Functional Dep',
            'functionaldepartment' => 'Functional Dep',

            'bu1bu2' => 'BU1&2',
            'bu12' => 'BU1&2',
            'bu1ybu2' => 'BU1&2',

            'bu1' => 'BU1',
            'bu2' => 'BU2',
            'bu3' => 'BU3',
            'bu5' => 'BU5',
            'bu6' => 'BU6',
        ];

        return $map[$normalized] ?? $value;
    }

    /**
     * Normalize plant values from the Excel file.
     */
    private function cleanPlant($value): ?string
    {
        $value = $this->cleanText($value);

        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));

        $map = [
            'plant b' => 'B',
            'plant d' => 'D',
            'plant g' => 'G',
            'plant h' => 'H',
            'b' => 'B',
            'd' => 'D',
            'g' => 'G',
            'h' => 'H',
            'mpi' => 'MPI',
            'mpii' => 'MPII',
            'mp' => 'MP',
        ];

        return $map[$normalized] ?? strtoupper($value);
    }

    /**
     * Convert common boolean values to true or false.
     */
    private function normalizeBoolean($value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [
                'value' => false,
                'error' => false,
            ];
        }

        $value = strtolower(trim((string) $value));

        $trueValues = [
            '1',
            'yes',
            'y',
            'true',
            'si',
            'sí',
            'x',
            'checked',
        ];

        $falseValues = [
            '0',
            'no',
            'n',
            'false',
            'unchecked',
            'n/a',
            'na',
        ];

        if (in_array($value, $trueValues, true)) {
            return [
                'value' => true,
                'error' => false,
            ];
        }

        if (in_array($value, $falseValues, true)) {
            return [
                'value' => false,
                'error' => false,
            ];
        }

        return [
            'value' => false,
            'error' => true,
        ];
    }

    /**
     * Convert CIA text values to tiny integers from 0 to 3.
     */
    private function normalizeCiaValue($value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [
                'value' => null,
                'error' => false,
            ];
        }

        $value = strtolower(trim((string) $value));

        $map = [
            '0' => 0,
            'none' => 0,
            'n/a' => 0,
            'na' => 0,
            'no aplica' => 0,

            '1' => 1,
            'low' => 1,
            'bajo' => 1,
            'baja' => 1,

            '2' => 2,
            'medium' => 2,
            'medio' => 2,
            'media' => 2,

            '3' => 3,
            'high' => 3,
            'alto' => 3,
            'alta' => 3,
            'critical' => 3,
            'critico' => 3,
            'crítico' => 3,
        ];

        if (!array_key_exists($value, $map)) {
            return [
                'value' => null,
                'error' => true,
            ];
        }

        return [
            'value' => $map[$value],
            'error' => false,
        ];
    }

    /**
     * Convert Excel numeric dates or text dates to Y-m-d.
     */
    private function normalizeDate($value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [
                'value' => null,
                'error' => false,
            ];
        }

        try {
            if (is_numeric($value)) {
                return [
                    'value' => ExcelDate::excelToDateTimeObject($value)->format('Y-m-d'),
                    'error' => false,
                ];
            }

            return [
                'value' => Carbon::parse($value)->format('Y-m-d'),
                'error' => false,
            ];
        } catch (\Throwable $e) {
            return [
                'value' => null,
                'error' => true,
            ];
        }
    }

    /**
     * Normalize state values to inventory enum values.
     */
    private function normalizeState($value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [
                'value' => 'active',
                'error' => false,
            ];
        }

        $value = strtolower(trim((string) $value));

        $map = [
            'active' => 'active',
            'activo' => 'active',
            'in use' => 'active',
            'assigned' => 'active',

            'inactive' => 'inactive',
            'inactivo' => 'inactive',
            'not in use' => 'inactive',

            'maintenance' => 'maintenance',
            'mantenimiento' => 'maintenance',
            'repair' => 'maintenance',

            'disposed' => 'disposed',
            'baja' => 'disposed',
            'scrap' => 'disposed',
            'discarded' => 'disposed',

            'lost' => 'lost',
            'perdido' => 'lost',
            'missing' => 'lost',
        ];

        if (!array_key_exists($value, $map)) {
            return [
                'value' => 'active',
                'error' => true,
            ];
        }

        return [
            'value' => $map[$value],
            'error' => false,
        ];
    }
}