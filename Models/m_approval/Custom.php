<?php

namespace App\Models\CustomModels;

class m_approval extends \App\Models\BasicModels\m_approval
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
        $this->joins[] = "m_branch.id=m_approval.m_branch_id";
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $fillable   = ["m_comp_id","m_dir_id","m_menu_id","desc","is_active","creator_id","last_editor_id","name","code","note","identifier","m_branch_id"];
    public $columns    = ["id","m_comp_id","m_dir_id","m_menu_id","desc","is_active","creator_id","last_editor_id","created_at","updated_at","name","code","note","identifier","m_branch_id"];
    public $columnsFull= ["id:bigint","m_comp_id:bigint","m_dir_id:bigint","m_menu_id:bigint","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","name:string:100","code:string","note:string","identifier:string","m_branch_id:bigint"];
    public $createable = ["m_comp_id","m_dir_id","m_menu_id","desc","is_active","creator_id","last_editor_id","name","code","note","identifier","m_branch_id"];
    public $updateable = ["m_comp_id","m_dir_id","m_menu_id","desc","is_active","creator_id","last_editor_id","name","code","note","identifier","m_branch_id"];
    public $searchable = ["id","m_comp_id","m_dir_id","m_menu_id","desc","is_active","creator_id","last_editor_id","created_at","updated_at","name","code","note","identifier","m_branch_id"];

    public function m_branch()
    {
        return $this->belongsTo('App\Models\BasicModels\m_branch', 'm_branch_id', 'id');
    }

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        
        $newArrayData  = array_merge( $arrayData,[
            'code' => $this->helper->generateNomor('KODE APPROVAL')
        ] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }
    
}