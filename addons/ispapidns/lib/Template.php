<?php

namespace HEXONET\WHMCS\ISPAPI\DNS;

use Illuminate\Database\Capsule\Manager as DB;

class Template
{
    /**
     * @return \Illuminate\Support\Collection<mixed>
     */
    public static function getAll(): \Illuminate\Support\Collection
    {
        return DB::table('mod_ispapi_dns_templates')->get();
    }

    /**
     * @param string $domain
     * @return string
     */
    public static function getForDomain(string $domain): string
    {
        $productId = DB::table('tblhosting')
            ->where('domain', '=', $domain)
            ->value('id');
        if ($productId) {
            $templateId = DB::table('mod_ispapi_dns_products')
                ->where('product_id', '=', $productId)
                ->value('template_id');
            if ($templateId) {
                return DB::table('mod_ispapi_dns_templates')
                    ->where('id', '=', $templateId)
                    ->value('zone');
            }
        }
        return self::getDefault();
    }

    /**
     * @return string
     */
    public static function getDefault(): string
    {
        return DB::table('mod_ispapi_dns_templates')
            ->where('default', '=', true)
            ->value('zone');
    }

    /**
     * @param int $templateId
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function get(int $templateId)
    {
        return DB::table('mod_ispapi_dns_templates AS t')
            ->where('t.id', '=', $templateId)
            ->leftJoin('mod_ispapi_dns_products AS p', 'p.template_id', '=', 't.id')
            ->get(['t.*', 'p.product_id']);
    }

    /**
     * @param string $domain
     * @return string
     */
    public static function getIp(string $domain): string
    {
        $result = DB::table('tblhosting AS h')
            ->leftJoin('tblservers AS s', 's.id', '=', 'h.server')
            ->where('h.domain', '=', $domain)
            ->first(['h.dedicatedip', 's.ipaddress']);
        // @phpstan-ignore-next-line
        if (!$result->dedicatedip) {
            // @phpstan-ignore-next-line
            return $result->ipaddress;
        }
        // @phpstan-ignore-next-line
        return $result->dedicatedip;
    }

    /**
     * @return int
     */
    public static function create(): int
    {
        $templateId = DB::table('mod_ispapi_dns_templates')
            ->insertGetId([
                'name' => @$_POST['name'],
                'zone' => @$_POST['zone'],
                'default' => @$_POST['default'] == 'true'
            ]);

        self::setDefaults($templateId);

        return $templateId;
    }

    /**
     * @param int $templateId
     * @return int
     */
    public static function edit(int $templateId): int
    {
        $updated = DB::table('mod_ispapi_dns_templates')
            ->where('id', '=', $templateId)
            ->update([
                'name' => @$_POST['name'],
                'zone' => html_entity_decode(@$_POST['zone']),
                'default' => @$_POST['default'] == 'true'
            ]);

        self::setDefaults($templateId);

        return $updated;
    }

    /**
     * @param int $templateId
     */
    private static function setDefaults(int $templateId): void
    {
        if (@$_POST['default'] == 'true') {
            DB::table('mod_ispapi_dns_templates')
                ->where('id', '!=', $templateId)
                ->update(['default' => 0]);
        }

        if (!@$_POST['products']) {
            return;
        }

        DB::table('mod_ispapi_dns_products')
            ->where('template_id', '=', $templateId)
            ->whereNotIn('product_id', $_POST['products'])
            ->delete();

        DB::table('mod_ispapi_dns_products')
            ->where('template_id', '!=', $templateId)
            ->whereIn('product_id', $_POST['products'])
            ->delete();

        foreach ($_POST['products'] as $productId) {
            DB::table('mod_ispapi_dns_products')
                ->insertOrIgnore([
                    'template_id' => $templateId,
                    'product_id' => $productId
                ]);
        }
    }

    /**
     * @param int $templateid
     * @return int
     */
    public static function delete(int $templateid): int
    {
        return DB::table('mod_ispapi_dns_templates')
            ->where('id', '=', $templateid)
            ->delete();
    }
}
