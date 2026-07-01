<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Middleware/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/FrontendController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';

$controller = $_GET['controller'] ?? 'auth';
$action     = $_GET['action'] ?? 'login';

switch ($controller) {

    // ── Autenticação ──────────────────────────────────────────────────────────
    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;
            case 'entrar':
                $authController->entrar();
                break;
            case 'dashboard':
                $authController->dashboard();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                $authController->exibirLogin();
        }
        break;

    // ── Páginas visuais (frontend) ────────────────────────────────────────────
    case 'frontend':
        exigirAutenticacao();
        $frontendController = new FrontendController();
        switch ($action) {
            case 'pessoas':
                $frontendController->pessoas();
                break;
            case 'tipos':
                $frontendController->tipos();
                break;
            case 'atendimentos':
                $frontendController->atendimentos();
                break;
            default:
                $frontendController->pessoas();
        }
        break;

    // ── Dashboard (dados JSON) ────────────────────────────────────────────────
    case 'dashboard':
        exigirAutenticacao();
        $dashboardController = new DashboardController();
        switch ($action) {
            case 'resumo':
                $dashboardController->resumo();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Ação não encontrada.']);
        }
        break;

    // ── Pessoas ───────────────────────────────────────────────────────────────
    case 'pessoas':
        exigirAutenticacao();
        $pessoasController = new PessoasController();
        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;
            case 'criar':
                $pessoasController->criar();
                break;
            case 'atualizar':
                $pessoasController->atualizar();
                break;
            case 'inativar':
                $pessoasController->inativar();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Ação de pessoas não encontrada.']);
        }
        break;

    // ── Tipos de atendimento ──────────────────────────────────────────────────
    case 'tipos':
        exigirAutenticacao();
        $tiposController = new TiposAtendimentosController();
        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $tiposController->buscarPorId();
                break;
            case 'criar':
                $tiposController->criar();
                break;
            case 'atualizar':
                $tiposController->atualizar();
                break;
            case 'inativar':
                $tiposController->inativar();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Ação de tipos não encontrada.']);
        }
        break;

    // ── Atendimentos ──────────────────────────────────────────────────────────
    case 'atendimentos':
        exigirAutenticacao();
        $atendimentosController = new AtendimentosController();
        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Ação de atendimentos não encontrada.']);
        }
        break;

    // ── Usuários ──────────────────────────────────────────────────────────────
    case 'usuarios':
        exigirAutenticacao();
        $usuariosController = new UsuariosController();
        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Ação não encontrada.']);
        }
        break;

    // ── Default ───────────────────────────────────────────────────────────────
    default:
        $authController = new AuthController();
        $authController->exibirLogin();
        break;
}
