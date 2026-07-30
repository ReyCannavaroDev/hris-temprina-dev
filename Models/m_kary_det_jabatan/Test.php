<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class mKaryDetJabatanTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/m_kary_det_jabatan', $payload);

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
		    "m_comp_id" => "bigint:optional",
		    "m_subcomp_id" => "bigint:optional",
		    "m_branch_id" => "bigint:optional",
		    "m_posisi_id" => "bigint:optional",
		    "start_time" => "date:optional",
		    "end_time" => "date:optional",
		    "desc" => "text:optional",
		    "is_primary" => "boolean:optional",
		    "is_active" => "boolean:required",
		    "creator_id" => "integer:optional",
		    "last_editor_id" => "integer:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "m_divisi_id" => "bigint:optional",
		    "m_karyawan_id" => "bigint:optional",
		    "m_company_id" => "integer:optional"
		];

        $this->call('POST', '/operation/m_kary_det_jabatan', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('m_kary_det_jabatan', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}