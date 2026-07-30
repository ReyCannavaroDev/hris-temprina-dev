<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tMutasiTest extends TestCase
{
    use DatabaseTransactions;

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_mutasi', $payload);

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
		    "nomor" => "string:50:optional",
		    "m_kary_id" => "bigint:required",
		    "tgl" => "date:required",
		    "tipe_mutasi" => "string:191:required",
		    "jenis_surat" => "bigint:optional",
		    "status_kary_lama_id" => "bigint:required",
		    "m_sbu_lama_id" => "bigint:optional",
		    "m_sub_lama_id" => "bigint:optional",
		    "m_branch_lama_id" => "bigint:optional",
		    "m_divisi_lama_id" => "bigint:optional",
		    "m_posisi_lama_id" => "bigint:optional",
		    "status_kary_baru_id" => "bigint:optional",
		    "m_sbu_baru_id" => "bigint:optional",
		    "m_sub_baru_id" => "bigint:optional",
		    "m_branch_baru_id" => "bigint:optional",
		    "m_divisi_baru_id" => "bigint:optional",
		    "m_posisi_baru_id" => "bigint:optional",
		    "no_dokumen" => "string:191:optional",
		    "file_dokumen" => "string:191:optional",
		    "deskripsi" => "text:optional",
		    "catatan" => "string:191:optional",
		    "keterangan" => "text:optional",
		    "status" => "string:50:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate",
		    "signature_id" => "bigint:optional",
		    "jadwal_kerja_lama_id" => "bigint:optional",
		    "jadwal_kerja_baru_id" => "bigint:optional",
		    "kompensasi" => "decimal:optional"
		];

        $this->call('POST', '/operation/t_mutasi', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_mutasi', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}