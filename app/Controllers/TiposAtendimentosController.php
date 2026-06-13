<?php

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . "/../../config/database.php";
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = '
            SELECT
                ID,
                DESCRICAO,
                STATUS,
                CRIADO_EM
            FROM
                TIPOS_ATENDIMENTOS
            ORDER BY
                ID DESC
        ';

        $stmt = $this->pdo->query($sql);
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            $tipos,
            JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);

            echo json_encode([
                'error' => 'ID inválido.'
            ]);

            return;
        }

        $sql = '
            SELECT
                ID,
                DESCRICAO,
                STATUS,
                CRIADO_EM
            FROM
                TIPOS_ATENDIMENTOS
            WHERE
                ID = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            http_response_code(404);

            echo json_encode([
                'error' => 'Tipo de atendimento não encontrado.'
            ]);

            return;
        }

        echo json_encode(
            $tipo,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if ($descricao === '') {
            http_response_code(400);

            echo json_encode([
                'error' => 'Descrição é obrigatória.'
            ]);

            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Status inválido.'
            ]);

            return;
        }

        try {

            $sqlVerifica = '
                SELECT COUNT(*)
                FROM TIPOS_ATENDIMENTOS
                WHERE UPPER(DESCRICAO) = UPPER(:descricao)
            ';

            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':descricao', $descricao);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetchColumn() > 0) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'Já existe um tipo de atendimento com essa descrição.'
                ]);

                return;
            }

            $sql = '
                INSERT INTO TIPOS_ATENDIMENTOS
                    (DESCRICAO, STATUS)
                VALUES
                    (:descricao, :status)
            ';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);

            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'mensagem' => 'Tipo de atendimento cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao cadastrar tipo de atendimento.'
            ]);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $descricao === '') {
            http_response_code(400);

            echo json_encode([
                'error' => 'ID e descrição são obrigatórios.'
            ]);

            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Status inválido.'
            ]);

            return;
        }

        try {

            $sqlVerifica = '
                SELECT COUNT(*)
                FROM TIPOS_ATENDIMENTOS
                WHERE UPPER(DESCRICAO) = UPPER(:descricao)
                  AND ID <> :id
            ';

            $stmtVerifica = $this->pdo->prepare($sqlVerifica);

            $stmtVerifica->bindValue(':descricao', $descricao);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);

            $stmtVerifica->execute();

            if ($stmtVerifica->fetchColumn() > 0) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'Já existe outro tipo com essa descrição.'
                ]);

                return;
            }

            $sql = '
                UPDATE TIPOS_ATENDIMENTOS
                SET
                    DESCRICAO = :descricao,
                    STATUS = :status
                WHERE
                    ID = :id
            ';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Tipo de atendimento atualizado com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao atualizar tipo de atendimento.'
            ]);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);

            echo json_encode([
                'error' => 'ID inválido.'
            ]);

            return;
        }

        try {

            $sqlVerifica = '
                SELECT COUNT(*)
                FROM ATENDIMENTOS
                WHERE TIPO_ATENDIMENTO_ID = :id
            ';

            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetchColumn() > 0) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'Não é possível excluir o tipo de atendimento, pois existem atendimentos vinculados.'
                ]);

                return;
            }

            $sql = '
                DELETE FROM TIPOS_ATENDIMENTOS
                WHERE ID = :id
            ';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Tipo de atendimento excluído com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao excluir tipo de atendimento.'
            ]);
        }
    }
}