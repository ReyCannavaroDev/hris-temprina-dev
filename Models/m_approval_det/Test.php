<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mApprovalDetTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_approval_det', $payload);

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
		    "m_approval_id" => "bigint:optional",
		    "level" => "integer:required",
		    "m_role_id" => "bigint:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "type" => "string:10:required",
		    "default_users_id" => "bigint:optional",
		    "is_full" => "boolean:optional",
		    "is_skip" => "boolean:optional",
		    "name" => "string:optional",
		    "is_active" => "boolean:optional",
		    "m_comp_id" => "bigint:optional",
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_level_posisi_id" => "bigint:optional"
		];

        $this->call('POST', '/operation/m_approval_det', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_approval_det', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}