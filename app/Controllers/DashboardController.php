<?php

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function resumo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $totais = [];

        $totais['total_pessoas'] = (int) $this->pdo->query('SELECT COUNT(*) FROM pessoas')->fetchColumn();
        $totais['total_tipos'] = (int) $this->pdo->query("SELECT COUNT(*) FROM tipos_atendimentos WHERE status = 'ativo'")->fetchColumn();
        $totais['total_atendimentos'] = (int) $this->pdo->query('SELECT COUNT(*) FROM atendimentos')->fetchColumn();

        $sql = '
            SELECT
                a.id,
                p.nome AS pessoa,
                t.descricao AS tipo,
                u.nome AS responsavel,
                a.data_atendimento,
                a.status
            FROM atendimentos a
            INNER JOIN pessoas p ON a.pessoa_id = p.id
            INNER JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
            INNER JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.id DESC
            LIMIT 5
        ';

        $totais['atendimentos_recentes'] = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($totais, JSON_UNESCAPED_UNICODE);
    }
}
