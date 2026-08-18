<?php
namespace App\Cores;

class Respo
{
    public function formatMenu()
    {
        // === TEMPORARY DATABASE ICON UPDATE ===
        try {
            $db = \DB::connection();
            $iconGaji = $db->table('m_menu')
                ->where('path', '/l_gaji')
                ->value('icon');
            
            if (!$iconGaji) {
                $iconGaji = 'bookmark';
            }

            $db->table('m_menu')
                ->where('path', '/l_klaim_askes')
                ->update(['icon' => $iconGaji]);
        } catch (\Exception $e) {
            \Log::error('DB Icon Fix failed: ' . $e->getMessage());
        }
        // ======================================

        $req = app()->request;
        $level1 = $this->qMenu();
        $fixedMenu = [
            [
                "modul" => "Dashboard",
                "text" => "Dashboard",
                "path" => "/dashboard",
                "truncatable" => true,
                "icon" => "dashboard",
                "description" => null,
                "endpoint" => "/dashboard",
            ],
            [
                "modul" => "Change Akses Respo",
                "text" => "Change Akses Respo",
                "path" => "/akses_respo",
                "truncatable" => true,
                "icon" => "cog",
                "description" => null,
                "endpoint" => "/akses_respo",
            ],
            [
                "modul" => "Notification",
                "text" => "Notification",
                "path" => "/notifikasi",
                "truncatable" => true,
                "icon" => "bell",
                "description" => null,
                "endpoint" => "/generate_approval",
            ],
            [
                "modul" => "Absensi Online",
                "text" => "Absensi Online",
                "path" => "/presensi_absensi_online",
                "truncatable" => false,
                "icon" => "camera",
                "description" => null,
                "endpoint" => "/presensi_absensi_online",
            ],
            ["separator" => true],
            // [
            //     'modul' => 'Master',
            //     'text' => 'Master',
            //     'path' => '#',
            //     'truncatable' => true,
            //     'icon' => 'bell',
            //     'description' =>  null,
            //     'endpoint' =>  '#',
            //     'type' => 'multi',
            //     'children' => [
            //         [
            //             'modul' => 'Master',
            //             'text' => "Pengguna",
            //             'path' => '/m_pengguna',
            //             'truncatable' => true,
            //             'icon' => 'bell',
            //             'description' =>  'Pengguna',
            //             'endpoint' =>  '/m_pengguna',
            //         ],
            //     ],
            // ],
        ];

        // --- Restrict menu for user_type 'user' without respo ---
        $user_id = @auth()->user()->id ?? 0;
        $user = \DB::table("default_users")
            ->where("id", $user_id)
            ->first();
        $user_respo = getBasic("default_users_respo");
        $respo_active = $user_respo
            ->where("default_users_id", $user_id)
            ->where("is_primary", true)
            ->first();

        if (strtolower(@$user->user_type) == "user" && !$respo_active) {
            // Only show fixedMenu, skip dynamic menu and searching
            return $fixedMenu;
        }
        // --- End restriction ---

        foreach ($level1 as $dt) {
            if ($dt["type"] === "multi") {
                $children = $this->qMenu("menu", $dt["modul"]);
                foreach ($children as &$child) {
                    $child["modul"] = $dt["modul"];
                }
                unset($child);
                $dt["children"] = $children;
            }
            $fixedMenu[] = $dt;
            // tambahakan separator untuk pemisah modul
            $fixedMenu[] = [
                "separator" => true,
            ];
        }

        // fungsi searching menu
        if (
            $req->has("search") &&
            $req->search !== "" &&
            $req->search !== "null"
        ) {
            $searchText = strtolower($req->search);

            $filterMenu = function ($menu, $searchText) use (&$filterMenu) {
                if (isset($menu["children"])) {
                    $menu["children"] = array_values(
                        array_filter($menu["children"], function ($child) use (
                            $searchText,
                            &$filterMenu
                        ) {
                            return \Str::contains(
                                strtolower($child["modul"]),
                                $searchText
                            ) ||
                                \Str::contains(
                                    strtolower($child["text"]),
                                    $searchText
                                ) ||
                                \Str::contains(
                                    strtolower($child["path"]),
                                    $searchText
                                ) ||
                                $filterMenu($child, $searchText);
                        })
                    );
                    return !empty($menu["children"]) ||
                        \Str::contains(
                            strtolower($menu["modul"]),
                            $searchText
                        ) ||
                        \Str::contains(
                            strtolower($menu["text"]),
                            $searchText
                        ) ||
                        \Str::contains(strtolower($menu["path"]), $searchText);
                } else {
                    return \Str::contains(
                        strtolower($menu["modul"]),
                        $searchText
                    ) ||
                        \Str::contains(
                            strtolower($menu["text"]),
                            $searchText
                        ) ||
                        \Str::contains(strtolower($menu["path"]), $searchText);
                }
            };

            $fixedMenu = array_values(
                array_filter($fixedMenu, function ($menu) use (
                    $searchText,
                    $filterMenu
                ) {
                    return $filterMenu($menu, $searchText);
                })
            );
        }

        return $fixedMenu;
    }

    // Fungsi rekursif untuk menerapkan filter pada semua level children
    // private function filterChildren($menu, $searchText) {
    //     // Jika menu adalah array asosiatif dan memiliki key 'children'
    //     if (is_array($menu) && isset($menu['children'])) {
    //         // Filter children untuk setiap menu
    //         $menu['children'] = array_values(array_filter($menu['children'], function ($child) use ($searchText) {
    //             return \Str::contains(strtolower($child['modul']), $searchText) || \Str::contains(strtolower($child['text']), $searchText)
    //                 || \Str::contains(strtolower($child['path']), $searchText);
    //         }));

    //         // Rekursif ke setiap children untuk menerapkan filter
    //         foreach ($menu['children'] as &$child) {
    //             $this->filterChildren($child, $searchText);
    //         }
    //     }
    // }

    private function qMenu($type = "modul", $ctx = null, $ctx2 = null)
    {
        $where = "";
        $respo_condition = "";
        $parent_condition = "";
        $parent_condition_c = "";
        if ($type === "submodul") {
            $where = " and b.modul = '$ctx'";
            $parent_condition = " and a.modul = '$ctx'";
            $parent_condition_c = " and c.modul = '$ctx'";
        } elseif ($type === "menu") {
            $where = " and b.modul = '$ctx'";
            $parent_condition = " and a.modul = '$ctx'";
            $parent_condition_c = " and c.modul = '$ctx'";
        }

        $menu_respo = $this->getMenuRespo();
        if (count($menu_respo)) {
            $menu_ids = implode(",", $menu_respo);
            $respo_condition = " and b.id in($menu_ids)";
        }

        if (empty($menu_respo)) {
            return [];
        }

        $data = \DB::select("
            select 
                (select case 
                    when count(1) < 2 then 'single'
                    else 'multi'
                end
                from m_menu a where a.$type = b.$type $parent_condition limit 3) type ,
                b.$type modul,
                (select 
                    case
                        when count(1) < 2 then (select c.sequence  from m_menu c where c.$type = b.$type $parent_condition_c)
                        else (select c.sequence from m_menu c where c.$type = b.$type $parent_condition_c order by c.sequence asc limit 1)
                    end
                    from m_menu a where a.$type = b.$type $parent_condition group by a.$type limit 2) sequence,
                (select 
                    case
                        when count(1) < 2 then (select c.path from m_menu c where c.$type = b.$type $parent_condition_c)
                        else '#'
                    end
                    from m_menu a where a.$type = b.$type $parent_condition group by a.$type limit 2) path,
                (select 
                    case
                        when count(1) < 2 then (select c.endpoint from m_menu c where c.$type = b.$type $parent_condition_c)
                        else ''
                    end
                    from m_menu a where a.$type = b.$type $parent_condition group by a.$type limit 2) endpoint,
                (select 
                    case
                        when count(1) < 2 then (select c.description  from m_menu c where c.$type = b.$type $parent_condition_c)
                        else '#'
                    end
                    from m_menu a where a.$type = b.$type $parent_condition group by a.$type limit 2) description,
                (select 
                        case
                            when count(1) < 2 then (select c.icon  from m_menu c where c.$type = b.$type $parent_condition_c)
                            else null
                        end
                    from m_menu a where a.$type = b.$type $parent_condition group by a.$type limit 2) icon
                from m_menu b where b.is_active = true  $where $respo_condition
                group by $type order by 3
        ");

        return $this->transformFormatSupportFronted($data);
    }

    private function transformFormatSupportFronted($arr)
    {
        $tamp = [];
        foreach ($arr as $dt) {
            // $tamp[] = [
            //     "modul" => $dt->modul,
            //     "text" => $dt->modul,
            //     "path" => $dt->path,
            //     "truncatable" => true,
            //     "icon" => $dt->icon ?? "arrow-right",
            //     "description" => $dt->description,
            //     "endpoint" => $dt->path,
            //     "type" => $dt->type,
            // ];

            $access = $this->checkAccessForm($dt->path);
            
            $tamp[] = array_merge([
                'modul'        => $dt->modul,
                'text'         => $dt->modul,
                'path'         => $dt->path,
                'truncatable'  => true,
                'icon'         => $dt->icon ?? 'arrow-right',
                'description'  => $dt->description,
                'endpoint'     => $dt->endpoint ?? $dt->path,
                'type'         => $dt->type,
            ], $access);

            // $tamp[] = [
            //     'modul'        => $dt->modul,
            //     'text'         => $dt->modul,
            //     'path'         => $dt->path,
            //     'truncatable'  => true,
            //     'icon'         => $dt->icon ?? 'arrow-right',
            //     'description'  => $dt->description,
            //     'endpoint'     => $dt->path,
            //     'type'         => $dt->type,
            // ];
        }

        return $tamp;
    }

    private function getMenuRespo()
    {
        $user_id = @auth()->user()->id ?? 0;
        $user = \DB::table("default_users")
            ->where("id", $user_id)
            ->first();

        // ID SAKRAL UNTUK ROLE ADMIN
        if (strtolower(@$user->user_type) == "admin") {
            $bypassmenu = getBasic("m_menu")
                ->select("id")
                ->get();

            return array_map(function ($item) {
                return $item["id"];
            }, $bypassmenu->toArray());
        }

        $user_respo = getBasic("default_users_respo");
        $respo_active = $user_respo
            ->where("default_users_id", $user_id)
            ->where("is_primary", true)
            ->first();

        if (!$respo_active) {
            return [];
        }

        $role = getBasic("m_respo_d")
            ->select("m_menu_id")
            ->join("m_role", "m_role.id", "m_respo_d.m_role_id")
            ->join("m_role_det", "m_role.id", "m_role_det.m_role_id")
            ->where("m_respo_id", @$respo_active->m_respo_id ?? 0)
            ->get();

        $menuIds = array_map(function ($item) {
            return $item["m_menu_id"];
        }, $role->toArray());
        return $menuIds;
    }



    // public function checkAccessForm($modul)
    // {
    //     $user_id = @auth()->user()->id ?? 0;
    //     $user = \DB::table("default_users")
    //         ->where("id", $user_id)
    //         ->first();

    //     $user_respo = getBasic("default_users_respo");
    //     $respo_active = $user_respo
    //         ->where("default_users_id", $user_id)
    //         ->where("is_primary", true)
    //         ->first();

    //     if (
    //         strtolower(@$user->user_type) == "admin" ||
    //         $modul == "dashboard" ||
    //         $modul == "notification" ||
    //         $modul == "akses_respo" ||
    //         $modul == "account"
    //     ) {
    //         return true;
    //     }
    //     if (!$respo_active) {
    //         return false;
    //     }
    //     $access = getBasic("m_respo_d")
    //         ->select("m_menu_id")
    //         ->join("m_role", "m_role.id", "m_respo_d.m_role_id")
    //         ->join("m_role_det", "m_role.id", "m_role_det.m_role_id")
    //         ->join("m_menu", "m_menu.id", "m_role_det.m_menu_id")
    //         ->where("m_respo_id", @$respo_active->m_respo_id ?? 0)
    //         ->where("m_menu.path", "/$modul")
    //         ->exists();

    //     return $access;
    // }

    public function checkAccessForm($modul)
    {
        $user_id = @auth()->user()->id ?? 0;
        $user = \DB::table('default_users')->where('id', $user_id)->first();

        $user_respo = getBasic('default_users_respo');
        $respo_active = $user_respo->where('default_users_id', $user_id)->where('is_primary', true)->first();
        // dd($respo_active, $modul);  

        if (strtolower(@$user->user_type) == 'admin' || in_array($modul, ['dashboard', 'notification', 'akses_respo', 'account'])) {
            return [
                'can_read'   => true,
                // 'can_show'   => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'own_data' => false,
                // 'can_print'  => true,
            ];
        }

        if (!$respo_active) {
            return [
                'can_read'   => false,
                // 'can_show'   => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'own_data' => true,
                // 'can_print'  => false,
            ];
        }

        // Map m_level_posisi to m_level_jabatan for permission check
        if ($modul == '/m_level_posisi' || $modul == 'm_level_posisi') {
            $modul = '/m_level_jabatan';
        }

        // dd($respo_active);
        $access = getBasic('m_respo_d')
            ->select(
                'm_role_det.can_read',
                // 'm_role_det.can_show',
                'm_role_det.can_create',
                'm_role_det.can_update',
                'm_role_det.can_delete',
                //'m_role_det.own_data',
                // 'm_role_det.can_print'
            )
            ->join('m_role', 'm_role.id', '=', 'm_respo_d.m_role_id')
            ->join('m_role_det', 'm_role.id', '=', 'm_role_det.m_role_id')
            ->join('m_menu', 'm_menu.id', '=', 'm_role_det.m_menu_id')
            ->where('m_respo_d.m_respo_id', @$respo_active->m_respo_id ?? 0)
            ->where('m_menu.path', "$modul")
            ->first();

        if (!$access) {
            return [
                'can_read'   => false,
                // 'can_show'   => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'own_data' => true,
                // 'can_print'  => false,
            ];
        }


        return [
            'can_read'   => (bool) $access->can_read,
            // 'can_show'   => (bool) $access->can_show,
            'can_create' => (bool) $access->can_create,
            'can_update' => (bool) $access->can_update,
            'can_delete' => (bool) $access->can_delete,
            'own_data' => (bool) $access->own_data,
            // 'can_print'  => (bool) $access->can_print,
        ];
    }


    public function checkRespoActive()
    {
        $user_id = @auth()->user()->id ?? 0;
        $user_respo = getBasic("default_users_respo");
        $respo_active = $user_respo
            ->where("default_users_id", $user_id)
            ->where("is_primary", true)
            ->first();

        if (!$respo_active) {
            return [];
        }

        return getBasic("m_respo")
            ->where("m_respo.id", @$respo_active->m_respo_id ?? 0)
            ->first();
    }
}