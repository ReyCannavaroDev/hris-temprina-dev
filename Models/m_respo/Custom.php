<?php

namespace App\Models\CustomModels;

class m_respo extends \App\Models\BasicModels\m_respo
{   
    private $helper;
    private $generateCode;

    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
        $this->generateCode = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function default_users_respo(){
        return $this->hasMany('App\Models\BasicModels\default_users_respo', 'm_respo_id', 'id');
    }

    public function transformRowData( array $row )
    {
        $role = m_respo_d::where('m_respo_id', $row['id'])
        ->whereHas('m_role')
        ->get()
        ->pluck('m_role.name')
        ->implode(', ');

        $row['roles'] = $role;

        return $row;
    }
    


    public function custom_change($req)
    {
        if(!$req->m_respo_id) return $this->helper->customResponse('Respo ID Diperlukan!', 422);

        $data = $this->where('is_active', true)->where('id',$req->m_respo_id)->first();
        if(!$data) return $this->helper->customResponse('Data respo ID tersebut tidak ditemukan', 404);

        $check_exist_respo = default_users_respo::where('m_respo_id', $data->id)->where('default_users_id', auth()->user()->id)->first();
        
        if($check_exist_respo) {
            default_users_respo::where('default_users_id', auth()->user()->id)->update([
                'is_primary' => false
            ]);
            default_users_respo::where('default_users_id', auth()->user()->id)->where('m_respo_id',$data->id)->update([
                'is_primary' => true
            ]);
        }else{
            $last_seq = default_users_respo::where('default_users_id', auth()->user()->id)->orderBy('seq','desc')->pluck('seq')->first() ?? 0;
            default_users_respo::create([
                'default_users_id' => auth()->user()->id,
                'seq' =>  (int)$last_seq+1,
                'm_respo_id' => $data->id,
                'is_primary' => true,
                'is_active' => true
            ]);
        }

        return $this->helper->customResponse('Responsibility berhasil diganti', 200, $this->getRespoWithNames($data->id), true);
    }

    public function custom_active_respo($req)
    {
        $user_id = auth()->user()->id;
        $respo_active = default_users_respo::where('default_users_id', $user_id)->where('is_primary', true)->first();

        $data = $this->getRespoWithNames(@$respo_active->m_respo_id);
        return $this->helper->customResponse('OK', 200, $data, true);
    }

    // leftJoin agar respo tanpa comp/subcomp/branch (mis. HOLDING) tetap terbaca
    private function getRespoWithNames($respoId)
    {
        return $this
            ->selectRaw("m_respo.*,m_comp.name \"m_comp.name\",m_subcomp.name \"m_subcomp.name\", m_branch.name \"m_branch.name\"")
            ->leftJoin('m_comp','m_comp.id','m_respo.m_comp_id')
            ->leftJoin('m_subcomp','m_subcomp.id','m_respo.m_subcomp_id')
            ->leftJoin('m_branch','m_branch.id','m_respo.m_branch_id')
            ->where('m_respo.is_active', true)->where('m_respo.id', $respoId)->first();
    }

    public function custom_main_respo()
    {
        $user_id = auth()->user()->id;
        $respo = default_users_respo::where('default_users_id', $user_id)
                    ->where('is_primary', true)
                    ->first() ?? null;
        return $respo?->m_respo;
    }

    public function scopeByUserRespo($model)
    {
        $model->whereRaw("
            m_respo.id in(select d.m_respo_id from default_users_respo d where d.default_users_id = ? and d.is_active = true)
            and m_respo.is_active = true
        ", [ auth()->user()->id ]);

        // $model->join('m_respo_d', 'm_respo.id', '=', 'm_respo_d.m_respo_id')
        // ->whereRaw("
        //     m_respo.id in (
        //         select d.m_respo_id 
        //         from default_users_respo d 
        //         where d.default_users_id = ? 
        //         and d.is_active = true
        //     )
        //     and m_respo.is_active = true
        // ", [auth()->user()->id])
        // ->select(
        //     'm_respo.*', 
        //     'm_respo_d.m_role_id'
        // );


        // $model::whereHas('m_respo_d')
        // ->whereHas('default_users_respo', function($q){
        //     $q->where('default_users_id', auth()->user()->id);
        // })
        // ->where('is_active', true);
        // ->get()
        // ->map(function($respo){
        //     return [
        //         "id" => $respo->id,
        //         "name" => $respo->name,
        //         "m_comp.name" => $respo->m_comp?->name ?? null,
        //         "m_subcomp.name" => $respo->m_subcomp?->name ?? null,
        //         "m_branch" => $respo->m_branch?->name ?? null,
        //         "role" => $respo->m_respo_d,
        //     ];
        // });

        // default_users_respo::where('default_user_id', auth()->user()->id)
        // ->whereHas('m_respo', function($q){
        //     $q->where('is_active', true)
        //     ->whereHas('m_respo_d');
        // })
        // ->get()
        // ->map(function($respo){
        //     return [
        //         "id" => $respo->m_respo->id,
        //         "name" => $respo->m_respo->name
        //     ]
        // });
    }


      public function public_checkAccessForm()
    {
        $user_id = @auth()->user()->id ?? 14;

        $user_respo = getBasic('default_users_respo');
        $respo_active = $user_respo->where('default_users_id', $user_id)->where('is_primary', true)->first();
        
        if($modul == 'dashboard' || $modul == 'notification' || $modul == 'akses_respo' || $modul == 'account'){
            return true;
        }
        if(!$respo_active) return false;
        $access = getBasic('m_respo_d')
            ->select('m_menu_id')
            ->join('m_role','m_role.id','m_respo_d.m_role_id')
            ->join('m_role_d','m_role.id','m_role_d.m_role_id')
            ->join('m_menu','m_menu.id','m_role_d.m_menu_id')
            ->where('m_respo_id', @$respo_active->m_respo_id ?? 0)
            ->where('m_menu.path', "/$modul")->exists();

       return $access;
    }
    
}