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


    public function custom_upload_grade_excel($req)
    {
        $validator = \Validator::make($req->all(), [
            "file" => "required|mimes:xls,xlsx"
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Wrong format'], 401);
        }

        $collection = \Excel::toCollection(null, $req->file('file'));

        foreach ($collection[0] as $index => $row) {
            if ($index === 0)
                continue;

            $row = $row->toArray();

            if (count($row) < 6 || empty($row[1]))
                continue;

            m_grade::updateOrCreate(
            [
                'code' =>$row[1]
            ],[
                'm_zona_id' => $row[0],
                'keterangan' => $row[2],
                'score' => $row[3],
                'min' => $row[4],
                'max' => $row[5],
            ]);
        }
        return response()->json(['success' => 'File imported successfully'], 200);
    }
    
}