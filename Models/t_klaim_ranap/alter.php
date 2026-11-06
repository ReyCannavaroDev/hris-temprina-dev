<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tklaimranap extends Migration
{
    protected $tableName = "t_klaim_ranap";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            if (!Schema::hasColumn($this->tableName, 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // Ensure sidebar menu icon is set to hospital
        \DB::table('m_menu')
            ->where('path', '/t_klaim_ranap')
            ->update(['icon' => 'hospital']);

        // Register Laporan Klaim Rawat Inap under modul Laporan
        \DB::table('m_menu')->updateOrInsert(
            ['path' => '/l_klaim_ranap'],
            [
                'm_comp_id' => null,
                'm_dir_id' => null,
                'modul' => 'Laporan',
                'submodul' => null,
                'menu' => 'Laporan Klaim Rawat Inap',
                'endpoint' => '/l_klaim_ranap',
                'icon' => 'file-invoice-dollar',
                'sequence' => 12,
                'description' => 'Laporan Klaim Rawat Inap',
                'note' => null,
                'truncatable' => 0,
                'is_active' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]
        );

        $menu = \DB::table('m_menu')->where('path', '/l_klaim_ranap')->first();
        if ($menu) {
            $roles = \DB::table('m_role')->get();
            foreach ($roles as $role) {
                \DB::table('m_role_det')->updateOrInsert(
                    [
                        'm_role_id' => $role->id,
                        'm_menu_id' => $menu->id,
                    ],
                    [
                        'can_create' => 0,
                        'can_read' => 1,
                        'can_update' => 0,
                        'can_delete' => 0,
                        'can_verify' => 1,
                        'own_data' => 0,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]
                );
            }
        }

        // Ensure KODE KLAIM RANAP parameter setup exists
        try {
            $existsRanap = \DB::table('generate_num')->where('nama', 'KODE KLAIM RANAP')->exists();
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
        } catch (\Exception $e) {
            // Ignore database errors
        }
    }
}
