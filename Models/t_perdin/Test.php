<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tPerdinTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_perdin', $payload);

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
		    "m_kary_id" => "bigint:optional",
		    "date_from" => "date:required",
		    "date_to" => "date:required",
		    "tugas" => "string:191:required",
		    "tempat_tujuan" => "string:191:required",
		    "provinsi_id" => "bigint:optional",
		    "kota_id" => "bigint:optional",
		    "kecamatan_id" => "bigint:optional",
		    "alamat_tujuan" => "string:191:required",
		    "creator_id" => "integer:optional",
		    "status" => "string:191:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "nomor" => "string:optional",
		    "m_posisi_id" => "bigint:optional",
		    "m_atasan_id" => "bigint:optional"
		];

        $this->call('POST', '/operation/t_perdin', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_perdin', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}