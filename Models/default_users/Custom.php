<?php

namespace App\Models\CustomModels;

use DB;

class default_users extends \App\Models\BasicModels\default_users
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
        // SELECT * FROM default_users
    }

    protected $hidden = ["password"];

    public $fileColumns = [
        /*file_column*/
    ];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function presensi_absensi() :\HasMany
    {
        return $this->HasMany('App\Models\BasicModels\presensi_absensi', 'default_user_id', 'id');
    }

    public function onRetrieved($model)
    {
        $req = app()->request;
        if ($req->from == 'role_access') {
            $model->is_superadmin =
                m_role_access::join('m_role as r', 'r.id', 'm_role_access.m_role_id')
                    ->where('m_role_access.user_id', $model->id)
                    ->pluck('is_superadmin')->first();
            if ($req->detail)
                $model->detail = m_role_access::join('m_role as r', 'r.id', 'm_role_access.m_role_id')
                    ->select("r.*")
                    ->where('m_role_access.user_id', $model->id)
                    ->get();
        }
        if ($req->withKary) {
            $kary = m_kary::find($model->m_kary_id);
            $model->nama_lengkap = @$kary->nama_lengkap;
            $model->m_kary_id = @$kary->id;
            $model->kode = @$kary->kode;
            $model->nik = @$kary->nik;
            $model->divisi = @m_divisi::find($kary->m_divisi_id)->nama;
            $model->dept = @m_dept::find($kary->m_dept_id)->nama;
        }
        $model->atasan = m_kary::where('id', @$model['m_kary.atasan_id'] ?? 0)->pluck('nama_lengkap')->first();

        if (app()->request->header('Source') === 'mobile') {
            $data = \DB::select("select public.employee_attendance(?,?)", [Date('Y-m-d'), $model['m_kary_id'] ?? 0]);
            $data = json_decode($data[0]->employee_attendance);
            $model['m_kary.cuti_sisa_panjang'] = $data->sisa_cuti_reguler ?? 0;
            $model['m_kary.cuti_sisa_reguler'] = $data->sisa_cuti_masa_kerja ?? 0;
            $model['m_kary.cuti_sisa_p24'] = $data->sisa_cuti_p24 ?? 0;
            $model['info_cuti'] = $data;

        }
    }

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $check = $model->where("username", req("username"))->exists();
        if ($check && req("username")) {
            return ["errors" => ["Username sudah dipakai"]];
        }

        $check = $model->where("email", req("email"))->exists();
        if ($check && req("email")) {
            return ["errors" => ["Email sudah dipakai"]];
        }

        if (req("password") && req("password") != req("password_confirm")) {
            return ["errors" => ["Konfirmasi password salah"]];
        }


        $hasher = app()->make("hash");
        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "password" => $hasher->make(req("password")),
            ]),
        ];
    }

    public function updateAfter( $model, $arrayData, $metaData, $id=null )
    {
        return [
            "model" => $model,
            "data" => array_merge($arrayData, [
                "is_sync" => false,
            ]),
        ];
    }
    

    public function transformRowData(array $row)
    {
        return array_merge($row, [
            'profil_image' => url('') . '/' . $row['profil_image'] ?? null
        ]);
    }


    public function custom_update_foto_profil($req)
    {
        $validator = \Validator::make($req->all(), [
            "profil_image" => "required",
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return $this->helper->responseValidate($validator);
        }

        DB::beginTransaction();
        try {
            if ($req->hasFile("profil_image")) {
                $file = $req->file("profil_image");
                $fileName =
                    auth()->user()->username .
                    ":::" .
                    md5(time()) .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/profile"), $fileName);
            } else {
                trigger_error("IMAGE NOT VALID");
            }

            $this->where("id", $req->id)->update([
                "profil_image" => "uploads/profile/$fileName",
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse(
                "Update Foto Profil gagal, coba kembali nanti",
                400
            );
        }
        return $this->helper->customResponse(
            "Update Foto Profil berhasil",
            200,
            $this->where("id", $req->id)->first()
        );
    }

    public function public_generate()
    {
        $kary = m_kary::whereRaw("m_kary.id not in(select u.m_kary_id from default_users u where u.m_kary_id is not null)")->limit(200)->get();

        \DB::beginTransaction();
        try {
            $hasher = app()->make("hash");
            foreach ($kary as $k) {
                if ($k->kode) {
                    $this->create([
                        'username' => $k->kode,
                        'name' => $k->nama_lengkap,
                        'email' => $k->kode . "@hris.com",
                        'password' => $hasher->make($k->kode),
                        'm_kary_id' => $k->id
                    ]);

                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            return response(['m' => $e->getMessage() . ' - ' . $e->getLine()], 400);
        }

        return response(['m' => $kary]);
    }

    public function custom_reset_password($req)
    {
        if (req("password") && !req("password_confirm")) {
            return ["errors" => "Masukkan password Konfirmasi"];
        }

        if (req("password") && req("password") != req("password_confirm")) {
            return ["errors" => "Konfirmasi password salah"];
        }

        $hasher = app()->make("hash");

        if ($req->email && $req->username) {
            $this->where('id', auth()->user()->id)->update([
                'username' => $req->username,
                'email' => $req->email,
            ]);
        }

        if (req("password")) {
            $this->where('id', auth()->user()->id)->update([
                'password' => $hasher->make(req("password"))
            ]);
        }

        return response([
            'message' => 'Update password berhasil'
        ]);

    }


    public function scopePic($model)
    {

        $kary_id = default_users::where('id', auth()->user()->id)->pluck('m_kary_id')->first();
        return $model->whereRaw("default_users.id = ? or default_users.m_kary_id in (select k.id from default_users u join m_kary k on k.id = u.m_kary_id where k.atasan_id = ?)", [auth()->user()->id ?? 0, $kary_id]);
    }

    // public function public_phpinfo(){
    //     return phpinfo();
    // }


    public function custom_update($req)
    {
        $hasher = app()->make("hash");
        $id = $req->id;
        if (!$id)
            return $this->helper->customResponse('ID diperlukan', 422);

        $validator = \Validator::make($req->all(), [
            "name" => "required",
            "username" => "required",
            "email" => "required",
        ]);
        if ($validator->fails())
            return $this->helper->responseValidate($validator);

        try {
            \DB::beginTransaction();
            if ($req->password) {
                $data = [
                    'name' => $req->name,
                    'username' => $req->username,
                    'email' => $req->email,
                    'm_kary_id' => $req->m_kary_id,
                    // 'm_company_id' => $req->m_company_id,
                    'user_type' => $req->user_type,
                    'note' => $req->note,
                    'is_active' => $req->is_active,
                    'is_hc' => $req->is_hc,
                    'password' => $hasher->make($req->password),
                    'm_os_id' => $req->m_os_id,
                ];
            } else {
                $data = [
                    'name' => $req->name,
                    'username' => $req->username,
                    'email' => $req->email,
                    'm_kary_id' => $req->m_kary_id,
                    // 'm_company_id' => $req->m_company_id,
                    'user_type' => $req->user_type,
                    'note' => $req->note,
                    'is_active' => $req->is_active,
                    'is_hc' => $req->is_hc,
                    'm_os_id' => $req->m_os_id,

                ];
            }
            $this->where('id', $id)->update($data);

            default_users_respo::where('default_users_id', $id)->delete();

            foreach ($req->default_users_respo as $d) {
                default_users_respo::where('default_users_id', $id)->create([
                    'default_users_id' => $id,
                    'seq' => $d['seq'],
                    'm_respo_id' => $d['m_respo_id'],
                    'is_primary' => $d['is_primary'],
                    'is_active' => $d['is_active']
                ]);
            }
            \DB::commit();
            return $this->helper->customResponse('Update berhasil');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->helper->responseCatch($e);
        }
    }

    public function custom_generate_user()
    {
        $batchSize = 50;
        $getKary = m_kary::whereRaw("m_kary.id not in(select u.m_kary_id from default_users u where u.m_kary_id is not null)")
            ->limit($batchSize)
            ->get();
        if ($getKary->count() == 0)
            return response(['message' => 'Tidak ada karyawan yang bisa dibuatkan user'], 400);

        $hasher = app()->make("hash");
        $bulkData = [];
        foreach ($getKary as $k) {
            if ($k->kode) {
                $bulkData[] = [
                    'username' => null,
                    'name' => null,
                    'email' => null,
                    'password' => null,
                    'm_kary_id' => $k->id,
                    'created_at' => null,
                    'updated_at' => null,
                ];
            }
        }

        \DB::beginTransaction();
        try {
            $this->upsert(
                $bulkData,
                ['m_kary_id'],
                ['username', 'name', 'email', 'password', 'updated_at']
            );
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            return response(['message' => $e->getMessage() . ' - ' . $e->getLine()], 400);
        }

        return response(['message' => $getKary]);
    }

    public function scopehigherlevel($model)
    {
        $m_kary = m_kary::whereHas('default_users', function($q){
            $q->where('id', auth()->user()->id);
        })->first();

        $level = m_level_posisi::whereHas('m_level_posisi_d', function($q) use ($m_kary){
            $q->where('m_posisi_id', $m_kary->m_posisi_id);
        })->first();

        $maxLevel = m_level_posisi::max('sequence');
        // dd($maxLevel);
        // dd($level->sequence);
        if($level->sequence < $maxLevel)
        {
            return $model->join('m_level_posisi_d as ld', 'ld.m_posisi_id', 'm_kary.m_posisi_id')
                ->join('m_level_posisi as l', 'l.id', 'ld.m_level_posisi_id')
                ->where('l.sequence', '>', $level->sequence);
        }else{
            return $model;
        }

        // return $model->whereHas('m_posisi.m_level_posisi_d.m_level_posisi', function($q) use ($level) {
        //     $q->where('sequence', '>', $level->sequence);
        // });
    }
}
