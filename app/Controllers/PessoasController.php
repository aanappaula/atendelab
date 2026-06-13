<?php

class PessoasController
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

        $sql = "
            SELECT
                ID,
                NOME,
                CPF,
                TELEFONE,
                EMAIL,
                ENDERECO,
                CRIADO_EM
            FROM PESSOAS
            ORDER BY ID DESC
        ";

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            $pessoas,
            JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $sql = "
            SELECT
                ID,
                NOME,
                CPF,
                TELEFONE,
                EMAIL,
                ENDERECO,
                CRIADO_EM
            FROM PESSOAS
            WHERE ID = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['error' => 'Pessoa não encontrada']);
            return;
        }

        echo json_encode(
            $pessoa,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nome é obrigatório']);
            return;
        }

        if ($cpf !== '' && !$this->validarCpf($cpf)) {
            http_response_code(400);
            echo json_encode(['error' => 'CPF inválido']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'E-mail inválido']);
            return;
        }

        if ($cpf !== '') {
            $sqlCpf = "SELECT COUNT(*) FROM PESSOAS WHERE CPF = :cpf";

            $stmtCpf = $this->pdo->prepare($sqlCpf);
            $stmtCpf->bindValue(':cpf', $cpf);
            $stmtCpf->execute();

            if ($stmtCpf->fetchColumn() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'CPF já cadastrado']);
                return;
            }
        }

        try {
            $sql = "
                INSERT INTO PESSOAS
                    (NOME, CPF, TELEFONE, EMAIL, ENDERECO)
                VALUES
                    (:nome, :cpf, :telefone, :email, :endereco)
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf === '' ? null : $cpf);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':endereco', $endereco);

            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cadastrar pessoa']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        $nome = trim($_POST['nome'] ?? '');
        $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        if (!$id || $nome === '') {
            http_response_code(400);
            echo json_encode(['error' => 'ID e nome são obrigatórios']);
            return;
        }

        if ($cpf !== '' && !$this->validarCpf($cpf)) {
            http_response_code(400);
            echo json_encode(['error' => 'CPF inválido']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'E-mail inválido']);
            return;
        }

        if ($cpf !== '') {
            $sqlCpf = "
                SELECT COUNT(*)
                FROM PESSOAS
                WHERE CPF = :cpf
                  AND ID <> :id
            ";

            $stmtCpf = $this->pdo->prepare($sqlCpf);

            $stmtCpf->bindValue(':cpf', $cpf);
            $stmtCpf->bindValue(':id', $id, PDO::PARAM_INT);

            $stmtCpf->execute();

            if ($stmtCpf->fetchColumn() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'CPF já cadastrado']);
                return;
            }
        }

        try {
            $sql = "
                UPDATE PESSOAS
                SET
                    NOME = :nome,
                    CPF = :cpf,
                    TELEFONE = :telefone,
                    EMAIL = :email,
                    ENDERECO = :endereco
                WHERE ID = :id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf === '' ? null : $cpf);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':endereco', $endereco);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Pessoa atualizada com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar pessoa']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        try {
            $sqlVerifica = "
                SELECT COUNT(*)
                FROM ATENDIMENTOS
                WHERE PESSOA_ID = :id
            ";

            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetchColumn() > 0) {
                http_response_code(400);

                echo json_encode([
                    'error' => 'Não é possível excluir a pessoa, pois existem atendimentos vinculados.'
                ]);

                return;
            }

            $sql = "DELETE FROM PESSOAS WHERE ID = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Pessoa excluída com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'error' => 'Erro ao excluir pessoa.'
            ]);
        }
    }

    private function validarCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {

            $soma = 0;

            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }
}
