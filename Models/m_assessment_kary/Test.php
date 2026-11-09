<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mAssessmentKaryTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_assessment_kary', $payload);

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
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_divisi_id" => "bigint:optional",
		    "deskripsi" => "string:191:required",
		    "type" => "bigint:required",
		    "is_active" => "boolean:optional",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "m_assessment_kary_d" => [
		        [
		            "id" => "bigint:optional:autocreate",
		            "m_assessment_kary_id" => "bigint:required:autocreate",
		            "nama_assessment" => "string:191:required",
		            "kategori" => "bigint:required",
		            "bobot" => "integer:required",
		            "creator_id" => "integer:optional",
		            "last_editor_id" => "integer:optional",
		            "created_at" => "datetime:optional:autocreate",
		            "updated_at" => "datetime:optional:autocreate",
		            "m_assessment_kary_sub_d" => [
		                [
		                    "id" => "bigint:optional:autocreate",
		                    "m_assessment_kary_d_id" => "bigint:required:autocreate",
		                    "keterangan" => "string:191:required",
		                    "creator_id" => "integer:optional",
		                    "last_editor_id" => "integer:optional",
		                    "created_at" => "datetime:optional:autocreate",
		                    "updated_at" => "datetime:optional:autocreate",
		                    "nilai" => "integer:required"
		                ]
		            ]
		        ]
		    ]
		];

        $this->call('POST', '/operation/m_assessment_kary', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_assessment_kary', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}