<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mProgPelatihanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_prog_pelatihan', $payload);

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
		    "kode" => "string:191:required",
		    "tema_pelatihan" => "string:191:required",
		    "sasaran" => "string:191:optional",
		    "sifat_penyelenggara" => "string:191:required",
		    "jumlah_peserta" => "integer:required",
		    "total_budget" => "decimal:required",
		    "month" => "date:required",
		    "is_active" => "boolean:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/m_prog_pelatihan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_prog_pelatihan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}