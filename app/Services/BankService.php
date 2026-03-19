<?php

namespace App\Services;

class BankService
{
    /**
     * Lista de bancos brasileiros com código e nome
     */
    private static array $banks = [
        ['code' => '001', 'name' => 'Banco do Brasil'],
        ['code' => '033', 'name' => 'Santander'],
        ['code' => '104', 'name' => 'Caixa Econômica Federal'],
        ['code' => '237', 'name' => 'Bradesco'],
        ['code' => '341', 'name' => 'Itaú Unibanco'],
        ['code' => '389', 'name' => 'Banco Mercantil do Brasil'],
        ['code' => '422', 'name' => 'Banco Safra'],
        ['code' => '612', 'name' => 'Banco GuardCorp'],
        ['code' => '633', 'name' => 'Banco Rendimento'],
        ['code' => '654', 'name' => 'Banco A. J. Renner'],
        ['code' => '655', 'name' => 'Banco Votorantim'],
        ['code' => '707', 'name' => 'Banco Daycoval'],
        ['code' => '712', 'name' => 'Banco Ourinvest'],
        ['code' => '745', 'name' => 'Banco Citibank'],
        ['code' => '751', 'name' => 'Scotiabank'],
        ['code' => '752', 'name' => 'BanCoppel'],
        ['code' => '755', 'name' => 'Banco Boavista Interatlântico'],
        ['code' => '756', 'name' => 'Banco Cooperativo Sicredi'],
        ['code' => '757', 'name' => 'Banco Volkswagen'],
        ['code' => '758', 'name' => 'Banco Ceib'],
        ['code' => '759', 'name' => 'Banco Sorocred'],
        ['code' => '761', 'name' => 'Banco Paulista'],
        ['code' => '762', 'name' => 'Banco BM&FBOVESPA'],
        ['code' => '763', 'name' => 'Banco Variable'],
        ['code' => '766', 'name' => 'Banco Banco do Nordeste'],
        ['code' => '767', 'name' => 'Banco Asp'],
        ['code' => '768', 'name' => 'Banco bmg'],
        ['code' => '769', 'name' => 'Banco Itaú Personnalité'],
        ['code' => '770', 'name' => 'Banco Hsbc'],
        ['code' => '771', 'name' => 'Banco Bbb'],
        ['code' => '772', 'name' => 'Banco Primus'],
        ['code' => '773', 'name' => 'Banco Ficsa'],
        ['code' => '774', 'name' => 'Banco Verdepar'],
        ['code' => '775', 'name' => 'Banco Jurisbank'],
        ['code' => '776', 'name' => 'Banco Ibi'],
        ['code' => '777', 'name' => 'Banco Finacor'],
        ['code' => '778', 'name' => 'Banco Bancap'],
        ['code' => '779', 'name' => 'Banco Ciclosoft'],
        ['code' => '780', 'name' => 'Banco Bfc'],
        ['code' => '781', 'name' => 'Banco Brk'],
        ['code' => '782', 'name' => 'Banco Basisinvest'],
        ['code' => '783', 'name' => 'Banco Picpay'],
    ];

    /**
     * Buscar bancos por código ou nome
     *
     * @param string $query
     * @return array
     */
    public static function search(string $query): array
    {
        if (empty($query)) {
            return self::$banks;
        }

        $query = strtolower(trim($query));

        return array_filter(self::$banks, function ($bank) use ($query) {
            $code = strtolower($bank['code']);
            $name = strtolower($bank['name']);

            return str_starts_with($code, $query) || str_contains($name, $query);
        });
    }

    /**
     * Obter todos os bancos
     */
    public static function all(): array
    {
        return self::$banks;
    }

    /**
     * Obter banco por código
     */
    public static function getByCode(string $code): ?array
    {
        foreach (self::$banks as $bank) {
            if ($bank['code'] === $code) {
                return $bank;
            }
        }

        return null;
    }
}
