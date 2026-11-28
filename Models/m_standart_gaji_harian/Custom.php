<?php

namespace App\Models\CustomModels;

class m_standart_gaji_harian extends \App\Models\BasicModels\m_standart_gaji_harian
{    
    private $helper;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    // Paksa hilangkan filter otomatis dari core system agar data tampil
    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope('bypass_security', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $query = $builder->getQuery();
            if (isset($query->wheres)) {
                foreach ($query->wheres as $key => $where) {
                    $targetStr = isset($where['column']) ? $where['column'] : (isset($where['sql']) ? $where['sql'] : '');
                    if (strpos($targetStr, 'm_kary.m_subcomp_id') !== false || strpos($targetStr, 'm_kary.m_branch_id') !== false) {
                        unset($query->wheres[$key]);
                    }
                }
                $query->wheres = array_values($query->wheres);
            }
        });
    }

    public $fillable = [
        "kode", "m_kary_id", "start_date", "m_zona_id", "m_posisi_id", 
        "grading_id", "gaji_pokok", "check_late", "desc", "is_active", 
        "creator_id", "last_editor_id", "created_at", "updated_at", 
        "total_jam_kerja", "status", "m_branch_id"
    ];

    public $createable = [
        "kode", "m_kary_id", "start_date", "m_zona_id", "m_posisi_id", 
        "grading_id", "gaji_pokok", "check_late", "desc", "is_active", 
        "creator_id", "last_editor_id", "created_at", "updated_at", 
        "total_jam_kerja", "status", "m_branch_id"
    ];

    public $updateable = [
        "kode", "m_kary_id", "start_date", "m_zona_id", "m_posisi_id", 
        "grading_id", "gaji_pokok", "check_late", "desc", "is_active", 
        "creator_id", "last_editor_id", "created_at", "updated_at", 
        "total_jam_kerja", "status", "m_branch_id"
    ];

    public $searchable = [
        "kode", "m_kary_id", "start_date", "m_zona_id", "m_posisi_id", 
        "grading_id", "gaji_pokok", "check_late", "desc", "is_active", 
        "creator_id", "last_editor_id", "created_at", "updated_at", 
        "total_jam_kerja", "status", "m_branch_id"
    ];

    // Join tabel m_kary, m_company_outsourcing, m_branch, m_posisi agar query relasi lengkap
    public $joins = [
        "m_kary.id=m_standart_gaji_harian.m_kary_id",
        "m_company_outsourcing.id=m_kary.m_company_outsourcing_id",
        "m_branch.id=m_standart_gaji_harian.m_branch_id",
        "m_posisi.id=m_standart_gaji_harian.m_posisi_id"
    ];

    public $fileColumns = [
        /*file_column*/
    ];
    
    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $chosenKaryId = req('m_kary_id');
        $rawInput = json_decode(app()->request->getContent(), true);
        if (!empty($rawInput['m_kary_id'])) {
            $chosenKaryId = $rawInput['m_kary_id'];
        }
        if (!empty($chosenKaryId)) {
            $arrayData['m_kary_id'] = $chosenKaryId;
            $branchId = \DB::table('m_kary')->where('id', $chosenKaryId)->value('m_branch_id');
            if ($branchId) {
                $arrayData['m_branch_id'] = $branchId;
            }
        }

        if (empty($arrayData['status'])) {
            $arrayData['status'] = 'DRAFT';
        }

        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "kode" => app()->request->kode ?? $this->helper->generateNomor("KODE STANDART GAJI"),
            ]),
        ];
    }

    public function updateBefore($model, $arrayData, $metaData, $id = null)
    {
        // Guard penguncian data: Data POSTED atau IN_APPROVAL tidak boleh diubah
        if ($model && in_array(strtoupper(trim($model->status ?? '')), ['POSTED', 'IN_APPROVAL', 'IN APPROVAL'])) {
            throw new \Exception("Data standar gaji dengan status " . $model->status . " sudah terkunci dan tidak dapat diubah.");
        }

        $chosenKaryId = req('m_kary_id');
        $rawInput = json_decode(app()->request->getContent(), true);
        if (!empty($rawInput['m_kary_id'])) {
            $chosenKaryId = $rawInput['m_kary_id'];
        }
        if (!empty($chosenKaryId)) {
            $arrayData['m_kary_id'] = $chosenKaryId;
            $branchId = \DB::table('m_kary')->where('id', $chosenKaryId)->value('m_branch_id');
            if ($branchId) {
                $arrayData['m_branch_id'] = $branchId;
            }
        }

        return [
            "model" => $model,
            "data" => $arrayData,
        ];
    }

    public function deleteBefore($model, $id = null)
    {
        // Guard penguncian data: Data POSTED atau IN_APPROVAL tidak boleh dihapus
        if ($model && in_array(strtoupper(trim($model->status ?? '')), ['POSTED', 'IN_APPROVAL', 'IN APPROVAL'])) {
            throw new \Exception("Data standar gaji dengan status " . $model->status . " sudah terkunci dan tidak dapat dihapus.");
        }
        return $model;
    }

    public function transformRowData( array $row )
    {
        $newData = [
            "gaji_pokok_formatted" => "Rp " . number_format($row['gaji_pokok'], 0, ',', '.'),
            "status" => $row['status'] ?? 'DRAFT',
        ];

        return array_merge( $row, $newData );
    }

    // Endpoint untuk mengajukan approval standar gaji harian OS ke HC Cabang
    public function custom_send_approval()
    {
        $id = req('id');
        $trx = $this->find($id);
        if (!$trx) {
            return $this->helper->customResponse("Data standar gaji tidak ditemukan", 404);
        }

        $currentStatus = strtoupper(trim($trx->status ?? ''));
        if (!in_array($currentStatus, ['DRAFT', 'REJECTED', ''])) {
            return $this->helper->customResponse("Hanya data berstatus DRAFT atau REJECTED yang dapat diajukan approval.", 400);
        }

        $kary = \DB::table('m_kary')->where('id', $trx->m_kary_id)->first();
        $branchId = $trx->m_branch_id ?: ($kary->m_branch_id ?? auth()->user()->m_branch_id ?? null);

        $conf = [
            "app_name"        => "APPROVAL STANDAR GAJI HARIAN OS",
            "trx_id"          => $trx->id,
            "trx_table"       => $this->getTable(),
            "trx_name"        => "Pengajuan Standar Gaji Harian OS (" . ($kary->nama_lengkap ?? $trx->kode) . ")",
            "form_name"       => "m_standart_gaji_harian_os",
            "trx_nomor"       => $trx->kode,
            "trx_date"        => date('Y-m-d'),
            "trx_creator_id"  => $trx->creator_id ?: auth()->user()->id,
            "m_branch_id"     => $branchId,
        ];

        try {
            $app = $this->helper->approvalCreateTicket($conf);
            if ($app) {
                $trx->update([
                    'status' => 'IN_APPROVAL',
                    'm_branch_id' => $branchId
                ]);
                return $this->helper->customResponse("Pengajuan approval ke HC Cabang berhasil dikirim", 200, $trx);
            } else {
                return $this->helper->customResponse("Gagal membuat tiket approval, pastikan konfigurasi approval cabang sudah tersedia.", 400);
            }
        } catch (\Throwable $e) {
            return $this->helper->customResponse("Terjadi kesalahan saat pengajuan approval: " . $e->getMessage(), 500);
        }
    }

    public function scopeharian($model)
    {
        $statusHarian = \DB::table('m_general')
            ->where('group', 'STATUS KARYAWAN')
            ->where(function ($q) {
                $q->where('value', 'HARIAN')
                  ->orWhereRaw("UPPER(value) LIKE '%HARIAN%'");
            })
            ->pluck('id')
            ->toArray();

        $statusOs = \DB::table('m_general')
            ->where('group', 'STATUS KARYAWAN OUTSOURCE')
            ->orWhereRaw("UPPER(value) LIKE '%OUTSOURCE%'")
            ->pluck('id')
            ->toArray();

        return $model
            ->whereNull('m_kary.m_company_outsourcing_id')
            ->when(!empty($statusOs), function ($q) use ($statusOs) {
                $q->whereNotIn('m_kary.status_kary_id', $statusOs);
            })
            ->when(!empty($statusHarian), function ($q) use ($statusHarian) {
                $q->whereIn('m_kary.status_kary_id', $statusHarian);
            });
    }

    public function scopeos($model)
    {
        $statusOs = \DB::table('m_general')
            ->where(function ($q) {
                $q->where('group', 'STATUS KARYAWAN OUTSOURCE')
                  ->orWhereRaw("UPPER(value) LIKE '%OUTSOURCE%'");
            })
            ->pluck('id')
            ->toArray();

        $authUser = auth()->user();
        $osId = request('m_os_id') ?: ($authUser->m_os_id ?? null);
        if (!$osId && $authUser && !empty($authUser->m_kary_id)) {
            $osId = \DB::table('m_kary')->where('id', $authUser->m_kary_id)->value('m_company_outsourcing_id');
        }

        return $model
            ->where(function ($q) use ($statusOs) {
                $q->whereNotNull('m_kary.m_company_outsourcing_id');
                if (!empty($statusOs)) {
                    $q->orWhereIn('m_kary.status_kary_id', $statusOs);
                }
            })
            ->when(!empty($osId), function ($q) use ($osId) {
                $q->where('m_kary.m_company_outsourcing_id', $osId);
            });
    }
}

