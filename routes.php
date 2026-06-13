<?php

require_once __DIR__ . "/app/Controllers/UsuariosController.php";
require_once __DIR__ . "/app/Controllers/PessoasController.php";
require_once __DIR__ . "/app/Controllers/TiposAtendimentosController.php";
require_once __DIR__ . "/app/Controllers/AtendimentosController.php";

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($controller) {

    case 'usuarios':
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
        echo '<h1>AtendeLab</h1>';
        echo '<p>Projeto em execução.</p>';
        exit;
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

    default:
        echo "Ação não encontrada.";
        break;
}