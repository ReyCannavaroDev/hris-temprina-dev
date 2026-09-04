<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tReqRecruitmentTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_req_recruitment', $payload);

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
		    "nomor" => "string:50:optional",
		    "tanggal" => "date:optional",
		    "m_kary_id" => "bigint:optional",
		    "m_comp_id" => "bigint:optional",
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:required",
		    "m_dept_id" => "bigint:optional",
		    "m_posisi_id" => "bigint:required",
		    "jumlah_kebutuhan" => "integer:required",
		    "status_kary_id" => "bigint:optional",
		    "jenis_permintaan_id" => "bigint:optional",
		    "karyawan_digantikan_id" => "bigint:optional",
		    "tgl_dibutuhkan" => "date:required",
		    "prioritas_id" => "bigint:optional",
		    "alasan" => "text:optional",
		    "status" => "string:50:optional",
		    "t_loker_id" => "bigint:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/t_req_recruitment', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_req_recruitment', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}