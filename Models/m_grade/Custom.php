<?php

namespace App\Models\CustomModels;

class m_grade extends \App\Models\BasicModels\m_grade
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function public_download_template($req = null)
    {
        $currentYear = (int) date('Y');
        $rows = [
            [
                'm_zona_id'  => 1,
                'tahun'      => $currentYear,
                'code'       => 'A',
                'keterangan' => 'Grade A',
                'score'      => 90,
                'min'        => 5000000,
                'max'        => 7000000,
            ],
            [
                'm_zona_id'  => 1,
                'tahun'      => $currentYear,
                'code'       => 'B',
                'keterangan' => 'Grade B',
                'score'      => 80,
                'min'        => 4000000,
                'max'        => 5000000,
            ],
        ];

        $fileName = "Format_Import_Master_Grading.xlsx";

        if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            return \Maatwebsite\Excel\Facades\Excel::download(new class (collect($rows)) implements 
                \Maatwebsite\Excel\Concerns\FromCollection, 
                \Maatwebsite\Excel\Concerns\WithHeadings, 
                \Maatwebsite\Excel\Concerns\ShouldAutoSize {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array {
                    return ["Zona ID", "Tahun", "Kode", "Keterangan", "Score", "Min", "Max"];
                }
            }, $fileName);
        }

        // Fallback CSV jika Excel facade tidak tersedia
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Format_Import_Master_Grading.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        $columns = ["Zona ID", "Tahun", "Kode", "Keterangan", "Score", "Min", "Max"];
        $callback = function() use ($rows, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function custom_download_template($req = null)
    {
        return $this->public_download_template($req);
    }

    public function custom_upload_grade_excel($req)
    {
        $validator = \Validator::make($req->all(), [
            "file" => "required|mimes:xls,xlsx,csv"
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Wrong format'], 401);
        }

        $collection = \Excel::toCollection(null, $req->file('file'));

        foreach ($collection[0] as $index => $row) {
            if ($index === 0)
                continue;

            $row = $row->toArray();

            if (count($row) < 6)
                continue;

            // Deteksi format: 7 kolom (dengan tahun) atau 6 kolom (format lama)
            if (count($row) >= 7 && (is_numeric($row[1]) && strlen((string)$row[1]) === 4)) {
                $zonaId     = !empty($row[0]) ? (int)$row[0] : null;
                $tahun      = (int)$row[1];
                $code       = trim((string)$row[2]);
                $keterangan = $row[3] ?? null;
                $score      = !empty($row[4]) ? (int)$row[4] : 0;
                $min        = !empty($row[5]) ? (float)$row[5] : 0;
                $max        = !empty($row[6]) ? (float)$row[6] : 0;
            } else {
                $zonaId     = !empty($row[0]) ? (int)$row[0] : null;
                $tahun      = (int) date('Y');
                $code       = trim((string)$row[1]);
                $keterangan = $row[2] ?? null;
                $score      = !empty($row[3]) ? (int)$row[3] : 0;
                $min        = !empty($row[4]) ? (float)$row[4] : 0;
                $max        = !empty($row[5]) ? (float)$row[5] : 0;
            }

            if (empty($code))
                continue;

            m_grade::updateOrCreate(
                [
                    'code'      => $code,
                    'tahun'     => $tahun,
                    'm_zona_id' => $zonaId,
                ],
                [
                    'keterangan' => $keterangan,
                    'score'      => $score,
                    'min'        => $min,
                    'max'        => $max,
                    'is_active'  => true,
                ]
            );
        }
        return response()->json(['success' => 'File imported successfully'], 200);
    }
}
