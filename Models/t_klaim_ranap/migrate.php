    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class tklaimranap extends Migration
    {
        protected $tableName = "t_klaim_ranap";

        public function up()
        {
            Schema::create($this->tableName, function (Blueprint $table) {
                $table->id()->from(1);

                $table->string('nomor')->unique()->nullable(); // No Klaim
                $table->integer('m_kary_id')->comment('{"src":"m_kary.id"}'); // Relasi ke m_karyawan
                
                $table->date('periode_awal')->nullable();
                $table->date('periode_akhir')->nullable();
            
                $table->decimal('total_nominal', 22, 2)->default(0)->nullable(); 
                $table->string('status')->nullable()->default('DRAFT');
                $table->boolean('is_active')->default(true);

                $table->integer('creator_id')->nullable();
                $table->integer('last_editor_id')->nullable();
                $table->timestamps();
            });

            table_config($this->tableName, [
                "guarded"       => ["id"],
                "required"      => [],
                "!createable"   => ["id","created_at","updated_at"],
                "!updateable"   => ["id","created_at","updated_at"],
                "searchable"    => "all",
                "deleteable"    => "true",
                "deleteOnUse"   => "false",
                "extendable"    => "false",
                "casts"     => [
                    'created_at' => 'datetime:d/m/Y H:i',
                    'updated_at' => 'datetime:d/m/Y H:i'
                ]
            ]);

            \DB::table('m_menu')->insertOrIgnore([
                'm_comp_id' => null,
                'm_dir_id' => null,
                'modul' => 'Transaksi',
                'submodul' => null,
                'menu' => 'Klaim Rawat Inap',
                'path' => '/t_klaim_ranap',
                'endpoint' => '/t_klaim_ranap',
                'icon' => 'hospital',
                'sequence' => 12,
                'description' => 'Klaim Rawat Inap',
                'note' => null,
                'truncatable' => 0,
                'is_active' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        public function down()
        {
            \DB::table('m_menu')->where('path', '/t_klaim_ranap')->delete();
            Schema::dropIfExists($this->tableName);
        }
    }
