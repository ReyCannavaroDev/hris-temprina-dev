<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tPengajuanPekerjaanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_pengajuan_pekerjaan', $payload);

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
		    "keterangan" => "string:191:optional",
		    "status" => "string:191:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "m_branch_id" => "bigint:optional"
		];

        $this->call('POST', '/operation/t_pengajuan_pekerjaan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_pengajuan_pekerjaan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}