<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tKlaimAskesDTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_klaim_askes_d', $payload);

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
		    "t_klaim_askes_id" => "integer:optional",
		    "nominal" => "decimal:optional",
		    "accepted" => "decimal:optional",
		    "reject" => "decimal:optional",
		    "keterangan" => "string:191:optional",
		    "santunan" => "string:191:optional",
		    "tanggal" => "date:optional",
		    "bukti" => "string:191:optional",
		    "klaim_nama" => "string:191:optional",
		    "klaim_id" => "integer:optional",
		    "klaim_table" => "string:191:optional",
		    "nomor_bukti" => "string:191:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/t_klaim_askes_d', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_klaim_askes_d', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}