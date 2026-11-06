<?php

namespace App\Models\CustomModels;
use DB;

class t_klaim_ranap extends \App\Models\BasicModels\t_klaim_ranap
{    
        private $helper;
        
        public function __construct()
        {
            parent::__construct();
            $this->helper = getCore("Helper");

            // Dynamically add is_active column configurations
            $this->mergeFillable(['is_active']);
            $this->columns[] = 'is_active';
            $this->columnsFull[] = 'is_active:boolean';
            $this->createable[] = 'is_active';
            $this->updateable[] = 'is_active';
            $this->searchable[] = 'is_active';
            $this->casts['is_active'] = 'integer';

            $req = app()->request ?? null;
            if ($req) {
                $nomor = $req->input('nomor') ?? null;
                if (empty($nomor)) {
                    unset($this->unique['nomor']);
                }
            }
        }
        
        public $fileColumns    = [ /*file_column*/ ];

        public function createBefore($model, $arrayData, $metaData, $id = null)
        {
            try {
                $existsRanap = \DB::table('generate_num')->where('nama', 'KODE KLAIM RANAP')->first();
                if (!$existsRanap) {
                    $ranapId = \DB::table('generate_num')->insertGetId([
                        'nama' => 'KODE KLAIM RANAP',
                        'is_active' => true,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now()
                    ]);

                    $typesToInsert = [
                        ['ref_type' => 'text', 'value' => 'KLA/RWTI/', 'nama' => 'KLA/RWTI/'],
                        ['ref_type' => 'year', 'value' => 'y', 'nama' => 'y'],
                        ['ref_type' => 'month', 'value' => 'm', 'nama' => 'm'],
                        ['ref_type' => 'seq', 'value' => '6', 'nama' => '6']
                    ];

                    $seq = 1;
                    foreach ($typesToInsert as $t) {
                        $typeId = \DB::table('generate_num_type')
                            ->where('ref_type', $t['ref_type'])
                            ->where('value', $t['value'])
                            ->pluck('id')
                            ->first();
                        if (!$typeId) {
                            $typeId = \DB::table('generate_num_type')->insertGetId(array_merge($t, [
                                'created_at' => \Carbon\Carbon::now(),
                                'updated_at' => \Carbon\Carbon::now()
                            ]));
                        }
                        \DB::table('generate_num_det')->insert([
                            'generate_num_id' => $ranapId,
                            'generate_num_type_id' => $typeId,
                            'seq' => $seq++,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now()
                        ]);
                    }
                }

                $kode = @$this->helper->generateNomor("KODE KLAIM RANAP");
                $newArrayData = array_merge($arrayData, [
                    "nomor" => @$arrayData["nomor"] ?? $kode,
                    "is_active" => isset($arrayData["is_active"]) ? ($arrayData["is_active"] ? 1 : 0) : 1,
                ]);
                return [
                    "model" => $model,
                    "data" => $newArrayData,
                ];
            } catch (\Throwable $e) {
                abort(500, "Setup KODE KLAIM RANAP failed: " . $e->getMessage() . " in file " . $e->getFile() . " line " . $e->getLine());
            }
        }

        public function updateBefore($model, $arrayData, $metaData, $id = null)
        {
            if (isset($arrayData["is_active"])) {
                $arrayData["is_active"] = $arrayData["is_active"] ? 1 : 0;
            }
            return [
                "model" => $model,
                "data" => $arrayData,
            ];
        }
        
        public function custom_posted()
        {
            DB::beginTransaction();
            try {
                $id = app()->request->id;
                $data = $this->find($id);

                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data tidak ditemukan.'
                    ], 404);
                }

                $data->status = 'POSTED';
                $data->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Status berhasil diperbarui menjadi POSTED.'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }
        }
}
