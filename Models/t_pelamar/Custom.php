<?php

namespace App\Models\CustomModels;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Carbon\Carbon;

class YourImportClass implements ToModel, WithHeadingRow
{
    protected $response = [];
    protected $currentSheet = null;

    public function model(array $row)
    {
        // Check if the current sheet is 't_pelamar'
        if ($this->currentSheet === "t_pelamar") {
            // Include only the desired fields if they exist in the $row array
            if (isset($row["temp_id"])) {
                $filteredRow = [
                    "temp_id" => $row["temp_id"],
                    "nomor" => $row["nomor"] ?? null,
                    "m_comp_id" => $row["m_comp_id"] ?? null,
                    "m_dir_id" => $row["m_dir_id"] ?? null,
                    "m_divisi_id" => $row["m_divisi_id"] ?? null,
                    "m_dept_id" => $row["m_dept_id"] ?? null,
                    "m_posisi_id" => $row["m_posisi_id"] ?? null,
                    "nama_pelamar" => $row["nama_pelamar"] ?? null,
                    "ktp_no" => $row["ktp_no"] ?? null,
                    "tanggal" => $row["tanggal"] ?? null,
                    "ref" => $row["ref"] ?? null,
                    "telp" => $row["telp"] ?? null,
                    "jk_id" => $row["jk_id"] ?? null,
                    "tempat_lahir" => $row["tempat_lahir"] ?? null,
                    "tgl_lahir" => $row["tgl_lahir"] ?? null,
                    "salary" => $row["salary"] ?? null,
                    "deskripsi" => $row["deskripsi"] ?? null,
                    "status" => $row["status"] ?? null,
                ];

                $filteredRow = array_filter($filteredRow, function ($value) {
                    return $value !== null;
                });

                if (!empty($filteredRow)) {
                    $this->response[] = $filteredRow;
                }
            }
        } elseif ($this->currentSheet === "t_pelamar_pend") {
            if (isset($row["nama_sekolah"]) && isset($row["pelamar_temp_id"])) {
                $filteredRow = [
                    "pelamar_temp_id" => $row["pelamar_temp_id"],
                    "tingkat_id" => $row["tingkat_id"] ?? null,
                    "nama_sekolah" => $row["nama_sekolah"] ?? null,
                    "tahun_masuk" => $row["tahun_masuk"] ?? null,
                    "tahun_lulus" => $row["tahun_lulus"] ?? null,
                    "kota_id" => $row["kota_id"] ?? null,
                    "nilai" => $row["nilai"] ?? null,
                    "jurusan" => $row["jurusan"] ?? null,
                    "is_pend_terakhir" => (($row["is_pend_terakhir"] == '=TRUE()') ? 1 : 0) ?? null,
                    "ijazah_no" => $row["ijazah_no"] ?? null,
                    "ijazah_foto" => $row["ijazah_foto"] ?? null,
                    "keterangan" => $row["keterangan"] ?? null,
                    "is_active" => (($row["is_active"] == '=TRUE()') ? 1 : 0) ?? null,
                ];

                $filteredRow = array_filter($filteredRow, function ($value) {
                    return $value !== null;
                });

                // Add to the response if there are non-null values
                if (!empty($filteredRow)) {
                    $this->response[] = $filteredRow;
                }
            }
        } elseif ($this->currentSheet === "t_pelamar_peng") {
            // Include only the desired fields for 't_pelamar_pend' if they exist in the $row array
            if (
                isset($row["nama_pengalaman"]) &&
                isset($row["pelamar_temp_id"])
            ) {
                $filteredRow = [
                    "pelamar_temp_id" => $row["pelamar_temp_id"],
                    "nama_pengalaman" => $row["nama_pengalaman"] ?? null,
                    "posisi" => $row["posisi"] ?? null,
                    "date_from" => $row["date_from"] ?? null,
                    "date_to" => $row["date_to"] ?? null,
                    "kota_id" => $row["kota_id"] ?? null,
                    "keterangan" => $row["keterangan"] ?? null,
                    "is_active" => $row["is_active"] ?? null,
                ];

                $filteredRow = array_filter($filteredRow, function ($value) {
                    return $value !== null;
                });

                // Add to the response if there are non-null values
                if (!empty($filteredRow)) {
                    $this->response[] = $filteredRow;
                }
            }
        }

        return null;
    }

    public function setSheet($sheetName)
    {
        $this->currentSheet = $sheetName;
    }

    public function getResponse()
    {
        return response()->json($this->response);
    }

    public function resetResponse()
    {
        $this->response = [];
    }
}

class t_pelamar extends \App\Models\BasicModels\t_pelamar
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore("Helper");
    }

    public $fileColumns = [
        'file_cv',
        'file_dokumen'
    ];

    public $joins = [
        "t_loker.id=t_pelamar.t_loker_id",
        "m_general.id=t_pelamar.jk_id",
        "default_users.id=t_pelamar.creator_id",
        "default_users.id=t_pelamar.last_editor_id"
    ];

    public function scopeloker($model)
    {
        $loker_id = request("t_loker_id") ?? null;
        return $model->when($loker_id, function ($q) use ($loker_id) {
            $q->where("t_pelamar.t_loker_id", $loker_id);
        })->where(function($q) {
            $q->whereRaw("upper(coalesce(t_pelamar.status, '')) != 'DRAFT'")
              ->whereRaw("upper(coalesce(t_pelamar.status, '')) != 'DITOLAK'")
              ->whereRaw("upper(coalesce(t_pelamar.status, '')) != 'TIDAK DITERIMA'");
        });
    }

    public function custom_postData($req)
    {
        $id = $req->id ?? request('id');
        $data = $this->find($id);
        if (!$data) {
            return response()->json(['message' => 'Data pelamar tidak ditemukan.'], 404);
        }

        $data->update([
            'status' => 'POSTED'
        ]);

        return response()->json([
            'message' => 'Data pelamar berhasil diposting.'
        ]);
    }

    public function custom_posted($req)
    {
        return $this->custom_postData($req);
    }

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function createBefore($model, $arrayData, $metaData, $id = null)
    {
        $newArrayData = array_merge($arrayData, [
            "nomor" => $this->helper->generateNomor("KODE PELAMAR"),
        ]);

        return [
            "model" => $model,
            "data" => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_destroy($req)
    {
        \DB::beginTransaction();

        try {
            $pelamar_id = $req->id;

            $t_pelamar = t_pelamar::with([
                "t_pelamar_det_bhs",
                "t_pelamar_det_kartu",
                "t_pelamar_det_org",
                "t_pelamar_det_pel",
                "t_pelamar_det_pend",
                "t_pelamar_det_pk",
                "t_pelamar_det_pres",
                "t_pelamar_det_dokumen",
            ])->find($pelamar_id);

            if (!$t_pelamar) {
                return response()->json(
                    ["message" => "Pelamar tidak ditemukan"],
                    404
                );
            }

            $t_pelamar->t_pelamar_det_bhs()->delete();
            $t_pelamar->t_pelamar_det_kartu()->delete();
            $t_pelamar->t_pelamar_det_org()->delete();
            $t_pelamar->t_pelamar_det_pel()->delete();
            $t_pelamar->t_pelamar_det_pend()->delete();
            $t_pelamar->t_pelamar_det_pk()->delete();
            $t_pelamar->t_pelamar_det_pres()->delete();
            $t_pelamar->t_pelamar_det_dokumen()->delete();


            $t_pelamar->delete();

            \DB::commit();

            return response()->json(
                [
                    "message" =>
                        "Data karyawan dan seluruh relasinya berhasil dihapus.",
                ],
                200
            );
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(
                [
                    "message" => "Gagal menghapus data karyawan",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function custom_parseCv($req)
    {
        try {
            if (!$req->hasFile('file')) {
                return response()->json(['error' => 'File CV tidak ditemukan dalam request'], 422);
            }

            $file = $req->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['pdf', 'docx', 'doc'])) {
                return response()->json(['error' => 'Format berkas harus PDF (.pdf) atau Word (.docx)'], 422);
            }

            // Simpan berkas fisik ke folder uploads
            $targetDir = public_path('uploads/t_pelamar');
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $filename = 'cv_' . time() . '_' . uniqid() . '.' . $ext;
            $file->move($targetDir, $filename);
            $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            $savedFilePath = 'uploads/t_pelamar/' . $filename;

            // Ekstrak teks mentah
            $rawText = $this->extractTextFromFile($fullPath, $ext);
            $rawFileContent = @file_get_contents($fullPath) ?: '';

            // Parsing teks menggunakan regex & aturan heuristik
            $parsedData = $this->parseCvText($rawText, $rawFileContent);
            $parsedData['file_cv'] = $savedFilePath;

            return response()->json([
                'success' => true,
                'message' => 'CV berhasil dipindai dan diekstrak',
                'data' => $parsedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses file CV: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractTextFromFile($filePath, $ext)
    {
        if ($ext === 'docx') {
            return $this->extractTextFromDocx($filePath);
        } elseif ($ext === 'pdf') {
            return $this->extractTextFromPdf($filePath);
        } elseif ($ext === 'doc') {
            return $this->extractTextFromDoc($filePath);
        }
        return '';
    }

    private function extractTextFromDocx($filePath)
    {
        $text = '';
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    $xmlData = $zip->getFromIndex($index);
                    // Pertahankan baris baru dan pemisah kolom/tabel
                    $xmlData = preg_replace('/<w:p[^>]*>/', "\n", $xmlData);
                    $xmlData = preg_replace('/<w:br[^>]*>/', "\n", $xmlData);
                    $xmlData = preg_replace('/<w:tab[^>]*>/', "\t", $xmlData);
                    $xmlData = preg_replace('/<w:tr[^>]*>/', "\n", $xmlData);
                    $text = strip_tags($xmlData);
                }
                $zip->close();
            }
        }
        return $text;
    }

    private function extractTextFromDoc($filePath)
    {
        $fileHandle = @fopen($filePath, "r");
        $line = @fread($fileHandle, filesize($filePath));
        @fclose($fileHandle);
        $lines = explode(chr(0x0D), $line);
        $outtext = "";
        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));
            if ($pos !== false || strlen($thisline) == 0) {
                // skip
            } else {
                $outtext .= $thisline . " ";
            }
        }
        $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\_\@\:\/\(\)\+]/", " ", $outtext);
        return $outtext;
    }

    private function extractTextFromPdf($filePath)
    {
        $content = @file_get_contents($filePath);
        if (!$content) return '';

        $text = '';

        // 1. Ekstrak seluruh stream biner di dalam PDF
        if (preg_match_all('/stream[\r\n]+([\s\S]*?)[\r\n]+endstream/m', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                // Dekompresi FlateDecode
                $decompressed = @gzuncompress($stream);
                if ($decompressed === false) {
                    $decompressed = @gzinflate($stream);
                }
                if ($decompressed === false) {
                    $decompressed = $stream;
                }

                // Ambil blok teks PDF: BT ... ET
                if (preg_match_all('/BT[\s\S]*?ET/m', $decompressed, $textBlocks)) {
                    foreach ($textBlocks[0] as $block) {
                        // Pola Tj tunggal: (Teks) Tj atau <Hex> Tj
                        if (preg_match_all('/(?:\(([\s\S]*?)\)|<([0-9a-fA-F\s]+)>)\s*Tj/s', $block, $tjMatches, PREG_SET_ORDER)) {
                            foreach ($tjMatches as $m) {
                                if (!empty($m[1])) {
                                    $text .= $this->decodePdfString($m[1]) . ' ';
                                } elseif (!empty($m[2])) {
                                    $text .= $this->decodePdfHex($m[2]) . ' ';
                                }
                            }
                            $text .= "\n";
                        }
                        // Pola TJ array: [(Teks1) 120 <Hex2> (Teks3)] TJ
                        if (preg_match_all('/\[([\s\S]*?)\]\s*TJ/s', $block, $tjArrayMatches)) {
                            foreach ($tjArrayMatches[1] as $arr) {
                                if (preg_match_all('/(?:\(([\s\S]*?)\)|<([0-9a-fA-F\s]+)>)/s', $arr, $innerMatches, PREG_SET_ORDER)) {
                                    foreach ($innerMatches as $m) {
                                        if (!empty($m[1])) {
                                            $text .= $this->decodePdfString($m[1]);
                                        } elseif (!empty($m[2])) {
                                            $text .= $this->decodePdfHex($m[2]);
                                        }
                                    }
                                }
                            }
                            $text .= "\n";
                        }
                        // Operator petik: (Teks) ' atau "
                        if (preg_match_all('/(?:\(([\s\S]*?)\)|<([0-9a-fA-F\s]+)>)\s*[\'"]/s', $block, $quoteMatches, PREG_SET_ORDER)) {
                            foreach ($quoteMatches as $m) {
                                if (!empty($m[1])) {
                                    $text .= $this->decodePdfString($m[1]) . "\n";
                                } elseif (!empty($m[2])) {
                                    $text .= $this->decodePdfHex($m[2]) . "\n";
                                }
                            }
                        }
                    }
                } else {
                    // Fallback scan teks langsung dari stream terdekompresi
                    if (preg_match_all('/[a-zA-Z0-9._%+\-@:\/\(\)\s,]{4,}/', $decompressed, $rawMatches)) {
                        $text .= implode(" ", $rawMatches[0]) . "\n";
                    }
                }
            }
        }

        // 2. Fallback scan jika tidak terdeteksi via stream (uncompressed PDF biasa)
        if (trim($text) === '' || strlen(trim($text)) < 50) {
            if (preg_match_all('/(?:\(([\s\S]*?)\)|<([0-9a-fA-F\s]+)>)\s*Tj/s', $content, $tjMatches, PREG_SET_ORDER)) {
                foreach ($tjMatches as $m) {
                    if (!empty($m[1])) {
                        $text .= $this->decodePdfString($m[1]) . ' ';
                    } elseif (!empty($m[2])) {
                        $text .= $this->decodePdfHex($m[2]) . ' ';
                    }
                }
            }
        }

        // Bersihkan whitespace berulang
        return preg_replace('/[ \t]+/', ' ', $text);
    }

    private function decodePdfString($str)
    {
        $str = str_replace(['\\\\', '\(', '\)', '\n', '\r', '\t'], ['\\', '(', ')', "\n", "\r", "\t"], $str);
        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($m) {
            return chr(octdec($m[1]));
        }, $str);
        return $str;
    }

    private function decodePdfHex($hex)
    {
        $hex = preg_replace('/\s+/', '', $hex);
        if (strlen($hex) % 2 !== 0) {
            $hex .= '0';
        }
        $bin = @hex2bin($hex);
        if ($bin === false) return '';

        // Deteksi jika encoded UTF-16BE (banyak dipakai PDF generator modern / Canva)
        if (strlen($bin) >= 2 && substr($bin, 0, 2) === "\xFE\xFF") {
            return @mb_convert_encoding(substr($bin, 2), 'UTF-8', 'UTF-16BE') ?: '';
        } elseif (strlen($bin) >= 2 && ord($bin[0]) === 0 && ord($bin[1]) >= 32 && ord($bin[1]) <= 126) {
            return @mb_convert_encoding($bin, 'UTF-8', 'UTF-16BE') ?: '';
        }

        return $bin;
    }

    private function parseCvText($rawText, $rawFileContent = '')
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $rawText)), function ($l) {
            return strlen($l) > 0;
        }));

        $result = [
            'nama_lengkap' => null,
            'nama_depan' => null,
            'nama_belakang' => null,
            'nama_panggilan' => null,
            'email' => null,
            'telp' => null,
            'no_tlp_lainnya' => null,
            'tempat_lahir' => null,
            'tgl_lahir' => null,
            'jk_id' => null,
            'alamat_domisili' => null,
            'linkedin' => null,
            'ig' => null,
            'facebook' => null,
            'x' => null,
            't_pelamar_det_pend' => [],
            't_pelamar_det_pk' => [],
            't_pelamar_det_pel' => [],
            't_pelamar_det_org' => [],
            't_pelamar_det_bhs' => [],
        ];

        // 1. Email Regex (Mendukung format di teks dan link mailto: di PDF stream)
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,6}/i', $rawText, $matchEmail)) {
            $result['email'] = strtolower(trim($matchEmail[0]));
        } elseif ($rawFileContent && preg_match('/(?:mailto:|\b)([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,6})\b/i', $rawFileContent, $matchEmail2)) {
            $result['email'] = strtolower(trim($matchEmail2[1]));
        }

        // 2. Nomor HP / WhatsApp (Mendukung format Indonesia +62, 62, 08, atau berlabel)
        $phoneCandidates = [];
        if (preg_match_all('/(?:telp|telepon|phone|hp|mobile|wa|whatsapp|kontak|contact)[\s\:\.\-]*(\+?[0-9\s\-\.\(\)]{9,20})/i', $rawText, $mPhoneLabel)) {
            foreach ($mPhoneLabel[1] as $p) {
                $clean = preg_replace('/[^\d+]/', '', $p);
                if (strlen($clean) >= 10 && strlen($clean) <= 16) {
                    $phoneCandidates[] = $clean;
                }
            }
        }
        if (preg_match_all('/(?:(?:\+?62|0)[\s\-\.\(]*8[0-9]{1,3}[\s\-\.\)]*[0-9]{2,4}[\s\-\.]*[0-9]{3,5})/', $rawText, $mPhones)) {
            foreach ($mPhones[0] as $p) {
                $clean = preg_replace('/[^\d+]/', '', $p);
                if (strlen($clean) >= 10 && strlen($clean) <= 16) {
                    $phoneCandidates[] = $clean;
                }
            }
        }
        if (empty($phoneCandidates) && $rawFileContent && preg_match_all('/(?:tel:|wa\.me\/|(?:\+?62|0)8)[0-9\s\-\.\(\)]{8,18}/i', $rawFileContent, $mPhones2)) {
            foreach ($mPhones2[0] as $p) {
                $clean = preg_replace('/[^\d+]/', '', $p);
                if (strlen($clean) >= 10 && strlen($clean) <= 16) {
                    $phoneCandidates[] = $clean;
                }
            }
        }
        if (!empty($phoneCandidates)) {
            // Ambil nomor telepon terlengkap
            usort($phoneCandidates, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
            $result['telp'] = $phoneCandidates[0];
        }

        // 3. Media Sosial
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?linkedin\.com\/in\/([a-zA-Z0-9_\-\.]+)/i', $rawText, $matchIn)) {
            $result['linkedin'] = 'https://linkedin.com/in/' . $matchIn[1];
        } elseif (preg_match('/(?:LinkedIn)\s*[:=]?\s*@?([a-zA-Z0-9_\.\-]{3,40})/i', $rawText, $matchIn2)) {
            $result['linkedin'] = 'https://linkedin.com/in/' . $matchIn2[1];
        } elseif ($rawFileContent && preg_match('/linkedin\.com\/in\/([a-zA-Z0-9_\-\.]+)/i', $rawFileContent, $matchIn3)) {
            $result['linkedin'] = 'https://linkedin.com/in/' . $matchIn3[1];
        }

        if (preg_match('/(?:https?:\/\/)?(?:www\.)?instagram\.com\/([a-zA-Z0-9_\.]+)/i', $rawText, $matchIg)) {
            $result['ig'] = '@' . $matchIg[1];
        } elseif (preg_match('/(?:IG|Instagram)\s*[:=]?\s*@?([a-zA-Z0-9_\.]{3,30})/i', $rawText, $matchIg2)) {
            $result['ig'] = '@' . $matchIg2[1];
        }

        if (preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/([a-zA-Z0-9_\.]+)/i', $rawText, $matchFb)) {
            $result['facebook'] = $matchFb[1];
        }
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/i', $rawText, $matchX)) {
            $result['x'] = '@' . $matchX[1];
        }

        // 4. Jenis Kelamin (Auto-lookup ke m_general)
        $isMale = preg_match('/\b(laki[\s-]?laki|pria|male|man)\b/i', $rawText);
        $isFemale = preg_match('/\b(perempuan|wanita|female|woman)\b/i', $rawText);
        if ($isMale && !$isFemale) {
            $result['jk_id'] = \DB::table('m_general')
                ->where('group', 'JENIS KELAMIN')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('value', 'ILIKE', '%laki%')->orWhere('code', 'ILIKE', '%L%');
                })->value('id');
        } elseif ($isFemale && !$isMale) {
            $result['jk_id'] = \DB::table('m_general')
                ->where('group', 'JENIS KELAMIN')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('value', 'ILIKE', '%perempuan%')->orWhere('value', 'ILIKE', '%wanita%')->orWhere('code', 'ILIKE', '%P%');
                })->value('id');
        }

        // 5. Nama Pelamar (Membaca baris awal dokumen sebelum kontak)
        $headerIgnores = ['curriculum vitae', 'resume', 'biodata', 'data diri', 'cv', 'profile', 'personal profile', 'tentang saya', 'about me', 'kontak', 'contact'];
        foreach ($lines as $line) {
            $cleanLine = trim($line);
            $lowerLine = strtolower($cleanLine);
            if (in_array($lowerLine, $headerIgnores) || strlen($cleanLine) < 3) {
                continue;
            }
            if (strpos($cleanLine, '@') !== false || preg_match('/\d{5,}/', $cleanLine) || strpos($lowerLine, 'http') !== false) {
                continue;
            }
            // Validasi string nama (hanya huruf, spasi, titik, koma, petik)
            if (preg_match('/^[a-zA-Z\s\.,\'\(\)]+$/', $cleanLine) && strlen($cleanLine) <= 60 && count(explode(' ', $cleanLine)) <= 6) {
                $cleanName = ucwords(strtolower($cleanLine));
                $result['nama_lengkap'] = $cleanName;
                $parts = explode(' ', $cleanName);
                $result['nama_depan'] = $parts[0] ?? '';
                $result['nama_belakang'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $result['nama_depan'];
                $result['nama_panggilan'] = $result['nama_depan'];
                break;
            }
        }

        // 6. Tempat, Tanggal Lahir (TTL)
        if (preg_match('/(?:tempat[\s,]*(?:dan[\s,]*)?tanggal\s*lahir|ttl|tempat\/tgl\s*lahir|date\s*of\s*birth|dob)\s*[:=]?\s*([^\n\r,]+)[,\s]+([0-9]{1,2}[\s\/\-\.](?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|[0-9]{1,2})[\s\/\-\.][0-9]{4})/i', $rawText, $matchTtl)) {
            $result['tempat_lahir'] = trim($matchTtl[1]);
            $result['tgl_lahir'] = $this->parseIndoDate(trim($matchTtl[2]));
        } elseif (preg_match('/\b([0-9]{1,2}[\s\/\-\.](?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|[0-9]{1,2})[\s\/\-\.](?:19[6-9][0-9]|20[0-2][0-9]))\b/i', $rawText, $matchDate)) {
            $result['tgl_lahir'] = $this->parseIndoDate(trim($matchDate[1]));
        }

        // 7. Segmentasi Bagian & Parsing Detail
        $sections = $this->splitIntoSections($rawText);

        if (!empty($sections['pendidikan'])) {
            $result['t_pelamar_det_pend'] = $this->parseEducationSection($sections['pendidikan']);
        }
        if (!empty($sections['pengalaman'])) {
            $result['t_pelamar_det_pk'] = $this->parseExperienceSection($sections['pengalaman']);
        }
        if (!empty($sections['organisasi'])) {
            $result['t_pelamar_det_org'] = $this->parseOrganizationSection($sections['organisasi']);
        }
        if (!empty($sections['pelatihan'])) {
            $result['t_pelamar_det_pel'] = $this->parseTrainingSection($sections['pelatihan']);
        }
        if (!empty($sections['bahasa'])) {
            $result['t_pelamar_det_bhs'] = $this->parseLanguageSection($sections['bahasa']);
        }

        return $result;
    }

    private function splitIntoSections($rawText)
    {
        $sections = [
            'pendidikan' => '',
            'pengalaman' => '',
            'organisasi' => '',
            'pelatihan' => '',
            'bahasa' => '',
        ];

        $lines = explode("\n", $rawText);
        $currentSection = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $upper = strtoupper($trimmed);

            // Deteksi judul section
            if (preg_match('/^(?:RIWAYAT\s+)?PENDIDIKAN|EDUCATION|LATAR\s+BELAKANG\s+PENDIDIKAN/i', $upper)) {
                $currentSection = 'pendidikan';
                continue;
            } elseif (preg_match('/^(?:RIWAYAT\s+)?PENGALAMAN(?:\s+KERJA)?|WORK\s+EXPERIENCE|PENGALAMAN\s+KERJA|PENGALAMAN\s+PROFESIONAL|EXPERIENCE/i', $upper)) {
                $currentSection = 'pengalaman';
                continue;
            } elseif (preg_match('/^ORGANISASI|PENGALAMAN\s+ORGANISASI|ORGANIZATIONAL\s+EXPERIENCE/i', $upper)) {
                $currentSection = 'organisasi';
                continue;
            } elseif (preg_match('/^PELATIHAN|SERTIFIKASI|TRAINING|COURSES|SERTIFIKAT/i', $upper)) {
                $currentSection = 'pelatihan';
                continue;
            } elseif (preg_match('/^BAHASA|KEMAMPUAN\s+BAHASA|LANGUAGES/i', $upper)) {
                $currentSection = 'bahasa';
                continue;
            } elseif (preg_match('/^KEAHLIAN|SKILLS|PROYEK|PROJECTS|MINAT|HOBBY|TENTANG\s+SAYA|ABOUT\s+ME/i', $upper)) {
                $currentSection = null; // Lewati section non-tabel
                continue;
            }

            if ($currentSection && isset($sections[$currentSection])) {
                $sections[$currentSection] .= $trimmed . "\n";
            }
        }

        return $sections;
    }

    private function parseEducationSection($sectionText)
    {
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $sectionText)), function ($l) {
            return strlen($l) > 0;
        }));

        $current = null;

        foreach ($lines as $line) {
            // Cek apakah baris mengandung tahun kelulusan / rentang tahun (contoh: 2018 - 2022 atau 2020)
            $hasYear = preg_match('/(?:(20[0-2][0-9]|19[8-9][0-9])\s*[-–]\s*(20[0-2][0-9]|Sekarang|Present|\d{4})|(20[0-2][0-9]|19[8-9][0-9]))/i', $line, $matchYear);

            // Cek apakah ada kata kunci sekolah / universitas / jenjang
            $hasDegreeOrSchool = preg_match('/\b(Universitas|Institut|Politeknik|Sekolah Tinggi|Akademi|SMA|SMK|MAN|SMP|SD|S1|S2|S3|D3|D4|Sarjana|Diploma|Magister)\b/i', $line);

            if ($hasDegreeOrSchool || $hasYear) {
                if ($current && (!empty($current['nama_sekolah']) || !empty($current['tingkat_id']))) {
                    $items[] = $current;
                }

                $current = [
                    'tingkat_id' => null,
                    'tingkat' => null,
                    'nama_sekolah' => null,
                    'thn_masuk' => null,
                    'thn_lulus' => null,
                    'kota_id' => null,
                    'nilai' => null,
                    'jurusan' => null,
                    'is_pend_terakhir' => 0,
                    'desc' => null,
                ];

                // Tangkap tahun
                if ($hasYear) {
                    if (isset($matchYear[1]) && !empty($matchYear[1])) {
                        $current['thn_masuk'] = $matchYear[1];
                        $current['thn_lulus'] = is_numeric($matchYear[2]) ? $matchYear[2] : date('Y');
                    } elseif (isset($matchYear[3])) {
                        $current['thn_lulus'] = $matchYear[3];
                    }
                }

                // Tangkap jenjang pendidikan & lookup m_general
                if (preg_match('/\b(S3|Doktor)\b/i', $line)) {
                    $current['tingkat'] = 'S3';
                } elseif (preg_match('/\b(S2|Magister|Master)\b/i', $line)) {
                    $current['tingkat'] = 'S2';
                } elseif (preg_match('/\b(S1|Sarjana|Bachelor)\b/i', $line)) {
                    $current['tingkat'] = 'S1';
                } elseif (preg_match('/\b(D4|Diploma 4)\b/i', $line)) {
                    $current['tingkat'] = 'D4';
                } elseif (preg_match('/\b(D3|Diploma 3|Diploma)\b/i', $line)) {
                    $current['tingkat'] = 'D3';
                } elseif (preg_match('/\b(SMK|Sekolah Menengah Kejuruan)\b/i', $line)) {
                    $current['tingkat'] = 'SMK';
                } elseif (preg_match('/\b(SMA|MA|Sekolah Menengah Atas)\b/i', $line)) {
                    $current['tingkat'] = 'SMA';
                } elseif (preg_match('/\b(SMP|MTS)\b/i', $line)) {
                    $current['tingkat'] = 'SMP';
                } elseif (preg_match('/\b(SD)\b/i', $line)) {
                    $current['tingkat'] = 'SD';
                }

                if ($current['tingkat']) {
                    $t = strtoupper($current['tingkat']);
                    $current['tingkat_id'] = \DB::table('m_general')
                        ->where(function ($q) {
                            $q->where('group', 'ILIKE', '%PENDIDIKAN%')->orWhere('group', 'ILIKE', '%TINGKAT%');
                        })
                        ->where('is_active', true)
                        ->where(function ($q) use ($t) {
                            $q->where('code', $t)
                              ->orWhere('value', 'ILIKE', '%' . $t . '%');
                            if ($t === 'SMK') {
                                $q->orWhere('value', 'ILIKE', '%SMA%')->orWhere('value', 'ILIKE', '%SLTA%')->orWhere('code', 'SMA')->orWhere('code', 'SLTA');
                            } elseif ($t === 'SMA') {
                                $q->orWhere('value', 'ILIKE', '%SMK%')->orWhere('value', 'ILIKE', '%SLTA%');
                            } elseif ($t === 'SMP') {
                                $q->orWhere('value', 'ILIKE', '%SLTP%')->orWhere('code', 'SLTP');
                            } elseif ($t === 'S1') {
                                $q->orWhere('value', 'ILIKE', '%SARJANA%')->orWhere('code', 'SARJANA');
                            } elseif ($t === 'D3') {
                                $q->orWhere('value', 'ILIKE', '%DIPLOMA%');
                            }
                        })->value('id');
                }

                // Tangkap nama institusi sekolah/universitas
                if (preg_match('/((?:Universitas|Institut|Politeknik|Sekolah Tinggi|Akademi|SMA|SMK|MAN|SMP|SD)[^\n\r,\(]+)/i', $line, $matchSchool)) {
                    $current['nama_sekolah'] = trim(rtrim($matchSchool[1], '\\/'));
                } else {
                    $current['nama_sekolah'] = trim(rtrim($line, '\\/'));
                }
            } else if ($current) {
                // Baris lanjutan: cek jurusan atau IPK
                if (preg_match('/(?:IPK|GPA|Nilai)\s*[:=]?\s*([0-4](?:\.[0-9]{1,2})?)/i', $line, $matchIpk)) {
                    $current['nilai'] = $matchIpk[1];
                }
                if (preg_match('/(?:Jurusan|Program Studi|Prodi|Major)\s*[:=]?\s*([^\n\r,]+)/i', $line, $matchJurusan)) {
                    $current['jurusan'] = trim(rtrim($matchJurusan[1], '\\/'));
                } elseif (!$current['jurusan'] && preg_match('/\b(Teknik|Sistem Informasi|Informatika|Akuntansi|Manajemen|Ilmu Komunikasi|Hukum|Psikologi|Desain|IPA|IPS|RPL|TKJ|Multimedia)\b[^\n\r,]*/i', $line, $matchJurusan2)) {
                    $current['jurusan'] = trim(rtrim($matchJurusan2[0], '\\/'));
                }
            }
        }

        if ($current && (!empty($current['nama_sekolah']) || !empty($current['tingkat_id']))) {
            $items[] = $current;
        }

        if (count($items) > 0) {
            $items[0]['is_pend_terakhir'] = 1;
        }

        return $items;
    }

    private function parseExperienceSection($sectionText)
    {
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $sectionText)), function ($l) {
            return strlen($l) > 0;
        }));

        $current = null;

        foreach ($lines as $line) {
            // Header pengalaman kerja HANYA dipicu oleh adanya rentang tahun / tahun jelas ATAU nama institusi PT/CV
            $hasYear = preg_match('/(?:(20[0-2][0-9]|19[8-9][0-9])\s*[-–]\s*(20[0-2][0-9]|Sekarang|Present|\d{4})|(20[0-2][0-9]|19[8-9][0-9]))/i', $line, $matchYear);
            $hasCompanyPrefix = preg_match('/^(?:PT|CV|PT\.|CV\.)\s+/i', $line);

            if ($hasYear || $hasCompanyPrefix) {
                if ($current && (!empty($current['instansi']) || !empty($current['posisi']))) {
                    $items[] = $current;
                }

                $current = [
                    'instansi' => null,
                    'thn_masuk' => null,
                    'thn_keluar' => null,
                    'kota_id' => null,
                    'alamat_kantor' => null,
                    'bidang_usaha' => null,
                    'no_tlp' => null,
                    'posisi' => null,
                ];

                if ($hasYear) {
                    if (isset($matchYear[1]) && !empty($matchYear[1])) {
                        $current['thn_masuk'] = $matchYear[1];
                        $current['thn_keluar'] = is_numeric($matchYear[2]) ? $matchYear[2] : date('Y');
                    } elseif (isset($matchYear[3])) {
                        $current['thn_masuk'] = $matchYear[3];
                        $current['thn_keluar'] = $matchYear[3];
                    }
                }

                // Cek nama perusahaan (PT/CV)
                if (preg_match('/((?:PT|CV)\s+[^\n\r,\(]+)/i', $line, $matchComp)) {
                    $current['instansi'] = trim($matchComp[1]);
                }

                // Cek posisi
                if (preg_match('/\b((?:Software\s+Engineer|Frontend\s+Developer|Backend\s+Developer|Fullstack\s+Developer|Web\s+Developer|Mobile\s+Developer|Staff|Supervisor|Manager|Admin|Operator|Intern|Magang|Designer|Marketing|Sales|Accountant)[^\n\r,]*)/i', $line, $matchPos)) {
                    $current['posisi'] = trim($matchPos[1]);
                }

                if (!$current['instansi'] && !$current['posisi']) {
                    $current['posisi'] = trim(preg_replace('/(?:(20[0-2][0-9]|19[8-9][0-9])\s*[-–]\s*(20[0-2][0-9]|Sekarang|Present|\d{4})|(20[0-2][0-9]|19[8-9][0-9]))/i', '', $line));
                }
            } elseif ($current) {
                // Baris detail/deskripsi pendukung
                if (!$current['instansi'] && preg_match('/((?:PT|CV)\s+[^\n\r,\(]+)/i', $line, $matchComp2)) {
                    $current['instansi'] = trim($matchComp2[1]);
                } elseif (!$current['posisi'] && preg_match('/\b((?:Software\s+Engineer|Frontend\s+Developer|Backend\s+Developer|Fullstack\s+Developer|Web\s+Developer|Mobile\s+Developer|Staff|Supervisor|Manager|Admin|Operator|Intern|Magang|Designer|Marketing|Sales|Accountant)[^\n\r,]*)/i', $line, $matchPos2)) {
                    $current['posisi'] = trim($matchPos2[1]);
                } elseif (!$current['instansi'] && strlen($line) < 40 && !strpos($line, '•') && !strpos($line, '-')) {
                    $current['instansi'] = trim($line);
                }
            }
        }

        if ($current && (!empty($current['instansi']) || !empty($current['posisi']))) {
            $items[] = $current;
        }

        return $items;
    }

    private function parseOrganizationSection($sectionText)
    {
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $sectionText)), function ($l) {
            return strlen($l) > 0;
        }));

        foreach ($lines as $line) {
            if (preg_match('/(?:(20[0-2][0-9]|19[8-9][0-9]))/i', $line, $matchYear)) {
                $items[] = [
                    'nama' => trim(preg_replace('/\b(20[0-2][0-9]|19[8-9][0-9])\b.*$/', '', $line)) ?: trim($line),
                    'tahun' => $matchYear[1],
                    'jenis_org_id' => null,
                    'kota_id' => null,
                    'posisi' => 'Anggota / Pengurus',
                    'desc' => null
                ];
            }
        }
        return $items;
    }

    private function parseTrainingSection($sectionText)
    {
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $sectionText)), function ($l) {
            return strlen($l) > 0;
        }));

        foreach ($lines as $line) {
            preg_match('/(?:(20[0-2][0-9]|19[8-9][0-9]))/i', $line, $matchYear);
            $items[] = [
                'nama_pel' => trim($line),
                'tahun' => $matchYear[1] ?? date('Y'),
                'nama_lem' => '-',
                'kota_id' => null
            ];
        }
        return $items;
    }

    private function parseLanguageSection($sectionText)
    {
        $items = [];
        if (preg_match('/\b(Inggris|English)\b/i', $sectionText)) {
            $items[] = [
                'bhs_dikuasai' => 'Bahasa Inggris',
                'nilai_lisan' => 'Aktif',
                'nilai_tertulis' => 'Aktif'
            ];
        }
        if (preg_match('/\b(Indonesia)\b/i', $sectionText)) {
            $items[] = [
                'bhs_dikuasai' => 'Bahasa Indonesia',
                'nilai_lisan' => 'Aktif',
                'nilai_tertulis' => 'Aktif'
            ];
        }
        if (preg_match('/\b(Mandarin|Jepang|Arab|Jerman)\b/i', $sectionText, $matchOtherLang)) {
            $items[] = [
                'bhs_dikuasai' => 'Bahasa ' . ucfirst($matchOtherLang[1]),
                'nilai_lisan' => 'Pasif',
                'nilai_tertulis' => 'Pasif'
            ];
        }
        return $items;
    }

    private function parseIndoDate($dateStr)
    {
        $months = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'agu' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09',
            'oktober' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12', 'dec' => '12'
        ];

        // Format DD-MM-YYYY atau DD/MM/YYYY
        if (preg_match('/^([0-9]{1,2})[\s\/\-\.]([0-9]{1,2})[\s\/\-\.]([0-9]{4})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Format YYYY-MM-DD
        if (preg_match('/^([0-9]{4})[\s\/\-\.]([0-9]{1,2})[\s\/\-\.]([0-9]{1,2})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // Format DD Bulan YYYY (contoh: 17 Agustus 1995)
        if (preg_match('/^([0-9]{1,2})[\s\/\-\.]([a-zA-Z]+)[\s\/\-\.]([0-9]{4})$/', $dateStr, $m)) {
            $monthKey = strtolower($m[2]);
            $monthNum = $months[$monthKey] ?? '01';
            return sprintf('%04d-%02d-%02d', $m[3], $monthNum, $m[1]);
        }

        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function custom_importPelamar($req)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,xls,csv'
        // ]);

        DB::beginTransaction();

        try {
            if (!$req->hasFile('file')) {
                return response()->json(['error' => 'File tidak ditemukan'], 422);
            }

            $file = $req->file('file');

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
                return response()->json(['error' => 'Format file harus CSV atau Excel (xls/xlsx)'], 422);
            }

            $rows = Excel::toArray([], $file)[0];
            array_shift($rows); // Hapus header baris pertama

            foreach ($rows as $row) {

                $fullName = trim($row[0]);
                $nameParts = explode(' ', $fullName);
                $firstName = $nameParts[0];
                $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;

                $l = m_general::where('group', 'JENIS KELAMIN')->where('value', 'Laki-Laki')->first()?->id ?? null;
                $p = m_general::where('group', 'JENIS KELAMIN')->where('value', 'Perempuan')->first()?->id ?? null;

                $jkInput = strtoupper(trim($row[5]));
                $jkId = ($jkInput == 'L') ? $l : (($jkInput == 'P') ? $p : null);

                //cek blacklist
                $ktpNo = (string) $row[1];
                $existingPelamar = t_pelamar::where('ktp_no', $ktpNo)->first();
                $newStatus = ($existingPelamar && $existingPelamar->status === 'blacklist')
                    ? 'blacklist'
                    : 'aktif';

                $pelamar = t_pelamar::updateOrCreate(
                    ['ktp_no' => (string) $row[1]],
                    [
                        'nomor' => $this->helper->generateNomor("KODE PELAMAR"),
                        'nama_lengkap' => $fullName,
                        'nama_depan' => $firstName,
                        'nama_belakang' => $lastName,
                        'nama_panggilan' => $row[2],
                        'ktp_no' => (string) $row[1],
                        'telp' => $row[3],
                        'email' => $row[4],
                        'jk_id' => $jkId,
                        'tempat_lahir' => $row[6],
                        'tgl_lahir' => $this->formatDateExcel($row[7]),
                        'tanggal' => Carbon::now(),
                        'ig' => $row[8],
                        'linkedin' => $row[9],
                        'status' => $newStatus,
                        'creator_id' => auth()->id(),
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data pelamar berhasil diimport']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper untuk handle format tanggal dari Excel
     */
    private function formatDateExcel($value)
    {
        if (!$value)
            return null;
        try {
            // Jika formatnya angka (excel serial date)
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            // Jika formatnya string YYYY-MM-DD
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function fetchData($request)
    {
        if (!$request->hasFile("file")) {
            return response()->json("file harus ada", 400);
        }

        $sheets = ["t_pelamar", "t_pelamar_pend", "t_pelamar_peng"];
        $jsonResults = [];

        foreach ($sheets as $sheet) {
            try {
                $import = new YourImportClass(); // Adjust with the correct namespace
                $import->setSheet($sheet);
                Excel::import(
                    $import,
                    $request->file("file")->getRealPath(),
                    null,
                    \Maatwebsite\Excel\Excel::XLSX,
                    $sheet
                );
                $jsonResults[$sheet] = $import->getResponse();
                $import->resetResponse();
            } catch (\Exception $e) {
                // Handle exception, for example, log error messages
                return response()->json(
                    "Error importing data from sheet {$sheet}: " .
                    $e->getMessage()
                );
            }
        }

        return $jsonResults;
    }

    public function custom_generate_import($request)
    {
        $response = $this->fetchData($request);
        $data = $response["t_pelamar"]->getData(true);
        $detData = $response["t_pelamar_peng"]->getData(true);
        $detData1 = $response["t_pelamar_pend"]->getData(true);
        $mappedData = [];
        foreach ($data as $header) {
            $jk = m_general::where("id", $header["jk_id"])->value("value");
            $mappedHeader = [
                "nomor" => $header["nomor"],
                "m_comp_id" => $header["m_comp_id"],
                "m_dir_id" => $header["m_dir_id"],
                "m_divisi_id" => $header["m_divisi_id"],
                "m_dept_id" => $header["m_dept_id"],
                "m_posisi_id" => $header["m_posisi_id"],
                "nama_pelamar" => $header["nama_pelamar"],
                "ktp_no" => $header["ktp_no"],
                "tanggal" => $header["tanggal"],
                "ref" => $header["ref"],
                "telp" => $header["telp"],
                "jk_id" => $header["jk_id"],
                "jk" => $jk,
                "tempat_lahir" => $header["tempat_lahir"],
                "tgl_lahir" => $header["tgl_lahir"],
                "salary" => $header["salary"],
                "deskripsi" => $header["deskripsi"],
                "status" => $header["status"],
                "t_pelamar_det_peng" => [],
                "t_pelamar_det_pend" => [],
            ];

            foreach ($detData as $detailPeng) {
                if ($detailPeng["pelamar_temp_id"] == $header["temp_id"]) {
                    $mappedDetail = [
                        "nama_pengalaman" =>
                            $detailPeng["nama_pengalaman"] ?? null,
                        "posisi" => $detailPeng["posisi"] ?? null,
                        "date_from" => $detailPeng["date_from"] ?? null,
                        "date_to" => $detailPeng["date_to"] ?? null,
                        "kota_id" => $detailPeng["kota_id"] ?? null,
                        "keterangan" => $detailPeng["keterangan"] ?? null,
                        "is_active" => $detailPeng["is_active"] ?? null,
                        "creator_id" => $detailPeng["creator_id"] ?? null,
                        "last_editor_id" =>
                            $detailPeng["last_editor_id"] ?? null,
                    ];
                    $mappedHeader["t_pelamar_det_peng"][] = $mappedDetail;
                }
            }

            foreach ($detData1 as $detailPend) {
                if ($detailPend["pelamar_temp_id"] == $header["temp_id"]) {
                    $mappedDetail = [
                        "tingkat_id" => $detailPend["tingkat_id"] ?? null,
                        "nama_sekolah" => $detailPend["nama_sekolah"] ?? null,
                        "tahun_masuk" => $detailPend["tahun_masuk"] ?? null,
                        "tahun_lulus" => $detailPend["tahun_lulus"] ?? null,
                        "kota_id" => $detailPend["kota_id"] ?? null,
                        "nilai" => $detailPend["nilai"] ?? null,
                        "jurusan" => $detailPend["jurusan"] ?? null,
                        "is_pend_terakhir" =>
                            $detailPend["is_pend_terakhir"] ?? null,
                        "ijazah_no" => $detailPend["ijazah_no"] ?? null,
                        "ijazah_foto" => $detailPend["ijazah_foto"] ?? null,
                        "keterangan" => $detailPend["keterangan"] ?? null,
                        "is_active" => $detailPend["is_active"] ?? null,
                        "creator_id" => $detailPend["creator_id"] ?? null,
                        "last_editor_id" =>
                            $detailPend["last_editor_id"] ?? null,
                    ];
                    $mappedHeader["t_pelamar_det_pend"][] = $mappedDetail;
                }
            }
            $mappedData[] = $mappedHeader;
        }

        return $mappedData;
    }

    public function custom_saveExcel($req)
    {
        $data = $req->all();
        if (!$data) {
            return response()->json(["errors" => "Data Tidak Terbaca"], 422);
        }

        try {
            \DB::beginTransaction();
            foreach ($data as $datas) {
                if (is_array($datas)) {
                    $pelamar = t_pelamar::create([
                        "nomor" => $datas["nomor"] ?? null,
                        "m_comp_id" => $datas["m_comp_id"] ?? null,
                        "m_dir_id" => $datas["m_dir_id"] ?? null,
                        "m_divisi_id" => $datas["m_divisi_id"] ?? null,
                        "m_dept_id" => $datas["m_dept_id"] ?? null,
                        "m_posisi_id" => $datas["m_posisi_id"] ?? null,
                        "nama_pelamar" => $datas["nama_pelamar"] ?? null,
                        "ktp_no" => $datas["ktp_no"] ?? null,
                        "tanggal" => $datas["tanggal"] ?? null,
                        "ref" => $datas["ref"] ?? null,
                        "telp" => $datas["telp"] ?? null,
                        "jk_id" => $datas["jk_id"] ?? null,
                        "tempat_lahir" => $datas["tempat_lahir"] ?? null,
                        "tgl_lahir" => $datas["tgl_lahir"] ?? null,
                        "salary" => $datas["salary"] ?? null,
                        "deskripsi" => $datas["deskripsi"] ?? null,
                        "status" => $datas["status"] ?? null,
                    ]);

                    foreach ($datas["t_pelamar_det_peng"] as $detPeng) {
                        t_pelamar_det_peng::create([
                            "t_pelamar_id" => $pelamar->id,
                            "nama_pengalaman" => $detPeng["nama_pengalaman"],
                            "posisi" => $detPeng["posisi"],
                            "date_from" => $detPeng["date_from"],
                            "date_to" => $detPeng["date_to"],
                            "kota_id" => $detPeng["kota_id"],
                            "keterangan" => $detPeng["keterangan"],
                            "is_active" => $detPeng["is_active"],
                        ]);
                    }

                    foreach ($datas["t_pelamar_det_pend"] as $detPend) {
                        t_pelamar_det_pend::create([
                            "t_pelamar_id" => $pelamar->id,
                            "tingkat_id" => $detPend["tingkat_id"],
                            "nama_sekolah" => $detPend["nama_sekolah"],
                            "tahun_masuk" => $detPend["tahun_masuk"],
                            "tahun_lulus" => $detPend["tahun_lulus"],
                            "kota_id" => $detPend["kota_id"],
                            "nilai" => $detPend["nilai"],
                            "jurusan" => $detPend["jurusan"],
                            "is_pend_terakhir" => $detPend["is_pend_terakhir"],
                            "ijazah_no" => $detPend["ijazah_no"],
                            "ijazah_foto" => $detPend["ijazah_foto"],
                            "keterangan" => $detPend["keterangan"],
                            "is_active" => $detPend["is_active"],
                        ]);
                    }
                }
            }

            \DB::commit();

            return $this->helper->customResponse("Data berhasil disimpan");
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->helper->responseCatch($e);
        }
    }

    public function custom_importexcel($request)
    {
        if (!$request->hasFile("file")) {
            return response()->json("file harus ada", 400);
        }
        return _uploadexcel($this, $request);
    }

    public function public_pelamar($request)
    {
        \DB::beginTransaction();

        try {
            $data = $request->all();

            if (is_array($data)) {
                $pelamar = t_pelamar::create([
                    "nomor" => $this->helper->generateNomor("KODE PELAMAR") ?? $data["nomor"],
                    "m_comp_id" => $data["m_comp_id"] ?? null,
                    "m_dir_id" => $data["m_dir_id"] ?? null,
                    "m_divisi_id" => $data["m_divisi_id"] ?? null,
                    "m_dept_id" => $data["m_dept_id"] ?? null,
                    "m_posisi_id" => $data["m_posisi_id"] ?? null,
                    "nama_pelamar" => $data["nama_pelamar"] ?? null,
                    "ktp_no" => $data["ktp_no"] ?? null,
                    "tanggal" => $data["tanggal"] ?? null,
                    "ref" => $data["ref"] ?? null,
                    "telp" => $data["telp"] ?? null,
                    "jk_id" => m_general::where('value', $data["jk_id"])->value('id') ?? 0,
                    "tempat_lahir" => $data["tempat_lahir"] ?? null,
                    "tgl_lahir" => $data["tgl_lahir"] ?? null,
                    "salary" => $data["salary"] ?? null,
                    "deskripsi" => $data["deskripsi"] ?? null,
                    "status" => $data["status"] ?? null,
                ]);
                if (isset($data["t_pelamar_det_peng"])) {
                    foreach ($data["t_pelamar_det_peng"] as $detPeng) {
                        t_pelamar_det_peng::create([
                            "t_pelamar_id" => $pelamar->id,
                            "nama_pengalaman" => $detPeng["nama_pengalaman"],
                            "posisi" => $detPeng["posisi"],
                            "date_from" => $detPeng["date_from"],
                            "date_to" => $detPeng["date_to"],
                            "kota_id" => m_general::where('value', $detPeng["kota_id"])->value('id') ?? 0,
                            "keterangan" => $detPeng["keterangan"],
                            "is_active" => $detPeng["is_active"],
                        ]);
                    }
                }
                if (isset($data["t_pelamar_det_pend"])) {
                    foreach ($data["t_pelamar_det_pend"] as $detPend) {
                        t_pelamar_det_pend::create([
                            "t_pelamar_id" => $pelamar->id,
                            "tingkat_id" => m_general::where('value', $detPend["tingkat_id"])->value('id') ?? 0,
                            "nama_sekolah" => $detPend["nama_sekolah"],
                            "tahun_masuk" => $detPend["tahun_masuk"],
                            "tahun_lulus" => $detPend["tahun_lulus"],
                            "kota_id" => m_general::where('value', $detPend["kota_id"])->value('id') ?? 0,
                            "nilai" => $detPend["nilai"],
                            "jurusan" => $detPend["jurusan"],
                            "is_pend_terakhir" => $detPend["is_pend_terakhir"],
                            "ijazah_no" => $detPend["ijazah_no"],
                            // "ijazah_foto" => $detPend["ijazah_foto"] ?? null,
                            "keterangan" => $detPend["keterangan"],
                            "is_active" => $detPend["is_active"],
                        ]);
                    }
                }
                if (isset($data["t_pelamar_det_pel"])) {
                    foreach ($data["t_pelamar_det_pel"] as $detPel) {
                        t_pelamar_det_pel::create([
                            "t_pelamar_id" => $pelamar->id,
                            "nama_pel" => $detPel['nama_pel'],
                            "tahun" => $detPel['tahun'],
                            "nama_lem" => $detPel['nama_lem'],
                            "kota_id" => m_general::where('value', $detPel["kota_id"])->value('id') ?? 0,
                        ]);
                    }
                }

                if (isset($data["t_pelamar_det_org"])) {
                    foreach ($data["t_pelamar_det_org"] as $detOrg) {
                        t_pelamar_det_org::create([
                            "t_pelamar_id" => $pelamar->id,
                            "nama" => $detOrg['nama'],
                            "tahun" => $detOrg['tahun'],
                            // "jenis_org_id"=> m_general::where('value', $detPend["jenis_org_id"])->value('value') ?? 0,
                            "kota_id" => m_general::where('value', $detOrg["kota_id"])->value('id') ?? 0,
                            "posisi" => $detOrg['posisi'],
                            "desc" => $detOrg['desc'],
                        ]);
                    }
                }

                if (isset($data["t_pelamar_det_bhs"])) {
                    foreach ($data["t_pelamar_det_bhs"] as $detBhs) {
                        t_pelamar_det_bhs::create([
                            "t_pelamar_id" => $pelamar->id,
                            "bhs_dikuasai" => $detBhs['bhs_dikuasai'],
                            "nilai_lisan" => $detBhs['nilai_lisan'] ?? null,
                            "level_lisan" => $detBhs('level_lisan') ?? null,
                            "nilai_tertulis" => $detBhs['nilai_tertulis'] ?? null,
                            "level_tertulis" => $detBhs('level_tertulis') ?? null,
                            "desc" => $detBhs['desc'],
                        ]);
                    }
                }

                if (isset($data["t_pelamar_det_pres"])) {
                    foreach ($data["t_pelamar_det_pres"] as $detPres) {
                        t_pelamar_det_pres::create([
                            "t_pelamar_id" => $pelamar->id,
                            "nama_pres" => $detPres['nama_pres'],
                            "tahun" => $detPres['tahun'],
                            "tingkat_pres_id" => m_general::where('value', $detPres["tingkat_pres_id"])->value('id') ?? 0,
                            "desc" => $detPres['desc'],
                        ]);
                    }
                }


                \DB::commit(); // Commit transaksi jika semuanya berhasil
            }

        } catch (\Exception $e) {
            \DB::rollback(); // Rollback transaksi jika terjadi kesalahan
            throw $e; // Lepaskan exception setelah rollback
        }
    }
}
