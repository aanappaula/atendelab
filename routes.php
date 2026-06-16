<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/app/Controllers/UsuariosController.php";
require_once __DIR__ . "/app/Controllers/PessoasController.php";
require_once __DIR__ . "/app/Controllers/TiposAtendimentosController.php";
require_once __DIR__ . "/app/Controllers/AtendimentosController.php";
require_once __DIR__ . "/app/Controllers/AuthController.php";
require_once __DIR__ . "/app/Middleware/auth.php";

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

switch ($controller) {
    case 'auth':
        $authController = new AuthController();
        break;

    case 'usuarios':
        exigirAutenticacao();
        $controllerInstance = new UsuariosController();
        break;

    case 'pessoas':
        $controllerInstance = new PessoasController();
        break;

    case 'tipos_atendimentos':
        $controllerInstance = new TiposAtendimentosController();
        break;

    case 'atendimentos':
        $controllerInstance = new AtendimentosController();
        break;

    default:
        $authController = new AuthController();
        $action = 'login';
        break;
}

switch ($action) {

    case 'listar':
        $controllerInstance->listar();
        break;

    case 'buscar':
        $controllerInstance->buscarPorId();
        break;

    case 'criar':
        $controllerInstance->criar();
        break;

    case 'atualizar':
        $controllerInstance->atualizar();
        break;

    case 'excluir':
        $controllerInstance->excluir();
        break;

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
        echo "Ação não encontrada.";
        break;
}