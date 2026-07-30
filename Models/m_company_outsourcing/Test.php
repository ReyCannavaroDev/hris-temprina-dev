<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mCompanyOutsourcingTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_company_outsourcing', $payload);

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
		    "code" => "string:100:required",
		    "name" => "string:200:required",
		    "address" => "text:required",
		    "prov_id" => "bigint:required",
		    "city_id" => "bigint:required",
		    "district_id" => "bigint:required",
		    "postcode" => "string:30:required",
		    "nama_npwp" => "string:200:required",
		    "npwp" => "string:20:required",
		    "phone1" => "string:20:required",
		    "phone2" => "string:20:required",
		    "email" => "string:50:optional",
		    "website" => "string:20:optional",
		    "is_active" => "boolean:required",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "deletor_id" => "bigint:optional",
		    "deleted_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/m_company_outsourcing', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_company_outsourcing', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}