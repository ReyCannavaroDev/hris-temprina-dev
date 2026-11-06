<?php

namespace App\Models\CustomModels;
use App\Cores\Approval;
use App\Cores\Helper;
use App\Cores\Respo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class t_bonus extends \App\Models\BasicModels\t_bonus
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = new Helper();

    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        if(!isset($arrayData['status'])){
            $status = 'DRAFT';
        }else{
            $status = $arrayData['status'];
        }

        $newArrayData  = array_merge( $arrayData,[
            "nomor" => $this->helper->generateNomor("KODE BONUS"),
            "status" => $status,
            "creator_id" => auth()->user()->id
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    public function custom_send_approval()
    {
        $target_id = req("target_id");
        $user_target = $target_id ? default_users::where('m_kary_id', $target_id)->first()?->id : null;

        $app = $this->createAppTicket(req("id"), $user_target);
        if (!$app) {
            return $this->helper->customResponse(
                "Terjadi kesalahan, coba kembali nanti",
                400
            );
        }

        if (app()->request->header("Source") != "mobile") {
            $data = $this->find(req("id"));
            if ($data) {
                $data->update([
                    "status" => "IN APPROVAL",
                ]);
            }
        }

        return $this->helper->customResponse(
            "Permintaan approval berhasil dibuat"
        );
    }

    private function createAppTicket($id, $target_id = null)
    {
        $tempId = $id;
        $trx = \DB::table('t_request_pelatihan')->find($tempId);
        $conf = [
            "app_name" => "APPROVAL PENGAJUAN PELATIHAN",
            "trx_id" => $trx->id,
            "trx_table" => $this->getTable(),
            "trx_name" => "Pengajuan Pelatihan",
            "form_name" => "t_request_pelatihan",
            "trx_nomor" => $trx->kode,
            "trx_date" => Date("Y-m-d"),
            "trx_creator_id" => $trx->creator_id,
            "target_id" => $target_id,
        ];

        $app = $this->helper->approvalCreateTicket($conf);
        if ($app) {
            return true;
        } else {
            return false;
        }
    }

    public function custom_progress($req)
    {
        // Start a database transaction
        \DB::beginTransaction();

        try {
            $conf = [
                "app_id" => $req->id,
                "app_type" => $req->type, 
                "app_note" => $req->note,
            ];

            $app = $this->helper->approvalProgress($conf, true);
            if ($app->status) {
                $data = $this->find($app->trx_id);
                if ($app->finish) {
                    $data->update([
                        "status" => $req->type
                    ]);
                   
                } else {
                    $data->update([
                        "status" => "IN APPROVAL",
                    ]);
                }
            }

            \DB::commit();

            return $this->helper->customResponse("Proses approval berhasil");
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_posted($req)
    {

        \DB::beginTransaction();
        try{
            $data = $this->find($req->id);
            $data->status = 'POSTED';
            $data->save();

         \DB::commit();
         return $this->helper->customResponse("Data berhasil diposting");
        }catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_app_detail($req)
    {
        $id = $req->id ?? 1;
        $data = $this->approval->detail($id);
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_app_log($req)
    {
        $conf = [
            "trx_id" => $req->id ?? 0,
            "trx_table" => $this->getTable(),
        ];
        $data = $this->helper->approvalLog($conf);
        return response($data);
    }

    public function custom_approveHC()
    {
        $req = app()->request;

        try {
            \DB::beginTransaction();

            $data = $this->find($req->id);
            // dd($data);

            if (!$data) {
                return $this->helper->customResponse("Data tidak ditemukan", 404);
            }

            $data->update([
                'status' => 'APPROVED'
            ]);

            $log = $this->logHc($data->id);

            \DB::commit();
            return $this->helper->customResponse("Approval berhasil", 200);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error("Error Approve HC: " . $e->getMessage());

            return $this->helper->customResponse(
                "Terjadi kesalahan sistem: " . $e->getMessage(), 
                500
            );
        }
    }

    public function logHc($trxId)
    {
        $prevLog = generate_approval_log::where('trx_id', $trxId)->where('action_type', 'HALF APPROVED');
        if($check = $prevLog->exists()){
            $prev = $prevLog->first();
            $log_insert = generate_approval_log::create([
                'nomor'                     => $prev->nomor,
                'generate_approval_id'      => $prev->id,
                'generate_approval_det_id'  => null,
                'trx_id'                    => $prev->trx_id,
                'trx_table'                 => $prev->trx_table,
                'trx_name'                  => $prev->trx_name,
                'trx_nomor'                 => $prev->trx_nomor,
                'trx_date'                  => $prev->trx_date,
                'form_name'                 => $prev->form_name,
                'trx_creator_id'            => $prev->trx_creator_id,
                'action_type'               => 'APPROVED',
                'action_user_id'            => auth()->user()->id,
                'creator_id'                => auth()->user()->id,
                'action_at'                 => Carbon::now(),
                'action_note'               => 'APPROVED BY HC'
            ]); 
        }
    }

    public function scoperespo($model)
    {
        $m_subcomp_id = request('m_subcomp_id') ?? null;
        $m_branch_id = request('m_branch_id') ?? null;

        if ($m_subcomp_id === 'null') $m_subcomp_id = null;
        if ($m_branch_id === 'null')  $m_branch_id  = null;

        return $model->when($m_subcomp_id, function($q) use ($m_subcomp_id){
            $q->where('t_request_pelatihan.m_subcomp_id', $m_subcomp_id);
        })->when($m_branch_id, function($q) use ($m_branch_id){
            $q->where('t_request_pelatihan.m_branch_id', $m_branch_id);
        });
    }

    public function custom_importBonus($req)
    {
        try {
            if (!$req->hasFile('csv')) {
                return response()->json(['error' => 'File tidak ditemukan'], 422);
            }

            $file = $req->file('csv');
            $ext = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
                return response()->json(['error' => 'Format file harus CSV atau Excel (xls/xlsx)'], 422);
            }

            $data = [];
            if ($ext === 'csv') {
                $path = $file->getRealPath();
                $data = array_map('str_getcsv', file($path));
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $data = $spreadsheet->getActiveSheet()->toArray();
            }
            //dd($data);

            $header = array_map('trim', $data[0]);
            unset($data[0]);


            DB::beginTransaction();

            // Ambil ID Jenis Bonus (Sesuaikan 'code' dengan master data Anda, misal: JBON01)
            // $jenis_bonus = m_general::where('group', 'JENIS BONUS')->where('code', 'JBON01')->first();
            // if (!$jenis_bonus) throw new \Exception("Master Jenis Bonus (JBON01) tidak ditemukan.");

            foreach ($data as $row) {
                if (empty($row[0])) continue; // Skip baris kosong

                $row = array_map('trim', $row);
                if (count($header) !== count($row)) continue;

                $rowData = array_combine($header, $row);
                $validator = Validator::make($rowData, [
                    'NIK' => 'required',
                    'NILAI' => 'required|numeric',
                    'TANGGAL_AWAL' => 'required',
                    'TANGGAL_AKHIR' => 'required',
                ]);
                // dd($rowData);

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
                    return response()->json(['error' => "NIK {$rowData['NIK']} tidak ditemukan"], 422);
                }

                // Helper Konversi Tanggal (Handle format Excel serial number)
                $convertDate = function($val) {
                    if (is_numeric($val)) {
                        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                    }
                    return date('Y-m-d', strtotime($val));
                };

                $dateFrom = $convertDate($rowData['TANGGAL_AWAL']);
                $dateTo = $convertDate($rowData['TANGGAL_AKHIR']);

                t_bonus::insert([
                    'nomor' => $this->helper->generateNomor('KODE BONUS'),
                    'm_kary_id' => $kary->id,
                    'jenis_bonus_id' => 0,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'nilai' => $rowData['NILAI'],
                    'keterangan' => $rowData['KETERANGAN'] ?? null,
                    'no_doc' => $rowData['NO_DOKUMEN'] ?? null,
                    'status' => 'POSTED', // Otomatis Posted agar masuk perhitungan gaji
                    'm_comp_id' => $kary->m_comp_id ?? 1,
                    'm_subcomp_id' => $kary->m_subcomp_id,
                    'm_branch_id' => $kary->m_branch_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => 'Import Bonus berhasil'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Kesalahan: ' . $e->getMessage()], 500);
        }
    }
}