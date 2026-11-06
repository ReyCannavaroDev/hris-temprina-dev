<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class t_potongan extends \App\Models\BasicModels\t_potongan
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = ['doc'];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[
            'nomor' => $this->helper->generateNomor('KODE POTONGAN'),
            'percentage' => $arrayData['percentage'] ?? 100
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[
            'percentage' => $arrayData['percentage'] ?? 100
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }



    public function custom_inactive($request)
    {
        $data = t_potongan::find($request->id);
        
        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $update = $data->update([
                'status' => "INACTIVE"
            ]);

            if ($update) {
                return response()->json(['message' => 'Data berhasil dinonaktifkan.']);
            } else {
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }

    }

    public function custom_postData($request)
    {
        $data = t_potongan::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {
            $update = $data->update([
                'status' => "POSTED"
            ]);

            if ($update) {
                return response()->json(['message' => 'Data berhasil diposting.']);
            } else {
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            // Handle exception, log error messages, etc.
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function custom_importPotongan($req)
    {
        try {
            if (!$req->hasFile('csv')) { // Sesuaikan name input dari 'csv' ke 'file' agar umum
                return response()->json(['error' => 'File tidak ditemukan'], 422);
            }

            $file = $req->file('csv');
            $ext = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
                return response()->json(['error' => 'Format file harus CSV atau Excel (xls/xlsx)'], 422);
            }

            $data = [];

            // --- PROSES PEMBACAAN FILE ---
            if ($ext === 'csv') {
                $path = $file->getRealPath();
                $data = array_map('str_getcsv', file($path));
            } else {
                // Menggunakan PhpSpreadsheet (biasanya sudah include jika pakai Laravel Excel)
                // Jika belum ada, jalankan: composer require phpoffice/phpspreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $data = $spreadsheet->getActiveSheet()->toArray();
            }
            
            //dd($data);

            $header = array_map('trim', $data[0]);
            unset($data[0]);

            DB::beginTransaction();

            // Ambil JPTG di luar loop agar tidak query berulang kali (N+1 Problem)
            $jptg = m_general::where('group', 'JENIS POTONGAN')->where('code', 'JPTG03')->first();
            if (!$jptg) throw new \Exception("Master Jenis Potongan (JPTG03) tidak ditemukan.");

            foreach ($data as $row) {
                // Skip jika baris kosong (biasanya ada di file excel)
                if (empty($row[0])) continue;

                $row = array_map('trim', $row);
                
                // Proteksi jika jumlah kolom tidak sesuai header
                if (count($header) !== count($row)) continue;

                $rowData = array_combine($header, $row);

                $validator = Validator::make($rowData, [
                    'NIK' => 'required',
                    'NILAI' => 'required|numeric',
                    'TANGGAL' => 'required', // Tanggal di Excel kadang terbaca sebagai serial number
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Data tidak valid di NIK: ' . ($rowData['NIK'] ?? 'Unknown'),
                        'detail' => $validator->errors(),
                    ], 422);
                }

                $kary = m_kary::where('nik', $rowData['NIK'])->first();
                if (!$kary) {
                    DB::rollBack();
                    return response()->json(['error' => "Karyawan dengan NIK {$rowData['NIK']} tidak ditemukan"], 422);
                }

                // Konversi Tanggal jika dari Excel (Excel sering mengirim format 45330 alih-alih 2024-02-18)
                $tanggal = $rowData['TANGGAL'];
                if (is_numeric($tanggal)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
                }

                t_potongan::insert([
                    'nomor' => $this->helper->generateNomor('KODE POTONGAN'),
                    'jenis_potongan_id' => $jptg->id,
                    'm_kary_id' => $kary->id,
                    'nilai' => $rowData['NILAI'],
                    'keterangan' => $rowData['KETERANGAN'] ?? null,
                    'date_from' => $tanggal,
                    'date_to' => $tanggal,
                    'percentage' => 100,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Import berhasil'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function scopebranch($model)
    {
      return $model->join("m_branch as b", "b.id", "m_kary.m_branch_id")
      ->addSelect("b.name as m_branch.name");
    }
    
}