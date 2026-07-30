<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class default_users_fcm extends Model
{   
    use ModelTrait;

    protected $table    = 'default_users_fcm';
    protected $guarded  = ['id'];
    protected $casts    = ['created_at'=> 'datetime:d-m-Y','updated_at'=>'datetime:d-m-Y'];
    protected $fillable = ["default_users_id","token_fcm","creator_id","last_editor_id","created_at","updated_at"];

    public $columns     = ["id","default_users_id","token_fcm","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","default_users_id:bigint","token_fcm:string:191","creator_id:integer","last_editor_id:integer","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_users.id=default_users_fcm.default_users_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["default_users_id","token_fcm"];
    public $createable  = ["default_users_id","token_fcm","creator_id","last_editor_id","created_at","updated_at"];
    public $updateable  = ["default_users_id","token_fcm","creator_id","last_editor_id","created_at","updated_at"];
    public $searchable  = ["default_users_id","token_fcm","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function default_users() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'default_users_id', 'id');
    }
}
