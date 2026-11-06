<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tSuratPeringatanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_surat_peringatan', $payload);

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
		    "m_kary_id" => "bigint:required",
		    "tgl" => "date:optional",
		    "level_sp" => "bigint:optional",
		    "m_sbu_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:optional",
		    "m_posisi_id" => "bigint:optional",
		    "signature_id" => "bigint:optional",
		    "file_dokumen" => "string:191:optional",
		    "status" => "string:50:required",
		    "masa_berlaku" => "integer:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/t_surat_peringatan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_surat_peringatan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}