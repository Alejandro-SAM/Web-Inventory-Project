<?php

namespace App\Imports;

use App\Models\InventoryImportRow;
use App\Services\InventoryImportNormalizer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private string $batchId;
    private int $createdBy;
    private int $rowNumber = 2;
    private array $seenItNumbers = [];

    public function __construct(string $batchId, int $createdBy)
    {
        $this->batchId = $batchId;
        $this->createdBy = $createdBy;
    }

    /**
     * Process Excel rows and store them in the temporary import table.
     */
    public function collection(Collection $rows): void
    {
        $normalizer = app(InventoryImportNormalizer::class);

        foreach ($rows as $row) {
            $rawData = $row->toArray();

            if ($this->isEmptyRow($rawData)) {
                $this->rowNumber++;
                continue;
            }

            $result = $normalizer->normalize($rawData, $this->seenItNumbers);

            /*
                Ignore "document" type entries from Excel imports.
                They should not be stored temporarily, reviewed, or imported.
            */
            if ($this->shouldIgnoreRow($result['data'])) {
                $this->rowNumber++;
                continue;
            }

            if (!empty($result['data']['it_internal_number'])) {
                $this->seenItNumbers[] = $result['data']['it_internal_number'];
            }

            InventoryImportRow::create([
                'batch_id' => $this->batchId,
                'row_number' => $this->rowNumber,
                'raw_data' => $rawData,
                'normalized_data' => $result['data'],
                'errors' => $result['errors'],
                'status' => $result['status'],
                'created_by' => $this->createdBy,
            ]);

            $this->rowNumber++;
        }
    }

    /**
     * Read the file in chunks to reduce memory usage.
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Detect completely empty Excel rows.
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if an Excel row should be ignored completely.
     */
    private function shouldIgnoreRow(array $data): bool
    {
        $category = strtolower(trim((string) ($data['category'] ?? '')));

        return in_array($category, [
            'documento',
            'document',
        ], true);
    }
}