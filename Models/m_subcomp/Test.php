<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mSubcompTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_subcomp', $payload);

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
		    "m_comp_id" => "bigint:required",
		    "code" => "string:100:optional",
		    "name" => "string:200:required",
		    "province_id" => "bigint:required",
		    "city_id" => "bigint:required",
		    "district_id" => "bigint:required",
		    "address" => "string:200:required",
		    "npwp" => "string:200:required",
		    "phone1" => "string:200:required",
		    "phone2" => "string:200:optional",
		    "email" => "string:200:optional",
		    "is_active" => "boolean:required",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "deletor_id" => "bigint:optional",
		    "deleted_at" => "datetime:optional:autocreate",
		    "m_company_id" => "bigint:optional"
		];

        $this->call('POST', '/operation/m_subcomp', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_subcomp', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}