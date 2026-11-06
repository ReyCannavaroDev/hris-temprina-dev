<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mRespoTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_respo', $payload);

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
		    "m_comp_id" => "bigint:optional",
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "name" => "string:100:required",
		    "is_active" => "boolean:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "deletor_id" => "bigint:optional",
		    "deleted_at" => "datetime:optional:autocreate",
		    "m_respo_d_copy1" => [
		        [
		            "id" => "bigint:optional:autocreate",
		            "m_respo_id" => "bigint:optional:autocreate",
		            "m_role_id" => "bigint:required",
		            "note" => "string:200:optional",
		            "is_active" => "boolean:optional",
		            "creator_id" => "bigint:optional",
		            "last_editor_id" => "bigint:optional",
		            "created_at" => "datetime:optional:autocreate",
		            "updated_at" => "datetime:optional:autocreate",
		            "deletor_id" => "bigint:optional",
		            "deleted_at" => "datetime:optional:autocreate"
		        ]
		    ],
		    "m_respo_d" => [
		        [
		            "id" => "bigint:optional:autocreate",
		            "m_respo_id" => "bigint:optional:autocreate",
		            "m_role_id" => "bigint:required",
		            "note" => "string:200:optional",
		            "is_active" => "boolean:optional",
		            "creator_id" => "bigint:optional",
		            "last_editor_id" => "bigint:optional",
		            "created_at" => "datetime:optional:autocreate",
		            "updated_at" => "datetime:optional:autocreate",
		            "deletor_id" => "bigint:optional",
		            "deleted_at" => "datetime:optional:autocreate"
		        ]
		    ]
		];

        $this->call('POST', '/operation/m_respo', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_respo', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}