<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mStandartGajiHarianTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_standart_gaji_harian', $payload);

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
		    "kode" => "string:50:optional",
		    "m_kary_id" => "bigint:optional",
		    "start_date" => "date:optional",
		    "m_zona_id" => "bigint:optional",
		    "m_posisi_id" => "bigint:optional",
		    "grading_id" => "bigint:optional",
		    "gaji_pokok" => "decimal:required",
		    "check_late" => "boolean:optional",
		    "desc" => "text:optional",
		    "is_active" => "boolean:required",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "total_jam_kerja" => "decimal:optional"
		];

        $this->call('POST', '/operation/m_standart_gaji_harian', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_standart_gaji_harian', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}