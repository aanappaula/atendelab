<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = '
            SELECT
                a.id,
                p.nome AS pessoa,
                t.nome AS tipo,
                u.nome AS responsavel,
                a.data_atendimento,
                a.horario_atendimento,
                a.descricao,
                a.status,
                a.observacao_final
            FROM atendimentos a
            INNER JOIN pessoas p ON a.pessoa_id = p.id
            INNER JOIN tipos_atendimentos t ON a.tipo_atendimento_id = t.id
            INNER JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.id DESC
        ';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Responsável vem da sessão, não do formulário
        $usuarioId = isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;

        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['erro' => 'Usuário não autenticado.']);
            return;
        }

        $pessoaId  = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId    = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $data      = trim($_POST['data_atendimento'] ?? '');
        $horario   = trim($_POST['horario_atendimento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if (!$pessoaId || !$tipoId || $data === '' || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Pessoa, tipo, data e descrição são obrigatórios.']);
            return;
        }

        try {
            $sql = '
                INSERT INTO atendimentos (pessoa_id, tipo_atendimento_id, usuario_id, data_atendimento, horario_atendimento, descricao, status)
                VALUES (:pessoa_id, :tipo_id, :usuario_id, :data, :horario, :descricao, \'aberto\')
            ';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_id', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':data', $data);
            $stmt->bindValue(':horario', $horario ?: null);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->execute();

            http_response_code(201);
            echo json_encode(['mensagem' => 'Atendimento registrado com sucesso.', 'id' => $this->pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        $statusValidos = ['aberto', 'em_andamento', 'concluido', 'cancelado'];

        if (!$id || !in_array($status, $statusValidos, true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID ou status inválido.']);
            return;
        }

        if ($status === 'concluido' && $observacaoFinal === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Observação final é obrigatória ao concluir.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos SET status = :status, observacao_final = :obs WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':obs', $observacaoFinal ?: null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status.']);
        }
    }
}
