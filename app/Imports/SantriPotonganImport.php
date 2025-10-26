<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Validators\ValidationException;

class SantriPotonganImport implements ToCollection, WithHeadingRow
{
    public $errors = [];

    public function collection(Collection $rows)
    {
        $data = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena heading row di Excel

            $validator = Validator::make($row->toArray(), [
                'santri_id'   => 'required|integer|exists:santri,id',
                'potongan_id' => 'required|integer|exists:potongan,id',
                'keterangan'  => 'nullable|string|max:255',
                'status'      => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $this->errors[$rowNumber] = $validator->errors()->all();
                continue;
            }

            $data[] = [
                'santri_id'   => $row['santri_id'],
                'potongan_id' => $row['potongan_id'],
                'keterangan'  => $row['keterangan'] ?? null,
                'status'      => isset($row['status']) ? (bool) $row['status'] : true,
            ];
        }

        if (!empty($this->errors)) {
            throw new ValidationException(null, 'Terdapat error pada file Excel', $this->errors);
        }

        return $data;
    }
}
