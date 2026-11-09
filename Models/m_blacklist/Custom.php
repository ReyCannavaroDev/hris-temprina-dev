<?php

namespace App\Models\CustomModels;

class m_blacklist extends \App\Models\BasicModels\m_blacklist
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function public_debugBlacklistColumns($req)
    {
        $table = 'm_blacklist';

        // 1. Struktur kolom: nama, tipe data, panjang (length), presisi, nullable, default
        $columns = \DB::select("
            SELECT
                c.table_schema,
                c.ordinal_position,
                c.column_name,
                c.data_type,
                c.udt_name,
                c.character_maximum_length,
                c.numeric_precision,
                c.numeric_scale,
                c.is_nullable,
                c.column_default
            FROM information_schema.columns c
            WHERE c.table_name = ?
            AND c.table_schema = ANY (current_schemas(false))
            ORDER BY c.ordinal_position
        ", [$table]);

        if (empty($columns)) {
            return response()->json(['error' => "Tabel {$table} tidak ditemukan"], 404);
        }

        // 2. Constraint (PK / UNIQUE / FK / CHECK) pada tabel
        $constraints = \DB::select("
            SELECT
                tc.constraint_name,
                tc.constraint_type,
                kcu.column_name
            FROM information_schema.table_constraints tc
            LEFT JOIN information_schema.key_column_usage kcu
                ON kcu.constraint_name = tc.constraint_name
                AND kcu.table_schema    = tc.table_schema
            WHERE tc.table_name = ?
            AND tc.table_schema = ANY (current_schemas(false))
            ORDER BY tc.constraint_type, kcu.ordinal_position
        ", [$table]);

        // 3. Index pada tabel
        $indexes = \DB::select("
            SELECT indexname, indexdef
            FROM pg_indexes
            WHERE tablename = ?
            AND schemaname = ANY (current_schemas(false))
        ", [$table]);

        // 4a. Relasi KELUAR: m_blacklist mereferensi tabel lain (FK milik m_blacklist)
        $relasiKeluar = \DB::select("
            SELECT
                con.conname                              AS constraint_name,
                att.attname                              AS kolom_ini,
                fns.nspname || '.' || fcl.relname        AS tabel_referensi,
                fatt.attname                             AS kolom_referensi,
                CASE con.confdeltype
                    WHEN 'a' THEN 'NO ACTION' WHEN 'r' THEN 'RESTRICT'
                    WHEN 'c' THEN 'CASCADE'   WHEN 'n' THEN 'SET NULL'
                    WHEN 'd' THEN 'SET DEFAULT'
                END                                      AS on_delete,
                CASE con.confupdtype
                    WHEN 'a' THEN 'NO ACTION' WHEN 'r' THEN 'RESTRICT'
                    WHEN 'c' THEN 'CASCADE'   WHEN 'n' THEN 'SET NULL'
                    WHEN 'd' THEN 'SET DEFAULT'
                END                                      AS on_update
            FROM pg_constraint con
            JOIN pg_class cl        ON cl.oid  = con.conrelid
            JOIN pg_namespace ns    ON ns.oid  = cl.relnamespace
            JOIN pg_class fcl       ON fcl.oid = con.confrelid
            JOIN pg_namespace fns   ON fns.oid = fcl.relnamespace
            JOIN LATERAL unnest(con.conkey, con.confkey) AS k(attnum, fattnum) ON true
            JOIN pg_attribute att   ON att.attrelid  = con.conrelid  AND att.attnum  = k.attnum
            JOIN pg_attribute fatt  ON fatt.attrelid  = con.confrelid AND fatt.attnum = k.fattnum
            WHERE con.contype = 'f'
            AND cl.relname = ?
            AND ns.nspname = ANY (current_schemas(false))
            ORDER BY con.conname
        ", [$table]);

        // 4b. Relasi MASUK: tabel lain yang mereferensi m_blacklist (child table)
        $relasiMasuk = \DB::select("
            SELECT
                con.conname                              AS constraint_name,
                ns.nspname || '.' || cl.relname          AS tabel_child,
                att.attname                              AS kolom_child,
                fatt.attname                             AS kolom_ini,
                CASE con.confdeltype
                    WHEN 'a' THEN 'NO ACTION' WHEN 'r' THEN 'RESTRICT'
                    WHEN 'c' THEN 'CASCADE'   WHEN 'n' THEN 'SET NULL'
                    WHEN 'd' THEN 'SET DEFAULT'
                END                                      AS on_delete,
                CASE con.confupdtype
                    WHEN 'a' THEN 'NO ACTION' WHEN 'r' THEN 'RESTRICT'
                    WHEN 'c' THEN 'CASCADE'   WHEN 'n' THEN 'SET NULL'
                    WHEN 'd' THEN 'SET DEFAULT'
                END                                      AS on_update
            FROM pg_constraint con
            JOIN pg_class cl        ON cl.oid  = con.conrelid
            JOIN pg_namespace ns    ON ns.oid  = cl.relnamespace
            JOIN pg_class fcl       ON fcl.oid = con.confrelid
            JOIN pg_namespace fns   ON fns.oid = fcl.relnamespace
            JOIN LATERAL unnest(con.conkey, con.confkey) AS k(attnum, fattnum) ON true
            JOIN pg_attribute att   ON att.attrelid  = con.conrelid  AND att.attnum  = k.attnum
            JOIN pg_attribute fatt  ON fatt.attrelid  = con.confrelid AND fatt.attnum = k.fattnum
            WHERE con.contype = 'f'
            AND fcl.relname = ?
            AND fns.nspname = ANY (current_schemas(false))
            ORDER BY ns.nspname, cl.relname, con.conname
        ", [$table]);

        // 4c. Relasi implisit (tanpa FK): kolom berakhiran _id pada m_blacklist,
        //     dipakai untuk menebak tabel relasi yang tidak dibuatkan constraint FK
        $kolomIdTanpaFk = collect($columns)
            ->filter(fn($c) => preg_match('/_id$/', $c->column_name))
            ->pluck('column_name')
            ->diff(collect($relasiKeluar)->pluck('kolom_ini'))
            ->values();

        // 5. Jumlah baris & contoh 5 data teratas
        $totalRows = \DB::table($table)->count();
        $sample    = \DB::table($table)->limit(5)->get();

        // SATU return di akhir, setelah semua query dijalankan
        return response()->json([
            'table'        => $table,
            'total_columns' => count($columns),
            'total_rows'   => $totalRows,
            'columns'      => $columns,
            'constraints'  => $constraints,
            'indexes'      => $indexes,
            'relasi_keluar'     => $relasiKeluar,      // m_blacklist -> tabel lain
            'relasi_masuk'      => $relasiMasuk,       // tabel lain -> m_blacklist
            'kolom_id_tanpa_fk' => $kolomIdTanpaFk,    // kolom *_id yang tidak punya FK (kemungkinan relasi implisit)
            'sample_data'  => $sample,
        ]);
    }
    
}