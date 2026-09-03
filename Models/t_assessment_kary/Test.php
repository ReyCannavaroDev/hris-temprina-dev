<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tAssessmentKaryTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_assessment_kary', $payload);

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
		    "tanggal" => "date:required",
		    "m_kary_id" => "bigint:required",
		    "atasan_id" => "bigint:required",
		    "m_assessment_kary_id" => "bigint:required",
		    "tipe_penilaian" => "string:191:optional",
		    "catatan_1" => "text:optional",
		    "catatan_2" => "text:optional",
		    "catatan_3" => "text:optional",
		    "catatan_4" => "text:optional",
		    "rata_rata" => "decimal:required",
		    "status" => "string:191:required",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "t_assessment_kary_d" => [
		        [
		            "id" => "bigint:optional:autocreate",
		            "t_assessment_kary_id" => "bigint:required:autocreate",
		            "nama_assessment" => "string:191:required",
		            "nama_kategori" => "string:191:required",
		            "total_nilai" => "integer:required",
		            "bobot" => "integer:required",
		            "creator_id" => "integer:optional",
		            "last_editor_id" => "integer:optional",
		            "created_at" => "datetime:optional:autocreate",
		            "updated_at" => "datetime:optional:autocreate",
		            "t_assessment_kary_sub_d" => [
		                [
		                    "id" => "bigint:optional:autocreate",
		                    "t_assessment_kary_d_id" => "bigint:required:autocreate",
		                    "nama_keterangan" => "string:191:required",
		                    "nilai" => "integer:required",
		                    "is_selected" => "boolean:optional",
		                    "creator_id" => "integer:optional",
		                    "last_editor_id" => "integer:optional",
		                    "created_at" => "datetime:optional:autocreate",
		                    "updated_at" => "datetime:optional:autocreate"
		                ]
		            ]
		        ]
		    ]
		];

        $this->call('POST', '/operation/t_assessment_kary', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_assessment_kary', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}