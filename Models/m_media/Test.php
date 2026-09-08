<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mMediaTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_media', $payload);

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
		    "nama" => "string:100:required",
		    "kategori_id" => "bigint:optional",
		    "m_comp_id" => "bigint:optional",
		    "file_path" => "string:255:optional",
		    "keterangan" => "text:optional",
		    "is_active" => "boolean:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/m_media', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_media', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}