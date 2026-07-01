<?php

class PessoasController
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

        $sql = "SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status FROM pessoas ORDER BY nome ASC";

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($pessoas, JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        $sql = "SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status FROM pessoas WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada']);
            return;
        }

        echo json_encode($pessoa, JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $curso     = trim($_POST['curso'] ?? '');
        $periodo   = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status'] ?? 'ativo';

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome é obrigatório']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido']);
            return;
        }

        try {
            $sql = "INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, observacoes, status)
                    VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :observacoes, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento ?: null);
            $stmt->bindValue(':telefone', $telefone ?: null);
            $stmt->bindValue(':email', $email ?: null);
            $stmt->bindValue(':curso', $curso ?: null);
            $stmt->bindValue(':periodo', $periodo ?: null);
            $stmt->bindValue(':observacoes', $observacoes ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode(['mensagem' => 'Pessoa cadastrada com sucesso.', 'id' => $this->pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome      = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone  = trim($_POST['telefone'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $curso     = trim($_POST['curso'] ?? '');
        $periodo   = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status    = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e nome são obrigatórios']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido']);
            return;
        }

        try {
            $sql = "UPDATE pessoas SET nome=:nome, documento=:documento, telefone=:telefone, email=:email,
                    curso=:curso, periodo=:periodo, observacoes=:observacoes, status=:status WHERE id=:id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento ?: null);
            $stmt->bindValue(':telefone', $telefone ?: null);
            $stmt->bindValue(':email', $email ?: null);
            $stmt->bindValue(':curso', $curso ?: null);
            $stmt->bindValue(':periodo', $periodo ?: null);
            $stmt->bindValue(':observacoes', $observacoes ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE pessoas SET status = 'inativo' WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}
