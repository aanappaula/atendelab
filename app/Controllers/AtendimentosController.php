<?php

class AtendimentosController
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
                A.ID,
                P.NOME AS PESSOA,
                U.NOME AS ATENDENTE,
                T.DESCRICAO AS TIPO_ATENDIMENTO,
                A.DATA_ATENDIMENTO,
                A.OBSERVACOES,
                A.STATUS
            FROM
                ATENDIMENTOS A
                INNER JOIN PESSOAS P
                    ON A.PESSOA_ID = P.ID
                INNER JOIN USUARIOS U
                    ON A.USUARIO_ID = U.ID
                INNER JOIN TIPOS_ATENDIMENTOS T
                    ON A.TIPO_ATENDIMENTO_ID = T.ID
            ORDER BY
                A.ID DESC
        ';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            $atendimentos,
            JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido.']);
            return;
        }

        $sql = '
            SELECT
                A.ID,
                A.PESSOA_ID,
                P.NOME AS PESSOA,
                A.USUARIO_ID,
                U.NOME AS ATENDENTE,
                A.TIPO_ATENDIMENTO_ID,
                T.DESCRICAO AS TIPO_ATENDIMENTO,
                A.DATA_ATENDIMENTO,
                A.OBSERVACOES,
                A.STATUS
            FROM
                ATENDIMENTOS A
                INNER JOIN PESSOAS P
                    ON A.PESSOA_ID = P.ID
                INNER JOIN USUARIOS U
                    ON A.USUARIO_ID = U.ID
                INNER JOIN TIPOS_ATENDIMENTOS T
                    ON A.TIPO_ATENDIMENTO_ID = T.ID
            WHERE
                A.ID = :id
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['error' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode(
            $atendimento,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);

        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'aberto';

        if (!$pessoaId || !$usuarioId || !$tipoId) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Pessoa, usuário e tipo de atendimento são obrigatórios.'
            ]);

            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'finalizado', 'cancelado'], true)) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Status inválido.'
            ]);

            return;
        }

        try {

            if (!$this->registroExiste('PESSOAS', $pessoaId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Pessoa não encontrada.']);
                return;
            }

            if (!$this->registroExiste('USUARIOS', $usuarioId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Usuário não encontrado.']);
                return;
            }

            if (!$this->registroExiste('TIPOS_ATENDIMENTOS', $tipoId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de atendimento não encontrado.']);
                return;
            }

            $sql = '
                INSERT INTO ATENDIMENTOS
                    (
                        PESSOA_ID,
                        USUARIO_ID,
                        TIPO_ATENDIMENTO_ID,
                        OBSERVACOES,
                        STATUS
                    )
                VALUES
                    (
                        :pessoa,
                        :usuario,
                        :tipo,
                        :observacoes,
                        :status
                    )
            ';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':pessoa', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status', $status);

            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao registrar atendimento.'
            ]);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);

        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'aberto';

        if (!$id || !$pessoaId || !$usuarioId || !$tipoId) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Todos os campos obrigatórios devem ser preenchidos.'
            ]);

            return;
        }

        if (!in_array($status, ['aberto', 'em_andamento', 'finalizado', 'cancelado'], true)) {
            http_response_code(400);

            echo json_encode([
                'error' => 'Status inválido.'
            ]);

            return;
        }

        try {

            if (!$this->registroExiste('ATENDIMENTOS', $id)) {
                http_response_code(404);
                echo json_encode(['error' => 'Atendimento não encontrado.']);
                return;
            }

            if (
                !$this->registroExiste('PESSOAS', $pessoaId) ||
                !$this->registroExiste('USUARIOS', $usuarioId) ||
                !$this->registroExiste('TIPOS_ATENDIMENTOS', $tipoId)
            ) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'Pessoa, usuário ou tipo inválido.'
                ]);

                return;
            }

            $sql = '
                UPDATE ATENDIMENTOS
                SET
                    PESSOA_ID = :pessoa,
                    USUARIO_ID = :usuario,
                    TIPO_ATENDIMENTO_ID = :tipo,
                    OBSERVACOES = :observacoes,
                    STATUS = :status
                WHERE
                    ID = :id
            ';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':pessoa', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':observacoes', $observacoes);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Atendimento atualizado com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao atualizar atendimento.'
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

            $sql = '
                DELETE FROM ATENDIMENTOS
                WHERE ID = :id
            ';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Atendimento excluído com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {

            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao excluir atendimento.'
            ]);
        }
    }

    private function registroExiste(string $tabela, int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM {$tabela} WHERE ID = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}