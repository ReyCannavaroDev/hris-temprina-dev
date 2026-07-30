<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tPenyelesaianPerdinTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_penyelesaian_perdin', $payload);

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
		    "t_perdin_id" => "bigint:optional",
		    "m_kary_id" => "bigint:optional",
		    "total_biaya" => "decimal:optional",
		    "status" => "string:191:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "t_kbs_id" => "bigint:optional",
		    "sisa_biaya" => "decimal:optional",
		    "nomor" => "string:191:optional",
		    "nominal_kbs" => "decimal:optional",
		    "no_kbs" => "string:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional"
		];

        $this->call('POST', '/operation/t_penyelesaian_perdin', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_penyelesaian_perdin', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}