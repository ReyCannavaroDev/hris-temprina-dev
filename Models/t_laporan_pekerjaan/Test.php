<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tLaporanPekerjaanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_laporan_pekerjaan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        ff( $responseArr, 'dump data' );

        $this->assertTrue(true);
    }

    public function testCreatingData()
    {
        $user = User::where('username', 'USERNAME')->first();
        $this->assertNotEmpty( $user );
        
        Passport::actingAs($user);

        $payload = [
		    "id" => "bigint:optional:autocreate",
		    "t_pengajuan_pekerjaan_id" => "bigint:optional",
		    "kode" => "string:191:optional",
		    "m_divisi_id" => "bigint:optional",
		    "jenis_pekerjaan_id" => "bigint:optional",
		    "pekerjaan" => "string:191:required",
		    "start_date" => "date:optional",
		    "deadline_date" => "date:optional",
		    "pic_id" => "bigint:optional",
		    "m_divisi_pic_id" => "bigint:optional",
		    "request_id" => "bigint:optional",
		    "pekerjaan_sebelumnya_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "status" => "string:191:optional",
		    "sebelum" => "string:191:optional",
		    "tindakan" => "string:191:optional",
		    "setelah" => "string:191:optional",
		    "finish_date" => "date:optional",
		    "keterangan" => "string:191:optional",
		    "score" => "string:191:optional",
		    "catatan_penilaian" => "string:191:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/t_laporan_pekerjaan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_laporan_pekerjaan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}