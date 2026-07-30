<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tRealisasiPelatihanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_realisasi_pelatihan', $payload);

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
		    "m_comp_id" => "bigint:optional",
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:optional",
		    "trainer_id" => "bigint:optional",
		    "m_prog_pelatihan_id" => "bigint:optional",
		    "date_from" => "date:required",
		    "date_to" => "date:required",
		    "desc" => "string:191:optional",
		    "status" => "string:191:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "sarana" => "string:191:optional",
		    "t_request_pelatihan_id" => "bigint:optional"
		];

        $this->call('POST', '/operation/t_realisasi_pelatihan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_realisasi_pelatihan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}