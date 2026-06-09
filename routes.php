<?php

require_once __DIR__ ."/app/Controllers/UsuariosController.php";

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

if ($controller === 'usuarios') {
    $usuarioController = new UsuariosController();

    switch ($action) {
        case 'listar':
            $usuario = $usuarioController->listar();
            break;
        case 'buscar':
            $usuario = $usuarioController->buscarPorId();
            break;
        case 'criar':
            $usuario = $usuarioController->criar();
            break;
        case 'atualizar':
            $usuario = $usuarioController->atualizar();
            break;
        case 'excluir':
            $usuario = $usuarioController->excluir();
            break;
        default:
            echo "Ação de usuários não encontrada.";
            break;
    }
} else {
    echo '<h1>AtendeLab</h1>';
    echo '<p>Projeto em execução. Use ?controller=usuarios&action=listar para testar</p>';
}